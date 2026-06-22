<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

/**
 * RegrasExtract
 *
 * Varre os 8.910 itens do banco e extrai regras estruturadas:
 * pesos, dimensões, prazos, valores, restrições, elegibilidade.
 *
 * Uso: php spark regras:extract [--reset]
 */
class RegrasExtract extends BaseCommand
{
    protected $group       = 'inteligencia';
    protected $name        = 'regras:extract';
    protected $description = 'Extrai regras estruturadas dos itens do MANCAT para a tabela `regras`.';
    protected $usage       = 'regras:extract [--reset]';
    protected $options     = [
        '--reset' => 'Limpa a tabela antes de reextrair (padrão: incremental)',
    ];

    /** Serviços conhecidos — ordem importa: mais específico primeiro */
    private const SERVICOS = [
        'SEDEX 10',
        'SEDEX 12',
        'SEDEX HOJE',
        'SEDEX',
        'PAC MINI',
        'PAC',
        'MALOTE',
        'CARTA COMERCIAL',
        'CARTA',
        'EMS',
        'TELEGRAMA',
        'REMESSA EXPRESSA',
        'REMESSA ECONÔMICA',
        'REMESSA',
        'LOGÍSTICA REVERSA',
        'BANCO POSTAL',
        'VALE POSTAL',
    ];

    /** Padrões de extração — cada entrada: [tipo, regex, grupo_valor, grupo_unidade?] */
    private array $padroes;

    public function __construct($logger, $commands)
    {
        parent::__construct($logger, $commands);

        $this->padroes = [
            // ── PESO ─────────────────────────────────────────────────────────
            [
                'tipo'   => 'peso',
                'regex'  => '/(\d+[\.,]?\d*)\s*(kg|quilograma[s]?|g\b|grama[s]?)/ui',
                'val'    => 1,
                'uni'    => 2,
                'ctx'    => 'Limite de peso',
            ],
            // Peso por extenso: "trinta quilogramas"
            [
                'tipo'   => 'peso',
                'regex'  => '/(?:at[eé]|de|limite de|m[áa]ximo de|m[íi]nimo de)\s+(\d+)\s*\(([^)]+)\)\s*(kg|quilograma[s]?)/ui',
                'val'    => 1,
                'uni'    => 3,
                'ctx'    => 'Limite de peso (formal)',
            ],

            // ── DIMENSÃO ──────────────────────────────────────────────────────
            [
                'tipo'   => 'dimensao',
                'regex'  => '/(\d+[\.,]?\d*)\s*(cm|m[^a-z]|milímetro[s]?|mm)\s*[xX×]\s*(\d+[\.,]?\d*)\s*(cm|m[^a-z]|mm)/ui',
                'val'    => 1,
                'uni'    => 2,
                'ctx'    => 'Dimensão',
            ],
            // Dimensão única (ex: "comprimento máximo de 100 cm")
            [
                'tipo'   => 'dimensao',
                'regex'  => '/(?:comprimento|largura|altura|dimens[ãa]o|medida)[s]?\s+(?:m[áa]xim[ao]|m[íi]nim[ao]|de|máx\.?|mín\.?)?\s*(?:de\s+)?(\d+[\.,]?\d*)\s*(cm|metro[s]?|m[^a-z]|mm)/ui',
                'val'    => 1,
                'uni'    => 2,
                'ctx'    => 'Dimensão máxima',
            ],

            // ── PRAZO ─────────────────────────────────────────────────────────
            [
                'tipo'   => 'prazo',
                'regex'  => '/(?:prazo\s+(?:de\s+)?|em\s+)?(\d+)\s*\([^)]*\)\s*(dia[s]?\s*[uú]t[ei][il][s]?|hora[s]?|dia[s]?\s*corrid[ao][s]?|dia[s]?)/ui',
                'val'    => 1,
                'uni'    => 2,
                'ctx'    => 'Prazo formal',
            ],
            [
                'tipo'   => 'prazo',
                'regex'  => '/prazo\s+(?:m[áa]ximo\s+)?de\s+(\d+)\s*(dia[s]?\s*[uú]t[ei][il][s]?|hora[s]?|dia[s]?)/ui',
                'val'    => 1,
                'uni'    => 2,
                'ctx'    => 'Prazo',
            ],

            // ── VALOR MONETÁRIO ───────────────────────────────────────────────
            [
                'tipo'   => 'valor',
                'regex'  => '/R\$\s*(\d+[\.,]\d{1,2})/u',
                'val'    => 1,
                'uni'    => null,
                'ctx'    => 'Valor (R$)',
                'uni_fixo' => 'R$',
            ],

            // ── VOLUME / QUANTIDADE ───────────────────────────────────────────
            [
                'tipo'   => 'volume',
                'regex'  => '/(?:volume\s+m[íi]nimo|quantidade\s+m[íi]nima|m[íi]nimo\s+de|m[íi]nima\s+de)\s+(\d+[\.,]?\d*)\s*(objetos?|unidades?|envios?|pe[çc]as?|itens?)?/ui',
                'val'    => 1,
                'uni'    => 2,
                'ctx'    => 'Volume mínimo',
                'uni_fallback' => 'unidades',
            ],

            // ── TOLERÂNCIA / MARGEM ───────────────────────────────────────────
            [
                'tipo'   => 'tolerancia',
                'regex'  => '/toler[âa]ncia\s+(?:de\s+)?(\d+[\.,]?\d*)\s*(%|por\s+cento)/ui',
                'val'    => 1,
                'uni'    => null,
                'ctx'    => 'Margem de tolerância',
                'uni_fixo' => '%',
            ],

            // ── RESTRIÇÕES ────────────────────────────────────────────────────
            [
                'tipo'  => 'restricao',
                'regex' => '/n[ãa]o\s+[eé]\s+permitid[ao]|n[ãa]o\s+s[eé]\s+aceita?[ms]?|[eé]\s+vedado|[eé]\s+proibid[ao]|[eé]\s+expressamente\s+proibid[ao]|n[ãa]o\s+pode[m]?\s+ser\s+(?:aceito|remetido|enviado)/ui',
                'val'   => null,
                'uni'   => null,
                'ctx'   => 'Restrição',
            ],

            // ── ELEGIBILIDADE / CONDIÇÃO ──────────────────────────────────────
            [
                'tipo'  => 'elegibilidade',
                'regex' => '/somente\s+(?:para|por)|exclusivo\s+para|destinad[ao][s]?\s+a|elegível\s+(?:apenas\s+)?para|(?:válido|disponível)\s+(?:apenas\s+)?para/ui',
                'val'   => null,
                'uni'   => null,
                'ctx'   => 'Elegibilidade',
            ],
        ];
    }

    public function run(array $params): void
    {
        $db   = \Config\Database::connect();
        $now  = date('Y-m-d H:i:s');
        $reset = array_key_exists('reset', $params) || CLI::getOption('reset');

        if ($reset) {
            $db->table('regras')->truncate();
            CLI::write('   Tabela regras limpa.', 'yellow');
        }

        // Total de itens
        $total = $db->table('itens')->countAll();
        CLI::write("   Processando {$total} itens...", 'cyan');

        $lote        = 500;
        $offset      = 0;
        $totalRegras = 0;
        $itensProc   = 0;

        while (true) {
            $itens = $db->table('itens')
                ->select('id, doc_tipo, doc_id, numero, titulo, conteudo')
                ->limit($lote, $offset)
                ->get()->getResultArray();

            if (empty($itens)) break;

            $batch = [];

            foreach ($itens as $item) {
                // Texto completo do item para análise
                $textoCompleto = ($item['titulo'] ?? '') . ' ' . ($item['conteudo'] ?? '');
                if (mb_strlen(trim($textoCompleto)) < 10) continue;

                // Detecta o serviço mencionado no contexto
                $servico = $this->detectarServico($textoCompleto);

                // Fonte legível
                $fonte = $this->montarFonte($db, $item);

                // Roda cada padrão
                foreach ($this->padroes as $padrao) {
                    $tipo = $padrao['tipo'];

                    if (in_array($tipo, ['restricao', 'elegibilidade'])) {
                        // Padrões sem valor numérico — basta achar o match
                        if (preg_match($padrao['regex'], $textoCompleto, $m)) {
                            $trecho = $this->extrairTrecho($textoCompleto, $m[0]);
                            $batch[] = [
                                'item_id'        => $item['id'],
                                'doc_tipo'       => $item['doc_tipo'],
                                'doc_id'         => $item['doc_id'],
                                'servico'        => $servico,
                                'tipo'           => $tipo,
                                'descricao'      => $padrao['ctx'] . ': ' . mb_substr(trim($trecho), 0, 250),
                                'valor_numerico' => null,
                                'unidade'        => null,
                                'contexto'       => mb_substr(trim($textoCompleto), 0, 500),
                                'fonte'          => $fonte,
                                'criado_em'      => $now,
                            ];
                        }
                    } else {
                        // Padrões com valor numérico
                        preg_match_all($padrao['regex'], $textoCompleto, $matches, PREG_SET_ORDER);
                        foreach ($matches as $m) {
                            $valIdx = $padrao['val'];
                            $uniIdx = $padrao['uni'] ?? null;

                            $valorRaw = isset($m[$valIdx]) ? str_replace(',', '.', $m[$valIdx]) : null;
                            $valorNum = $valorRaw !== null ? (float) $valorRaw : null;
                            $unidade  = $uniIdx && isset($m[$uniIdx])
                                ? mb_strtolower(trim($m[$uniIdx]))
                                : ($padrao['uni_fixo'] ?? ($padrao['uni_fallback'] ?? null));

                            // Normaliza unidade
                            $unidade = $this->normalizarUnidade($unidade);

                            $descricao = $padrao['ctx'];
                            if ($valorNum !== null) {
                                $descricao .= ': ' . $valorRaw . ($unidade ? ' ' . $unidade : '');
                            }
                            $descricao .= ' — ' . mb_substr(trim($item['titulo']), 0, 120);

                            $batch[] = [
                                'item_id'        => $item['id'],
                                'doc_tipo'       => $item['doc_tipo'],
                                'doc_id'         => $item['doc_id'],
                                'servico'        => $servico,
                                'tipo'           => $tipo,
                                'descricao'      => mb_substr($descricao, 0, 400),
                                'valor_numerico' => $valorNum,
                                'unidade'        => $unidade,
                                'contexto'       => mb_substr(trim($textoCompleto), 0, 500),
                                'fonte'          => $fonte,
                                'criado_em'      => $now,
                            ];
                        }
                    }
                }

                $itensProc++;
            }

            if (! empty($batch)) {
                $db->table('regras')->insertBatch($batch);
                $totalRegras += count($batch);
            }

            $offset += $lote;
            CLI::showProgress($offset > $total ? $total : $offset, $total);
        }

        CLI::newLine(2);
        CLI::write("   ✔  {$itensProc} itens processados.", 'green');
        CLI::write("   ✔  {$totalRegras} regras extraídas.", 'green');

        // Resumo por tipo
        $resumo = $db->query(
            'SELECT tipo, servico, COUNT(*) AS total FROM regras GROUP BY tipo, servico ORDER BY tipo, total DESC'
        )->getResultArray();

        CLI::newLine();
        CLI::write('   Por tipo e serviço:', 'cyan');
        $tipoAtual = '';
        foreach ($resumo as $r) {
            if ($r['tipo'] !== $tipoAtual) {
                CLI::write("   [{$r['tipo']}]", 'yellow');
                $tipoAtual = $r['tipo'];
            }
            $srv = $r['servico'] ?? '(geral)';
            CLI::write("     {$srv}: {$r['total']}");
        }
    }

    // ──────────────────────────────────────────────────────────────────────
    // Helpers
    // ──────────────────────────────────────────────────────────────────────

    private function detectarServico(string $texto): ?string
    {
        $textoUp = mb_strtoupper($texto);
        foreach (self::SERVICOS as $srv) {
            if (str_contains($textoUp, $srv)) {
                return $srv;
            }
        }
        return null;
    }

    private function normalizarUnidade(?string $uni): ?string
    {
        if ($uni === null) return null;
        $uni = mb_strtolower(trim($uni));
        $map = [
            'quilograma'  => 'kg', 'quilogramas' => 'kg',
            'grama'       => 'g',  'gramas'      => 'g',
            'metro'       => 'm',  'metros'      => 'm',
            'centímetro'  => 'cm', 'centímetros' => 'cm',
            'milímetro'   => 'mm', 'milímetros'  => 'mm',
            'objetos'     => 'un', 'objeto'      => 'un',
            'unidades'    => 'un', 'unidade'     => 'un',
            'itens'       => 'un', 'item'        => 'un',
            'envios'      => 'un', 'envio'       => 'un',
            'por cento'   => '%',
        ];
        return $map[$uni] ?? $uni;
    }

    private function extrairTrecho(string $texto, string $match): string
    {
        $pos    = mb_strpos($texto, $match);
        $inicio = max(0, $pos - 60);
        $fim    = min(mb_strlen($texto), $pos + mb_strlen($match) + 60);
        return '...' . mb_substr($texto, $inicio, $fim - $inicio) . '...';
    }

    private function montarFonte(\CodeIgniter\Database\BaseConnection $db, array $item): string
    {
        $tipo  = $item['doc_tipo'];
        $docId = $item['doc_id'];

        if ($tipo === 'capitulo') {
            $row = $db->query(
                'SELECT c.numero AS cn, c.titulo AS ct, m.numero AS mn, m.titulo AS mt
                 FROM capitulos c JOIN modulos m ON m.id = c.modulo_id WHERE c.id = ?',
                [$docId]
            )->getRowArray();
            if ($row) {
                return "Módulo {$row['mn']} › Cap. {$row['cn']} › {$item['numero']}";
            }
        } else {
            $row = $db->query(
                'SELECT a.numero AS an, a.titulo AS at, m.numero AS mn
                 FROM anexos a
                 JOIN capitulos c ON c.id = a.capitulo_id
                 JOIN modulos m ON m.id = c.modulo_id
                 WHERE a.id = ?',
                [$docId]
            )->getRowArray();
            if ($row) {
                return "Módulo {$row['mn']} › Anx. {$row['an']} › {$item['numero']}";
            }
        }

        return "Item {$item['id']}";
    }
}
