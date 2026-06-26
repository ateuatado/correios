<?php

namespace App\Controllers;

use App\Libraries\GeminiService;

/**
 * Assistente IA — Chat sobre os manuais MANCAT
 *
 * GET  /assistente         → Página principal do chat
 * POST /assistente/chat    → Endpoint AJAX (retorna JSON)
 * GET  /assistente/limpar  → Limpa histórico da sessão
 */
class Assistente extends BaseController
{
    private GeminiService $gemini;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface           $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->gemini = new GeminiService();
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /assistente
    // ─────────────────────────────────────────────────────────────────

    public function index(): string
    {
        $session  = session();
        $historico = $session->get('assistente_historico') ?? [];

        return view('assistente/index', [
            'title'     => 'Assistente IA — CorreiosComercial',
            'historico' => $historico,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // POST /assistente/chat  (AJAX — retorna JSON)
    // ─────────────────────────────────────────────────────────────────

    public function chat(): \CodeIgniter\HTTP\ResponseInterface
    {
        $pergunta = trim($this->request->getPost('pergunta') ?? '');

        if (empty($pergunta)) {
            return $this->response->setJSON(['erro' => 'Pergunta vazia.']);
        }

        if (strlen($pergunta) > 500) {
            return $this->response->setJSON(['erro' => 'Pergunta muito longa (máx. 500 caracteres).']);
        }

        if (! $this->gemini->configurado()) {
            return $this->response->setJSON(['erro' => 'API Gemini não configurada.']);
        }

        // 1. Busca contexto relevante no banco
        $contexto = $this->buscarContexto($pergunta);

        // 2. Monta o prompt RAG
        $prompt = $this->montarPrompt($pergunta, $contexto['trechos']);

        // 3. Chama a IA
        $resposta = $this->gemini->gerar($prompt, [
            'temperature'     => 0.3,
            'maxOutputTokens' => 1200,
        ]);

        if ($resposta === null) {
            return $this->response->setJSON([
                'erro' => 'A IA não conseguiu responder agora. Tente novamente em alguns segundos.',
            ]);
        }

        // 4. Salva no histórico da sessão (últimas 10 trocas)
        $session   = session();
        $historico = $session->get('assistente_historico') ?? [];
        $historico[] = [
            'pergunta'  => $pergunta,
            'resposta'  => $resposta,
            'fontes'    => $contexto['fontes'],
            'timestamp' => date('H:i'),
        ];
        // Mantém apenas as últimas 10 trocas
        if (count($historico) > 10) {
            $historico = array_slice($historico, -10);
        }
        $session->set('assistente_historico', $historico);

        return $this->response->setJSON([
            'resposta'  => $resposta,
            'fontes'    => $contexto['fontes'],
            'csrf_hash' => csrf_hash(),
        ]);
    }

    // ─────────────────────────────────────────────────────────────────
    // GET /assistente/limpar
    // ─────────────────────────────────────────────────────────────────

    public function limpar(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove('assistente_historico');
        return redirect()->to('/assistente');
    }

    // ─────────────────────────────────────────────────────────────────
    // Busca trechos relevantes via FULLTEXT no banco
    // ─────────────────────────────────────────────────────────────────

    private function buscarContexto(string $pergunta): array
    {
        $db = db_connect();

        // Sanitiza a pergunta para FULLTEXT (remove caracteres especiais do MySQL)
        $q = preg_replace('/[+\-><\(\)~*"@]+/', ' ', $pergunta);
        $q = trim($q);

        $trechos = [];
        $fontes  = [];

        // ── Busca em ITENS do manual ──────────────────────────────
        try {
            $itens = $db->query(
                "SELECT i.id, i.titulo, SUBSTRING(i.conteudo, 1, 600) AS trecho,
                        m.codigo AS manual, m.titulo AS manual_titulo,
                        MATCH(i.titulo, i.conteudo) AGAINST(? IN NATURAL LANGUAGE MODE) AS score
                 FROM itens i
                 LEFT JOIN manuais m ON m.id = i.manual_id
                 WHERE MATCH(i.titulo, i.conteudo) AGAINST(? IN NATURAL LANGUAGE MODE)
                 ORDER BY score DESC
                 LIMIT 6",
                [$q, $q]
            )->getResultArray();

            foreach ($itens as $item) {
                $trechos[] = [
                    'origem'  => 'manual',
                    'titulo'  => $item['titulo'],
                    'trecho'  => $item['trecho'],
                    'manual'  => $item['manual'] ?? $item['manual_titulo'],
                    'score'   => (float) $item['score'],
                ];
                if ((float) $item['score'] >= 1.0) {
                    $fontes[] = [
                        'tipo'  => 'Manual',
                        'label' => ($item['manual'] ? "[{$item['manual']}] " : '') . mb_substr($item['titulo'], 0, 80),
                        'url'   => base_url("manuais/api/item/{$item['id']}"),
                        'score' => round((float) $item['score'], 2),
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('error', '[Assistente] Busca itens: ' . $e->getMessage());
        }

        // ── Busca em REGRAS interpretadas ─────────────────────────
        try {
            $regras = $db->query(
                "SELECT id, tipo, servico, descricao, contexto,
                        interpretacao,
                        MATCH(descricao, contexto) AGAINST(? IN NATURAL LANGUAGE MODE) AS score
                 FROM regras
                 WHERE interpretacao IS NOT NULL
                   AND MATCH(descricao, contexto) AGAINST(? IN NATURAL LANGUAGE MODE)
                 ORDER BY score DESC
                 LIMIT 5",
                [$q, $q]
            )->getResultArray();

            foreach ($regras as $regra) {
                $ia = json_decode($regra['interpretacao'] ?? '{}', true);
                $trechos[] = [
                    'origem'  => 'regra',
                    'titulo'  => $regra['descricao'],
                    'trecho'  => ($ia['o_que_e'] ?? '') . ' ' . ($ia['quando_aplica'] ?? ''),
                    'manual'  => $regra['servico'] ? "Serviço: {$regra['servico']}" : "Tipo: {$regra['tipo']}",
                    'score'   => (float) $regra['score'],
                    'resumo'  => $ia['resumo_executivo'] ?? null,
                ];
                if ((float) $regra['score'] >= 1.0) {
                    $fontes[] = [
                        'tipo'  => 'Regra',
                        'label' => mb_substr($regra['descricao'], 0, 80),
                        'url'   => null,
                        'score' => round((float) $regra['score'], 2),
                    ];
                }
            }
        } catch (\Exception $e) {
            log_message('error', '[Assistente] Busca regras: ' . $e->getMessage());
        }

        // Ordena trechos por score descendente e limita a 8
        usort($trechos, fn($a, $b) => $b['score'] <=> $a['score']);
        $trechos = array_slice($trechos, 0, 8);

        // Ordena fontes por score
        usort($fontes, fn($a, $b) => $b['score'] <=> $a['score']);
        $fontes = array_slice($fontes, 0, 5);

        return ['trechos' => $trechos, 'fontes' => $fontes];
    }

    // ─────────────────────────────────────────────────────────────────
    // Monta o prompt RAG para a Gemini
    // ─────────────────────────────────────────────────────────────────

    private function montarPrompt(string $pergunta, array $trechos): string
    {
        $contextoTexto = '';
        foreach ($trechos as $i => $t) {
            $num   = $i + 1;
            $orig  = strtoupper($t['origem']);
            $ref   = $t['manual'] ? " [{$t['manual']}]" : '';
            $contextoTexto .= "[{$num}] {$orig}{$ref}\n";
            $contextoTexto .= "Título: {$t['titulo']}\n";
            $contextoTexto .= "Conteúdo: " . mb_substr($t['trecho'], 0, 400) . "\n\n";
        }

        if (empty($contextoTexto)) {
            $contextoTexto = "Nenhum trecho encontrado no banco de dados para esta pergunta.";
        }

        return <<<PROMPT
Você é o Assistente Comercial dos Correios do Brasil, especialista no MANCAT (Manual de Atendimento Comercial).

Sua função é responder perguntas sobre produtos, serviços, regras e procedimentos dos Correios com base EXCLUSIVAMENTE nos trechos do manual abaixo. 

REGRAS IMPORTANTES:
1. Responda SOMENTE com base nos trechos fornecidos. Não invente informações.
2. Se a informação não estiver nos trechos, diga claramente: "Não encontrei essa informação no manual disponível."
3. Seja objetivo e direto. Use listas quando listar múltiplos itens.
4. Cite o número do trecho de referência quando usar uma informação (ex: "Conforme o trecho [2]...").
5. Use linguagem profissional mas acessível.
6. Formate a resposta em Markdown simples (negrito, listas com hífen).

## TRECHOS DO MANCAT / REGRAS

{$contextoTexto}

## PERGUNTA DO USUÁRIO

{$pergunta}

## RESPOSTA
PROMPT;
    }
}
