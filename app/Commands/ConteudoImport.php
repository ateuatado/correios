<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CapituloModel;
use App\Models\AnexoModel;
use App\Models\ItemModel;

/**
 * ConteudoImport — Extrai itens/subitens dos documentos .doc/.docx via LibreOffice headless.
 *
 * Uso:
 *   php spark conteudo:import                    ← processa todos os documentos pendentes
 *   php spark conteudo:import --reset            ← limpa itens e reprocessa tudo
 *   php spark conteudo:import --doc capitulo:5   ← processa apenas o capitulo id=5
 *   php spark conteudo:import --doc anexo:12     ← processa apenas o anexo id=12
 *   php spark conteudo:import --limite 10        ← processa até N documentos
 */
class ConteudoImport extends BaseCommand
{
    protected $group       = 'MANCAT';
    protected $name        = 'conteudo:import';
    protected $description = 'Extrai itens/subitens dos .doc/.docx via LibreOffice headless.';

    protected $usage   = 'conteudo:import [options]';
    protected $options = [
        '--reset'  => 'Apaga todos os itens antes de reprocessar.',
        '--doc'    => 'Processa um documento específico: capitulo:N ou anexo:N.',
        '--limite' => 'Máximo de documentos a processar por execução.',
    ];

    // ── Configuração ────────────────────────────────────────────────
    private string $soffice = 'C:\\Program Files\\LibreOffice\\program\\soffice.exe';
    private string $tmpDir  = '';

    // ── Contadores ──────────────────────────────────────────────────
    private int $cntDocs  = 0;
    private int $cntItens = 0;
    private int $cntErros = 0;

    // ── Modelos ─────────────────────────────────────────────────────
    private CapituloModel $capituloModel;
    private AnexoModel    $anexoModel;
    private ItemModel     $itemModel;
    private \CodeIgniter\Database\BaseConnection $db;

    // ================================================================
    public function run(array $params): void
    {
        $this->tmpDir        = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'ooo_tmp';
        $this->db            = \Config\Database::connect();
        $this->capituloModel = new CapituloModel();
        $this->anexoModel    = new AnexoModel();
        $this->itemModel     = new ItemModel();

        if (! is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }

        if (! file_exists($this->soffice)) {
            CLI::error("LibreOffice não encontrado em: {$this->soffice}");
            return;
        }

        $reset  = CLI::getOption('reset') !== null;
        $docOpt = CLI::getOption('doc');
        $limite = (int) (CLI::getOption('limite') ?? 9999);

        CLI::write('');
        CLI::write('╔══════════════════════════════════════════════╗', 'yellow');
        CLI::write('║   MANCAT — Extração de Conteúdo              ║', 'yellow');
        CLI::write('║   LibreOffice Headless                       ║', 'yellow');
        CLI::write('╚══════════════════════════════════════════════╝', 'yellow');
        CLI::write('');

        if ($reset) {
            CLI::write('⚠  Limpando tabela itens...', 'light_red');
            $this->db->table('itens')->emptyTable();
            CLI::write('   Feito.', 'green');
        }

        if ($docOpt !== null) {
            [$tipo, $id] = explode(':', $docOpt);
            $this->processarDocumento($tipo, (int) $id);
        } else {
            $this->processarTodos($limite);
        }

        // Limpar TXTs temporários
        foreach (glob($this->tmpDir . DIRECTORY_SEPARATOR . '*.txt') as $f) {
            @unlink($f);
        }

        CLI::write('');
        CLI::write('╔══════════════════════════════════════════════╗', 'green');
        CLI::write('║   EXTRAÇÃO CONCLUÍDA                         ║', 'green');
        CLI::write('╠══════════════════════════════════════════════╣', 'green');
        CLI::write("║  Documentos : {$this->cntDocs}", 'green');
        CLI::write("║  Itens      : {$this->cntItens}", 'green');
        CLI::write("║  Erros      : {$this->cntErros}", 'green');
        CLI::write('╚══════════════════════════════════════════════╝', 'green');
    }

    // ================================================================
    private function processarTodos(int $limite): void
    {
        $subCap    = "SELECT doc_id FROM itens WHERE doc_tipo = 'capitulo'";
        $capitulos = $this->capituloModel
            ->where('arquivo_caminho IS NOT NULL', null, false)
            ->where("id NOT IN ($subCap)", null, false)
            ->orderBy('id', 'ASC')
            ->findAll();

        $subAnx = "SELECT doc_id FROM itens WHERE doc_tipo = 'anexo'";
        $anexos = $this->anexoModel
            ->where('arquivo_caminho IS NOT NULL', null, false)
            ->where("id NOT IN ($subAnx)", null, false)
            ->orderBy('id', 'ASC')
            ->findAll();

        $total = count($capitulos) + count($anexos);
        CLI::write("   Documentos pendentes: $total (cap: " . count($capitulos) . ", anx: " . count($anexos) . ")", 'white');
        CLI::write('');

        $processados = 0;
        foreach ($capitulos as $cap) {
            if ($processados >= $limite) break;
            $this->processarDocumento('capitulo', (int) $cap['id'], $cap);
            $processados++;
        }
        foreach ($anexos as $anx) {
            if ($processados >= $limite) break;
            $this->processarDocumento('anexo', (int) $anx['id'], $anx);
            $processados++;
        }
    }

    // ================================================================
    private function processarDocumento(string $tipo, int $id, ?array $doc = null): void
    {
        if ($doc === null) {
            $model = $tipo === 'capitulo' ? $this->capituloModel : $this->anexoModel;
            $doc   = $model->find($id);
        }

        if (! $doc || empty($doc['arquivo_caminho'])) {
            CLI::write("   ⚠  {$tipo} #{$id}: sem arquivo vinculado.", 'light_yellow');
            $this->cntErros++;
            return;
        }

        $caminho = $doc['arquivo_caminho'];
        if (! file_exists($caminho)) {
            CLI::write("   ✗  {$tipo} #{$id}: não encontrado: " . basename($caminho), 'light_red');
            $this->cntErros++;
            return;
        }

        CLI::write("   → {$tipo} #{$id}: " . basename($caminho));

        $txtPath = $this->converterParaTxt($caminho);
        if ($txtPath === null) {
            CLI::write("     ✗  Falha na conversão", 'light_red');
            $this->cntErros++;
            return;
        }

        $raw  = file_get_contents($txtPath);
        $text = iconv('Windows-1252', 'UTF-8//IGNORE', $raw);
        @unlink($txtPath);

        $itens = $this->parsearItens($text, $tipo, $id);

        if (! empty($itens)) {
            $this->inserirItens($itens);
            CLI::write("     ✔  " . count($itens) . " itens extraídos.", 'green');
            $this->cntItens += count($itens);
        } else {
            CLI::write("     ℹ  Nenhum item estruturado encontrado.", 'light_yellow');
        }

        $this->cntDocs++;
    }

    // ================================================================
    private function converterParaTxt(string $caminhoDoc): ?string
    {
        $extensao    = strtolower(pathinfo($caminhoDoc, PATHINFO_EXTENSION));
        $nomeBase    = pathinfo($caminhoDoc, PATHINFO_FILENAME);
        $txtEsperado = $this->tmpDir . DIRECTORY_SEPARATOR . $nomeBase . '.txt';

        if (file_exists($txtEsperado) && filesize($txtEsperado) > 0) {
            return $txtEsperado;
        }

        // .docx: leitura direta via ZipArchive
        if ($extensao === 'docx') {
            $txt = $this->lerDocxDireto($caminhoDoc);
            if ($txt !== null) {
                file_put_contents($txtEsperado, iconv('UTF-8', 'Windows-1252//IGNORE', $txt));
                return $txtEsperado;
            }
        }

        // .doc: LibreOffice headless
        $cmd = sprintf(
            '"%s" --headless --norestore --convert-to txt --outdir "%s" "%s" 2>&1',
            $this->soffice,
            $this->tmpDir,
            $caminhoDoc
        );

        exec('taskkill /F /IM soffice.exe /T >nul 2>&1');
        sleep(1);
        exec($cmd, $output, $code);

        if (! file_exists($txtEsperado) || filesize($txtEsperado) === 0) {
            sleep(2);
            exec($cmd, $output2, $code2);
        }

        return (file_exists($txtEsperado) && filesize($txtEsperado) > 0) ? $txtEsperado : null;
    }

    // ================================================================
    private function lerDocxDireto(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return null;

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();
        if (! $xml) return null;

        $dom = new \DOMDocument();
        if (! @$dom->loadXML($xml)) return null;

        $ns     = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $paras  = $dom->getElementsByTagNameNS($ns, 'p');
        $linhas = [];

        foreach ($paras as $p) {
            $txt = '';
            foreach ($p->getElementsByTagNameNS($ns, 't') as $t) {
                $txt .= $t->textContent;
            }
            $linhas[] = $txt;
        }

        return implode("\r\n", $linhas);
    }

    // ================================================================
    // PARSER DE ITENS — 4 níveis hierárquicos
    // ================================================================
    private function parsearItens(string $text, string $docTipo, int $docId): array
    {
        $linhas = preg_split('/\r?\n/', $text);
        $itens  = [];
        $ordem  = 0;

        // Pilha: nivel => índice no array $itens do último item daquele nível.
        // TODOS os níveis inicializados como null — evita "Undefined array key".
        $pilha = [1 => null, 2 => null, 3 => null, 4 => null];

        $itemAtual      = null;
        $conteudoBuffer = [];

        $fecharItem = function () use (&$itens, &$itemAtual, &$conteudoBuffer) {
            if ($itemAtual !== null) {
                $itens[$itemAtual]['conteudo'] = implode("\n", array_filter(
                    $conteudoBuffer,
                    fn ($l) => $l !== ''
                ));
                $itemAtual      = null;
                $conteudoBuffer = [];
            }
        };

        foreach ($linhas as $linha) {
            $linhaTrim  = rtrim($linha);
            $textoLimpo = trim($linhaTrim);

            // ── Linha vazia ────────────────────────────────────────────────
            if ($textoLimpo === '') {
                if ($itemAtual !== null) {
                    $conteudoBuffer[] = '';
                }
                continue;
            }

            // ── Nível 1 — "  1 TITULO" (dígito + espaço + maiúscula) ──────
            if (preg_match('/^\s{0,4}(\d+)\s{1,3}([A-ZÁÀÂÃÉÊÍÓÔÕÚÜÇÑ].{1,})/u', $linha, $m)) {
                $fecharItem();
                $idx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo, 'doc_id' => $docId,
                    'pai_id'   => null,     'nivel'  => 1,
                    'numero'   => $m[1],    'titulo' => trim($m[2]),
                    'conteudo' => '',        'ordem'  => $ordem++,
                    '_idx' => $idx,
                ];
                $pilha[1] = $idx;
                $pilha[2] = $pilha[3] = $pilha[4] = null;
                $itemAtual = $idx;
                continue;
            }

            // ── Nível 1 — "  1) TITULO" (dígito + parêntese) ─────────────
            if (preg_match('/^\s{0,6}(\d+)\)\s+(\S.{1,})/u', $linha, $m)) {
                $fecharItem();
                $idx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo, 'doc_id' => $docId,
                    'pai_id'   => null,     'nivel'  => 1,
                    'numero'   => $m[1],    'titulo' => trim($m[2]),
                    'conteudo' => '',        'ordem'  => $ordem++,
                    '_idx' => $idx,
                ];
                $pilha[1] = $idx;
                $pilha[2] = $pilha[3] = $pilha[4] = null;
                $itemAtual = $idx;
                continue;
            }

            // ── Nível 2 — "        N.N texto" ────────────────────────────
            if (preg_match('/^\s{4,10}(\d+\.\d+)\s+(.+)/u', $linha, $m)) {
                $fecharItem();
                $idx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo, 'doc_id'   => $docId,
                    'pai_id'   => null,     'nivel'    => 2,
                    'numero'   => $m[1],    'titulo'   => trim($m[2]),
                    'conteudo' => '',        'ordem'    => $ordem++,
                    '_idx' => $idx, '_pai_key' => 1,
                ];
                $pilha[2] = $idx;
                $pilha[3] = $pilha[4] = null;
                $itemAtual = $idx;
                continue;
            }

            // ── Nível 3 — "            N.N.N texto" ──────────────────────
            if (preg_match('/^\s{10,16}(\d+\.\d+\.\d+)\s+(.+)/u', $linha, $m)) {
                $fecharItem();
                $idx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo, 'doc_id'   => $docId,
                    'pai_id'   => null,     'nivel'    => 3,
                    'numero'   => $m[1],    'titulo'   => trim($m[2]),
                    'conteudo' => '',        'ordem'    => $ordem++,
                    '_idx' => $idx, '_pai_key' => 2,
                ];
                $pilha[3] = $idx;
                $pilha[4] = null;
                $itemAtual = $idx;
                continue;
            }

            // ── Alínea — "    a) texto" ───────────────────────────────────
            if (preg_match('/^\s{2,8}([a-z])\)\s+(.+)/u', $linha, $m)) {
                $fecharItem();

                // Pai = nível mais profundo disponível na pilha
                if (($pilha[3] ?? null) !== null) {
                    $paiKey = 3;
                } elseif (($pilha[2] ?? null) !== null) {
                    $paiKey = 2;
                } elseif (($pilha[1] ?? null) !== null) {
                    $paiKey = 1;
                } else {
                    $paiKey = null; // sem pai identificado
                }

                $idx = count($itens);
                $itens[] = [
                    'doc_tipo'  => $docTipo, 'doc_id'   => $docId,
                    'pai_id'    => null,     'nivel'    => 4,
                    'numero'    => $m[1],    'titulo'   => trim($m[2]),
                    'conteudo'  => '',        'ordem'    => $ordem++,
                    '_idx' => $idx, '_pai_key' => $paiKey,
                ];
                $pilha[4] = $idx;
                $itemAtual = $idx;
                continue;
            }

            // ── Conteúdo livre ────────────────────────────────────────────
            if ($itemAtual !== null) {
                $conteudoBuffer[] = $textoLimpo;
            }
        }

        $fecharItem();

        // ── Resolver pai_id usando pilha de execução ──────────────────
        $pilhaReal = [1 => null, 2 => null, 3 => null, 4 => null];

        foreach ($itens as &$item) {
            $nivel  = (int) $item['nivel'];
            $paiKey = $item['_pai_key'] ?? null;

            if ($paiKey !== null && ($pilhaReal[$paiKey] ?? null) !== null) {
                $item['_pai_idx'] = $pilhaReal[$paiKey];
            }

            $pilhaReal[$nivel] = $item['_idx'] ?? null;
            for ($n = $nivel + 1; $n <= 4; $n++) {
                $pilhaReal[$n] = null;
            }

            unset($item['_idx'], $item['_pai_key']);
        }

        return $itens;
    }

    // ================================================================
    private function inserirItens(array $itens): void
    {
        $idMap = [];

        foreach ($itens as $pos => $item) {
            $paiId = null;
            if (isset($item['_pai_idx']) && $item['_pai_idx'] !== null) {
                $paiId = $idMap[$item['_pai_idx']] ?? null;
            }

            unset($item['_pai_idx']);
            $item['pai_id'] = $paiId;

            $id = $this->itemModel->insert($item, true);
            $idMap[$pos] = $id;
        }
    }
}
