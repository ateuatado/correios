<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * RegrasInterpretar
 *
 * Enriquece as regras extraídas do MANCAT com interpretação semântica
 * gerada pela Gemini API. Cada regra é analisada UMA vez e o resultado
 * fica salvo no banco — nenhuma chamada de IA é feita novamente.
 *
 * Uso:
 *   php spark regras:interpretar              ← só as não interpretadas
 *   php spark regras:interpretar --reset      ← reinterpreta todas
 *   php spark regras:interpretar --limite=50  ← processa N regras
 *   php spark regras:interpretar --tipo=prazo ← filtra por tipo
 */
class RegrasInterpretar extends BaseCommand
{
    protected $group       = 'inteligencia';
    protected $name        = 'regras:interpretar';
    protected $description = 'Enriquece regras do MANCAT com interpretação semântica via Gemini API.';
    protected $usage       = 'regras:interpretar [--reset] [--limite=N] [--tipo=tipo]';
    protected $options     = [
        '--reset'  => 'Reinterpreta todas as regras (inclusive já interpretadas)',
        '--limite' => 'Número máximo de regras a processar nesta execução',
        '--tipo'   => 'Filtra por tipo: peso|dimensao|prazo|valor|volume|restricao|elegibilidade',
    ];

    private string $apiKey;
    private string $model;
    private string $endpoint;

    public function run(array $params): void
    {
        // Configurações do .env
        $this->apiKey   = env('gemini.apiKey', '');
        $this->model    = env('gemini.model', 'gemini-2.0-flash');
        $this->endpoint = env('gemini.endpoint', 'https://generativelanguage.googleapis.com/v1beta/models');

        if (empty($this->apiKey)) {
            CLI::error('gemini.apiKey não encontrada no .env. Configure antes de continuar.');
            return;
        }

        $db     = \Config\Database::connect();
        $reset  = array_key_exists('reset', $params) || CLI::getOption('reset');
        $limite = (int) (CLI::getOption('limite') ?? 0);
        $tipo   = CLI::getOption('tipo') ?? '';

        // Monta a query
        $builder = $db->table('regras');
        if (! $reset) {
            $builder->where('interpretacao IS NULL');
        } else {
            $builder->set('interpretacao', null)->set('interpretado_em', null)->update();
            $builder = $db->table('regras');
            CLI::write('   Reset feito — reinterpretando tudo.', 'yellow');
        }
        if ($tipo) {
            $builder->where('tipo', $tipo);
        }
        if ($limite > 0) {
            $builder->limit($limite);
        }

        $regras = $builder
            ->select('id, tipo, servico, descricao, contexto, fonte')
            ->get()->getResultArray();

        $total = count($regras);
        if ($total === 0) {
            CLI::write('   Nenhuma regra pendente de interpretação.', 'green');
            return;
        }

        CLI::write("   Interpretando {$total} regras via {$this->model}...", 'cyan');
        CLI::write('   (Isso pode levar alguns minutos — 1 chamada por regra com pausa de 1s)', 'dark_gray');
        CLI::newLine();

        $ok      = 0;
        $erros   = 0;
        $now     = date('Y-m-d H:i:s');

        foreach ($regras as $i => $regra) {
            $num = $i + 1;
            CLI::showProgress($num, $total);

            $interpretacao = $this->interpretar($regra);

            if ($interpretacao !== null) {
                $db->table('regras')->where('id', $regra['id'])->update([
                    'interpretacao'    => json_encode($interpretacao, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                    'interpretado_em'  => $now,
                    'interpretado_por' => $this->model,
                ]);
                $ok++;
            } else {
                $erros++;
            }

            // Pausa para respeitar rate limit (15 RPM = 4s mínimo, 5s com margem de segurança)
            usleep(5_000_000); // 5 segundos
        }

        CLI::newLine(2);
        CLI::write("   ✔  {$ok} regras interpretadas com sucesso.", 'green');
        if ($erros > 0) {
            CLI::write("   ⚠  {$erros} regras com erro (rode novamente para tentar de novo).", 'yellow');
        }
    }

    // ─────────────────────────────────────────────────────────────────────
    // Chama a Gemini API e retorna o array interpretado
    // ─────────────────────────────────────────────────────────────────────

    private function interpretar(array $regra): ?array
    {
        $prompt = $this->montarPrompt($regra);
        $url    = "{$this->endpoint}/{$this->model}:generateContent?key={$this->apiKey}";

        $payload = json_encode([
            'contents' => [[
                'parts' => [['text' => $prompt]],
            ]],
            'generationConfig' => [
                'temperature'     => 0.2,  // baixa para respostas consistentes
                'maxOutputTokens' => 800,
                'responseMimeType'=> 'application/json',
            ],
        ]);

        // Até 3 tentativas em caso de rate limit (429)
        for ($tentativa = 1; $tentativa <= 3; $tentativa++) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT        => 30,
            ]);
            $body       = curl_exec($ch);
            $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError  = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                CLI::write("   CURL erro: {$curlError}", 'red');
                return null;
            }

            if ($httpCode === 429) {
                // Rate limit — espera mais e tenta novamente
                sleep(15 * $tentativa);
                continue;
            }

            if ($httpCode !== 200) {
                CLI::write("   HTTP {$httpCode} para regra #{$regra['id']}", 'red');
                return null;
            }

            $data = json_decode($body, true);
            $texto = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

            if (! $texto) return null;

            // Remove markdown code blocks se o modelo os incluir
            $texto = preg_replace('/^```(?:json)?\s*/m', '', $texto);
            $texto = preg_replace('/```\s*$/m', '', $texto);

            $result = json_decode(trim($texto), true);
            return is_array($result) ? $result : null;
        }

        return null;
    }

    // ─────────────────────────────────────────────────────────────────────
    // Monta o prompt para a Gemini
    // ─────────────────────────────────────────────────────────────────────

    private function montarPrompt(array $regra): string
    {
        $servico = $regra['servico'] ?? 'não identificado';
        $tipo    = $regra['tipo'];
        $desc    = $regra['descricao'];
        $ctx     = mb_substr($regra['contexto'] ?? '', 0, 600);
        $fonte   = $regra['fonte'] ?? '';

        return <<<PROMPT
Você é um especialista em normas e serviços postais dos Correios do Brasil, com profundo conhecimento do MANCAT (Manual de Atendimento Comercial).

Analise a regra abaixo extraída do MANCAT e retorne um JSON estruturado com a interpretação completa.

## REGRA A ANALISAR
- Serviço: {$servico}
- Tipo detectado: {$tipo}
- Descrição extraída: {$desc}
- Contexto original: {$ctx}
- Fonte: {$fonte}

## INSTRUÇÕES
Retorne APENAS um JSON válido (sem markdown, sem texto antes ou depois) com EXATAMENTE esta estrutura:

{
  "o_que_e": "Descrição clara em 1-2 frases do que esta regra define",
  "quando_aplica": "Em que situação / condição esta regra entra em vigor",
  "quem_se_aplica": "A quem esta regra se destina (ex: clientes PJ com contrato, remetentes, agências)",
  "condicoes": ["condição 1", "condição 2"],
  "impacto_comercial": "Como esta regra afeta a venda, o contrato ou o relacionamento com o cliente",
  "restricoes": ["restrição 1 se houver", "..."],
  "palavras_chave": ["palavra1", "palavra2", "palavra3"],
  "resumo_executivo": "Uma frase direta que um analista comercial usaria para explicar esta regra a um cliente"
}

Se algum campo não for aplicável, use null ou array vazio [].
PROMPT;
    }
}
