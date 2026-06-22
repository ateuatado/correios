<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ManualModel;
use App\Models\ModuloModel;
use App\Models\CapituloModel;
use App\Models\AnexoModel;

/**
 * ManualImport — Importa a estrutura do MANCAT dos Correios para o banco de dados.
 *
 * Uso:
 *   php spark manual:import
 *   php spark manual:import --reset        (limpa e reimporta)
 *   php spark manual:import --fase 1       (só índice)
 *   php spark manual:import --fase 2       (só vincula arquivos)
 */
class ManualImport extends BaseCommand
{
    protected $group       = 'MANCAT';
    protected $name        = 'manual:import';
    protected $description = 'Importa a estrutura do MANCAT (índice + arquivos) para o banco de dados.';

    protected $usage   = 'manual:import [options]';
    protected $options = [
        '--reset' => 'Apaga os dados existentes antes de importar.',
        '--fase'  => 'Executa somente uma fase: 1=índice, 2=arquivos. Padrão: ambas.',
    ];

    // ── Configuração — ajuste estes caminhos se necessário ──────────
    private string $soffice  = 'C:\\Program Files (x86)\\OpenOffice 4\\program\\soffice.exe';
    private string $indexDoc = 'C:\\Correios\\mancat.docx';
    private string $docDir   = 'C:\\Correios\\mancat';
    private string $tmpDir   = '';   // preenchido em run()

    // ── Contadores ──────────────────────────────────────────────────
    private int $cntModulos   = 0;
    private int $cntCapitulos = 0;
    private int $cntAnexos    = 0;
    private int $cntArquivos  = 0;

    // ── Modelos e DB ─────────────────────────────────────────────────
    private ManualModel                         $manualModel;
    private ModuloModel                         $moduloModel;
    private CapituloModel                       $capituloModel;
    private AnexoModel                          $anexoModel;
    private \CodeIgniter\Database\BaseConnection $db;

    // ================================================================
    public function run(array $params): void
    {
        $this->tmpDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'ooo_tmp';
        if (! is_dir($this->tmpDir)) {
            mkdir($this->tmpDir, 0777, true);
        }

        $this->db            = \Config\Database::connect();
        $this->manualModel   = new ManualModel();
        $this->moduloModel   = new ModuloModel();
        $this->capituloModel = new CapituloModel();
        $this->anexoModel    = new AnexoModel();

        $fase  = CLI::getOption('fase');
        $reset = CLI::getOption('reset') !== null;

        CLI::write('');
        CLI::write('╔══════════════════════════════════════════════╗', 'yellow');
        CLI::write('║   MANCAT — Importador de Estrutura           ║', 'yellow');
        CLI::write('╚══════════════════════════════════════════════╝', 'yellow');
        CLI::write('');

        // --reset: limpa tabelas filhas primeiro (ordem de FK)
        if ($reset) {
            CLI::write('⚠  Limpando dados existentes...', 'light_red');
            $this->db->table('anexos')->emptyTable();
            $this->db->table('capitulos')->emptyTable();
            $this->db->table('modulos')->emptyTable();
            $this->db->table('manuais')->emptyTable();
            CLI::write('   Tabelas limpas.', 'green');
        }

        // ── Fase 1: Parsear índice e inserir hierarquia ──────────────
        if ($fase === null || $fase === '1') {
            CLI::write('');
            CLI::write('[ FASE 1 ] Parseando índice mancat.docx...', 'cyan');
            $manualId = $this->fase1ParsearIndice();

            if ($manualId === null) {
                CLI::error('Falha na Fase 1. Abortando.');
                return;
            }
        } else {
            // busca manual já existente
            $manualId = $this->manualModel->where('codigo', 'MANCAT')->first()['id'] ?? null;
            if (! $manualId) {
                CLI::error('Manual MANCAT não encontrado. Execute sem --fase primeiro.');
                return;
            }
        }

        // ── Fase 2: Varrer pasta e vincular arquivos ─────────────────
        if ($fase === null || $fase === '2') {
            CLI::write('');
            CLI::write('[ FASE 2 ] Vinculando arquivos da pasta mancat/...', 'cyan');
            $this->fase2VincularArquivos();
        }

        // ── Resumo ───────────────────────────────────────────────────
        CLI::write('');
        CLI::write('╔══════════════════════════════════════════════╗', 'green');
        CLI::write('║   IMPORTAÇÃO CONCLUÍDA                       ║', 'green');
        CLI::write('╠══════════════════════════════════════════════╣', 'green');
        CLI::write("║  Módulos  : {$this->cntModulos}", 'green');
        CLI::write("║  Capítulos: {$this->cntCapitulos}", 'green');
        CLI::write("║  Anexos   : {$this->cntAnexos}", 'green');
        CLI::write("║  Arquivos : {$this->cntArquivos}", 'green');
        CLI::write('╚══════════════════════════════════════════════╝', 'green');
        CLI::write('');
    }

    // ================================================================
    // FASE 1 — Parsear mancat.docx e inserir Manual/Módulos/Capítulos/Anexos
    // ================================================================
    private function fase1ParsearIndice(): ?int
    {
        if (! file_exists($this->indexDoc)) {
            CLI::error("Arquivo não encontrado: {$this->indexDoc}");
            return null;
        }

        // Ler parágrafos do .docx
        $linhas = $this->lerDocx($this->indexDoc);
        if (empty($linhas)) {
            CLI::error('Não foi possível ler o mancat.docx');
            return null;
        }

        CLI::write("   {$linhas[0]} — {$linhas[1]}", 'light_gray');

        // ── Inserir ou recuperar o Manual ────────────────────────────
        $manual = $this->manualModel->where('codigo', 'MANCAT')->first();
        if (! $manual) {
            // Extrair sumário do documento
            $sumario = $this->extrairSumario($linhas);

            $manualId = $this->manualModel->insert([
                'codigo'  => 'MANCAT',
                'nome'    => 'Manual de Atendimento Comercial dos Correios',
                'sumario' => $sumario,
            ], true);

            CLI::write("   ✔ Manual MANCAT inserido (id=$manualId)", 'green');
        } else {
            $manualId = $manual['id'];
            CLI::write("   ℹ Manual MANCAT já existe (id=$manualId)", 'light_yellow');
        }

        // ── Parsear hierarquia ───────────────────────────────────────
        $moduloAtualId   = null;
        $capituloAtualId = null;
        $moduloNumAtual  = null;

        // Mapas para evitar duplicatas
        $modulosInseridos   = [];  // numero => id
        $capitulosInseridos = [];  // "modulo_id-numero" => id

        foreach ($linhas as $linha) {
            $linha = trim($linha);
            if ($linha === '') continue;

            // ── Módulo N - Título
            if (preg_match('/^[Mm][oó]dulo\s+(\d+)\s*[-–]\s*(.+)$/u', $linha, $m)) {
                $num    = (int) $m[1];
                $titulo = trim($m[2]);

                if (! isset($modulosInseridos[$num])) {
                    $id = $this->moduloModel->insert([
                        'manual_id' => $manualId,
                        'numero'    => $num,
                        'titulo'    => $titulo,
                    ], true);
                    $modulosInseridos[$num] = $id;
                    $this->cntModulos++;
                    CLI::write("   + Módulo $num: $titulo", 'light_cyan');
                }

                $moduloAtualId   = $modulosInseridos[$num];
                $moduloNumAtual  = $num;
                $capituloAtualId = null;
                continue;
            }

            // ── Capítulo N - Título
            if (preg_match('/^Cap[ií]tulo\s+(\d+)\s*[-–]\s*(.+)$/u', $linha, $m)) {
                $num    = (int) $m[1];
                $titulo = trim($m[2]);

                if ($moduloAtualId === null) continue;

                $chave = "{$moduloAtualId}-{$num}";
                if (! isset($capitulosInseridos[$chave])) {
                    $id = $this->capituloModel->insert([
                        'modulo_id' => $moduloAtualId,
                        'numero'    => $num,
                        'titulo'    => $titulo,
                    ], true);
                    $capitulosInseridos[$chave] = $id;
                    $this->cntCapitulos++;
                    CLI::write("     - Cap $num: " . mb_substr($titulo, 0, 70), 'white');
                }

                $capituloAtualId = $capitulosInseridos[$chave];
                continue;
            }

            // ── Anexo N - Título
            if (preg_match('/^Anexo\s*(\d+)\s*[-–]\s*(.+)$/u', $linha, $m)) {
                $num    = (int) $m[1];
                $titulo = trim($m[2]);

                if ($capituloAtualId === null) continue;

                // Verificar duplicata
                $existe = $this->anexoModel
                    ->where('capitulo_id', $capituloAtualId)
                    ->where('numero', $num)
                    ->first();

                if (! $existe) {
                    $this->anexoModel->insert([
                        'capitulo_id' => $capituloAtualId,
                        'numero'      => $num,
                        'titulo'      => $titulo,
                    ]);
                    $this->cntAnexos++;
                    CLI::write("       · Anx $num: " . mb_substr($titulo, 0, 65), 'light_gray');
                }
                continue;
            }
        }

        return $manualId;
    }

    // ================================================================
    // FASE 2 — Varrer pasta mancat/ e vincular arquivos
    // ================================================================
    private function fase2VincularArquivos(): void
    {
        if (! is_dir($this->docDir)) {
            CLI::error("Pasta não encontrada: {$this->docDir}");
            return;
        }

        $arquivos = glob($this->docDir . DIRECTORY_SEPARATOR . 'mancat-modulo-*.{doc,docx}', GLOB_BRACE);
        if (! $arquivos) {
            CLI::write('   Nenhum arquivo encontrado.', 'light_yellow');
            return;
        }

        CLI::write('   Encontrados: ' . count($arquivos) . ' arquivos.', 'white');

        foreach ($arquivos as $caminho) {
            $nome = basename($caminho);

            // Padrão: mancat-modulo-NN-capitulo-NNN[_anexo-NN][-1].docx?
            // Ex: mancat-modulo-06-capitulo-028-1.doc
            // Ex: mancat-modulo-06-capitulo-028_anexo-04-1.doc
            $pattern = '/^mancat-modulo-(\d+)-capitulo-(\d+)(?:-\d+)?(?:_anexo-(\d+)(?:-\d+)?)?\.docx?$/i';

            if (! preg_match($pattern, $nome, $m)) {
                // Padrão copy_of / copy2_of — também tentar
                $nomeClean = preg_replace('/^copy\d*_of_/', '', $nome);
                if (! preg_match($pattern, $nomeClean, $m)) {
                    CLI::write("   ⚠  Padrão não reconhecido: $nome", 'light_red');
                    continue;
                }
            }

            $modNum = (int) $m[1];
            $capNum = (int) $m[2];
            $anxNum = isset($m[3]) && $m[3] !== '' ? (int) $m[3] : null;

            if ($anxNum !== null) {
                // É um Anexo → localizar por modulo+capitulo+numero
                $capitulo = $this->db->table('capitulos c')
                    ->join('modulos mo', 'mo.id = c.modulo_id')
                    ->join('manuais ma', 'ma.id = mo.manual_id')
                    ->where('ma.codigo', 'MANCAT')
                    ->where('mo.numero', $modNum)
                    ->where('c.numero', $capNum)
                    ->select('c.id')
                    ->get()->getRowArray();

                if (! $capitulo) {
                    CLI::write("   ⚠  Capítulo não encontrado para $nome", 'light_yellow');
                    continue;
                }

                $existeAnexo = $this->anexoModel
                    ->where('capitulo_id', $capitulo['id'])
                    ->where('numero', $anxNum)
                    ->first();

                if ($existeAnexo) {
                    $this->anexoModel->update($existeAnexo['id'], [
                        'arquivo_nome'    => $nome,
                        'arquivo_caminho' => $caminho,
                    ]);
                } else {
                    // Arquivo existe mas não estava no índice → inserir
                    $this->anexoModel->insert([
                        'capitulo_id'     => $capitulo['id'],
                        'numero'          => $anxNum,
                        'titulo'          => "Anexo $anxNum",
                        'arquivo_nome'    => $nome,
                        'arquivo_caminho' => $caminho,
                    ]);
                    $this->cntAnexos++;
                }
                $this->cntArquivos++;

            } else {
                // É um Capítulo → localizar por modulo+numero
                $row = $this->db->table('capitulos c')
                    ->join('modulos mo', 'mo.id = c.modulo_id')
                    ->join('manuais ma', 'ma.id = mo.manual_id')
                    ->where('ma.codigo', 'MANCAT')
                    ->where('mo.numero', $modNum)
                    ->where('c.numero', $capNum)
                    ->select('c.id')
                    ->get()->getRowArray();

                if (! $row) {
                    CLI::write("   ⚠  Capítulo não encontrado: mod=$modNum cap=$capNum ($nome)", 'light_yellow');
                    continue;
                }

                $this->capituloModel->update($row['id'], [
                    'arquivo_nome'    => $nome,
                    'arquivo_caminho' => $caminho,
                ]);
                $this->cntArquivos++;
            }

            CLI::write("   ✔ $nome", 'dark_gray');
        }
    }

    // ================================================================
    // Helpers
    // ================================================================

    /**
     * Lê parágrafos de um arquivo .docx usando ZipArchive
     */
    private function lerDocx(string $path): array
    {
        $zip = new \ZipArchive();
        if ($zip->open($path) !== true) return [];

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        $dom = new \DOMDocument();
        if (! @$dom->loadXML($xml)) return [];

        $ns = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';
        $paras = $dom->getElementsByTagNameNS($ns, 'p');
        $linhas = [];

        foreach ($paras as $p) {
            $txt = '';
            foreach ($p->getElementsByTagNameNS($ns, 't') as $t) {
                $txt .= $t->textContent;
            }
            if (trim($txt) !== '') {
                $linhas[] = $txt;
            }
        }

        return $linhas;
    }

    /**
     * Extrai um texto de sumário das primeiras linhas do documento índice
     */
    private function extrairSumario(array $linhas): string
    {
        $sumario = [];
        foreach (array_slice($linhas, 0, 10) as $linha) {
            $l = trim($linha);
            if ($l && ! preg_match('/^(Módulo|Capítulo|Anexo)\s+\d+/ui', $l)) {
                $sumario[] = $l;
            }
        }
        return implode(' ', $sumario);
    }

}
