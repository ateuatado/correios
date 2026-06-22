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
        $this->tmpDir       = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'ooo_tmp';
        $this->db           = \Config\Database::connect();
        $this->capituloModel = new CapituloModel();
        $this->anexoModel   = new AnexoModel();
        $this->itemModel    = new ItemModel();

        if (! is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }

        // Validar LibreOffice
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

        // ── Modo: documento específico ───────────────────────────────
        if ($docOpt !== null) {
            [$tipo, $id] = explode(':', $docOpt);
            $this->processarDocumento($tipo, (int) $id);
        } else {
            // ── Modo: todos os documentos com arquivo vinculado ──────
            $this->processarTodos($limite);
        }

        // ── Limpar arquivos temporários ──────────────────────────────
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
    // Processa todos os documentos com arquivo vinculado
    // ================================================================
    private function processarTodos(int $limite): void
    {
        // Capítulos com arquivo ainda sem itens
        $subCap = "SELECT doc_id FROM itens WHERE doc_tipo = 'capitulo'";
        $capitulos = $this->capituloModel
            ->where('arquivo_caminho IS NOT NULL', null, false)
            ->where("id NOT IN ($subCap)", null, false)
            ->orderBy('id', 'ASC')
            ->findAll();

        // Anexos com arquivo ainda sem itens
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
    // Processa um único documento
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
            CLI::write("   ✗  {$tipo} #{$id}: arquivo não encontrado: " . basename($caminho), 'light_red');
            $this->cntErros++;
            return;
        }

        CLI::write("   → {$tipo} #{$id}: " . basename($caminho));

        // Converter para TXT via LibreOffice
        $txtPath = $this->converterParaTxt($caminho);
        if ($txtPath === null) {
            CLI::write("     ✗  Falha na conversão", 'light_red');
            $this->cntErros++;
            return;
        }

        // Ler e converter encoding
        $raw  = file_get_contents($txtPath);
        $text = iconv('Windows-1252', 'UTF-8//IGNORE', $raw);
        @unlink($txtPath);

        // Parsear itens
        $itens = $this->parsearItens($text, $tipo, $id);

        // Inserir em lote
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
    // Converte arquivo .doc/.docx → .txt via LibreOffice headless
    // ================================================================
    private function converterParaTxt(string $caminhoDoc): ?string
    {
        $extensao = strtolower(pathinfo($caminhoDoc, PATHINFO_EXTENSION));
        $nomeBase = pathinfo($caminhoDoc, PATHINFO_FILENAME);
        $txtEsperado = $this->tmpDir . DIRECTORY_SEPARATOR . $nomeBase . '.txt';

        // Se já convertido, reusar
        if (file_exists($txtEsperado) && filesize($txtEsperado) > 0) {
            return $txtEsperado;
        }

        // Para .docx podemos ler direto sem conversão
        if ($extensao === 'docx') {
            $txt = $this->lerDocxDireto($caminhoDoc);
            if ($txt !== null) {
                file_put_contents($txtEsperado, iconv('UTF-8', 'Windows-1252//IGNORE', $txt));
                return $txtEsperado;
            }
        }

        // LibreOffice headless
        $cmd = sprintf(
            '"%s" --headless --norestore --convert-to txt --outdir "%s" "%s" 2>&1',
            $this->soffice,
            $this->tmpDir,
            $caminhoDoc
        );

        // Matar instâncias presas
        exec('taskkill /F /IM soffice.exe /T >nul 2>&1');
        sleep(1);

        exec($cmd, $output, $code);

        if ($code !== 0 || ! file_exists($txtEsperado) || filesize($txtEsperado) === 0) {
            // Tentar uma vez mais
            sleep(2);
            exec($cmd, $output, $code);
        }

        return (file_exists($txtEsperado) && filesize($txtEsperado) > 0)
            ? $txtEsperado
            : null;
    }

    /**
     * Lê texto de um .docx diretamente via ZipArchive (sem LibreOffice)
     */
    private function lerDocxDireto(string $path): ?string
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return null;

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (! $xml) return null;

        $dom = new \DOMDocument();
        if (! @$dom->loadXML($xml)) return null;

        $ns    = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $paras = $dom->getElementsByTagNameNS($ns, 'p');
        $linhas = [];

        foreach ($paras as $p) {
            $txt = '';
            foreach ($p->getElementsByTagNameNS($ns, 't') as $t) {
                $txt .= $t->textContent;
            }
            $linhas[] = $txt; // mantém linhas vazias para preservar espaçamento
        }

        return implode("\r\n", $linhas);
    }

    // ================================================================
    // Parseia o texto convertido e retorna array de itens estruturados
    // ================================================================
    private function parsearItens(string $text, string $docTipo, int $docId): array
    {
        $linhas = preg_split('/\r?\n/', $text);
        $itens  = [];
        $ordem  = 0;

        // Pilha de contexto: [nivel => itemIndex]
        $pilha = [];

        // Buffer de conteúdo do item atual
        $itemAtual     = null;
        $conteudoBuffer = [];

        $fecharItem = function () use (&$itens, &$itemAtual, &$conteudoBuffer) {
            if ($itemAtual !== null) {
                $itens[$itemAtual]['conteudo'] = implode("\n", $conteudoBuffer);
                $itemAtual    = null;
                $conteudoBuffer = [];
            }
        };

        foreach ($linhas as $linha) {
            // Preservar linha original para conteúdo
            $linhaTrim  = rtrim($linha);
            $textoLimpo = trim($linhaTrim);
            $indent     = strlen($linhaTrim) - strlen(ltrim($linhaTrim));

            // ── Item nível 1: "    1 TITULO"  (indent 0-4, número simples) ──
            if (preg_match('/^\s{0,4}(\d+)\s{1,3}([A-ZÁÀÂÃÉÊÍÓÔÕÚÜÇ].{2,})/u', $linha, $m)) {
                $fecharItem();
                $num    = $m[1];
                $titulo = trim($m[2]);
                $paiId  = null;

                $novoIdx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo,
                    'doc_id'   => $docId,
                    'pai_id'   => null,   // pai_id resolvido após inserção
                    'nivel'    => 1,
                    'numero'   => $num,
                    'titulo'   => $titulo,
                    'conteudo' => '',
                    'ordem'    => $ordem++,
                    '_idx'     => $novoIdx,
                ];
                $pilha[1]  = $novoIdx;
                $pilha[2]  = null;
                $pilha[3]  = null;
                $itemAtual = $novoIdx;
                continue;
            }

            // ── Item nível 2: "        N.N texto" (indent ~8) ──────────────
            if (preg_match('/^\s{4,10}(\d+\.\d+)\s+(.+)/u', $linha, $m)) {
                $fecharItem();
                $num    = $m[1];
                $titulo = trim($m[2]);
                $paiKey = 1;

                $novoIdx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo,
                    'doc_id'   => $docId,
                    'pai_id'   => null, // resolvido depois
                    'nivel'    => 2,
                    'numero'   => $num,
                    'titulo'   => $titulo,
                    'conteudo' => '',
                    'ordem'    => $ordem++,
                    '_idx'     => $novoIdx,
                    '_pai_key' => $paiKey,
                ];
                $pilha[2]  = $novoIdx;
                $pilha[3]  = null;
                $itemAtual = $novoIdx;
                continue;
            }

            // ── Item nível 3: "            N.N.N texto" (indent ~12) ────────
            if (preg_match('/^\s{10,16}(\d+\.\d+\.\d+)\s+(.+)/u', $linha, $m)) {
                $fecharItem();
                $num    = $m[1];
                $titulo = trim($m[2]);

                $novoIdx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo,
                    'doc_id'   => $docId,
                    'pai_id'   => null,
                    'nivel'    => 3,
                    'numero'   => $num,
                    'titulo'   => $titulo,
                    'conteudo' => '',
                    'ordem'    => $ordem++,
                    '_idx'     => $novoIdx,
                    '_pai_key' => 2,
                ];
                $pilha[3]  = $novoIdx;
                $itemAtual = $novoIdx;
                continue;
            }

            // ── Alínea: "    a) texto" (indent 2-6) ──────────────────────────
            if (preg_match('/^\s{2,8}([a-z])\)\s+(.+)/u', $linha, $m)) {
                $fecharItem();
                $num    = $m[1];
                $titulo = trim($m[2]);

                // Pai = item atual mais próximo
                $paiKey = $pilha[3] !== null ? 3 : ($pilha[2] !== null ? 2 : 1);

                $novoIdx = count($itens);
                $itens[] = [
                    'doc_tipo' => $docTipo,
                    'doc_id'   => $docId,
                    'pai_id'   => null,
                    'nivel'    => 4,
                    'numero'   => $num,
                    'titulo'   => $titulo,
                    'conteudo' => '',
                    'ordem'    => $ordem++,
                    '_idx'     => $novoIdx,
                    '_pai_key' => $paiKey,
                ];
                $itemAtual = $novoIdx;
                continue;
            }

            // ── Conteúdo livre (continua item atual) ─────────────────────────
            if ($itemAtual !== null && $textoLimpo !== '') {
                $conteudoBuffer[] = $textoLimpo;
            }
        }

        $fecharItem();

        // Resolver pai_id usando índices relativos (_pai_key → pilha)
        // Refazer com pilha real
        $pilhaReal = [1 => null, 2 => null, 3 => null, 4 => null];
        foreach ($itens as &$item) {
            $nivel = (int) $item['nivel'];
            $paiKey = $item['_pai_key'] ?? null;

            if ($paiKey !== null && isset($pilhaReal[$paiKey])) {
                $item['_pai_idx'] = $pilhaReal[$paiKey];
            }

            $pilhaReal[$nivel] = $item['_idx'];
            // Limpar níveis filhos ao subir
            for ($n = $nivel + 1; $n <= 4; $n++) {
                $pilhaReal[$n] = null;
            }

            // Limpar campos internos
            unset($item['_idx'], $item['_pai_key']);
        }

        return $itens;
    }

    // ================================================================
    // Insere itens em lote, resolvendo pai_id via _pai_idx
    // ================================================================
    private function inserirItens(array $itens): void
    {
        // Mapa: posição no array => id real no banco
        $idMap = [];
        $db    = $this->db;

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
