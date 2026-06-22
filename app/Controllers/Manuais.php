<?php

namespace App\Controllers;

use App\Models\ManualModel;
use App\Models\ModuloModel;
use App\Models\CapituloModel;
use App\Models\AnexoModel;
use App\Models\ItemModel;
use App\Models\BuscaModel;

class Manuais extends BaseController
{
    protected ManualModel   $manualModel;
    protected ModuloModel   $moduloModel;
    protected CapituloModel $capituloModel;
    protected AnexoModel    $anexoModel;
    protected ItemModel     $itemModel;
    protected BuscaModel    $buscaModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface           $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->manualModel   = new ManualModel();
        $this->moduloModel   = new ModuloModel();
        $this->capituloModel = new CapituloModel();
        $this->anexoModel    = new AnexoModel();
        $this->itemModel     = new ItemModel();
        $this->buscaModel    = new BuscaModel();
    }

    // ---------------------------------------------------------------
    // GET /manuais
    // ---------------------------------------------------------------
    public function index(): string
    {
        $manuais = $this->manualModel->listarComContagem();

        return view('manuais/index', [
            'title'   => 'Manuais',
            'manuais' => $manuais,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /manuais/arvore/{id}
    // ---------------------------------------------------------------
    public function arvore(int $id): string
    {
        $manual = $this->manualModel->arvoreCompleta($id);

        if (! $manual) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Manual #{$id} não encontrado."
            );
        }

        return view('manuais/arvore', [
            'title'  => $manual['nome'],
            'manual' => $manual,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /manuais/modulo/{id}
    // ---------------------------------------------------------------
    public function modulo(int $id): string
    {
        $modulo = $this->moduloModel->comCapitulosEAnexos($id);

        if (! $modulo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Módulo #{$id} não encontrado."
            );
        }

        $manual = $this->manualModel->find($modulo['manual_id']);

        return view('manuais/modulo', [
            'title'  => "Módulo {$modulo['numero']} — {$modulo['titulo']}",
            'modulo' => $modulo,
            'manual' => $manual,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /manuais/capitulo/{id}
    // ---------------------------------------------------------------
    public function capitulo(int $id): string
    {
        $capitulo = $this->capituloModel->comContexto($id);

        if (! $capitulo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Capítulo #{$id} não encontrado."
            );
        }

        // Itens/subitens deste capítulo
        $itens = $this->itemModel->arvore('capitulo', $id);

        // Anexos vinculados
        $anexos = $this->anexoModel
            ->where('capitulo_id', $id)
            ->orderBy('numero', 'ASC')
            ->findAll();

        return view('manuais/leitura', [
            'title'        => "Cap. {$capitulo['numero']} — {$capitulo['titulo']}",
            'tipo'         => 'capitulo',
            'doc'          => $capitulo,
            'itens'        => $itens,
            'anexos'       => $anexos,
            'contexto'     => $this->_breadcrumb($capitulo),
        ]);
    }

    // ---------------------------------------------------------------
    // GET /manuais/anexo/{id}
    // ---------------------------------------------------------------
    public function anexo(int $id): string
    {
        $anexo = $this->anexoModel->find($id);

        if (! $anexo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Anexo #{$id} não encontrado."
            );
        }

        $capitulo = $this->capituloModel->comContexto((int) $anexo['capitulo_id']);
        $itens    = $this->itemModel->arvore('anexo', $id);

        return view('manuais/leitura', [
            'title'    => "Anexo {$anexo['numero']} — {$anexo['titulo']}",
            'tipo'     => 'anexo',
            'doc'      => $anexo,
            'itens'    => $itens,
            'anexos'   => [],
            'contexto' => $this->_breadcrumb($capitulo, $anexo),
        ]);
    }

    // ---------------------------------------------------------------
    // POST /manuais/buscar
    // GET  /manuais/buscar?q=...
    // ---------------------------------------------------------------
    public function buscar(): string
    {
        $q        = trim((string) $this->request->getVar('q'));
        $manualId = (int) ($this->request->getVar('manual_id') ?? 0) ?: null;

        $resultados = [];
        if (mb_strlen($q) >= 3) {
            $resultados = $this->buscaModel->buscarItens($q, $manualId, 40);

            // Logar busca para análise futura
            $this->buscaModel->insert([
                'manual_id' => $manualId,
                'query'     => $q,
                'itens_ids' => json_encode(array_column($resultados, 'id')),
                'score'     => count($resultados) > 0 ? 1.0 : 0.0,
            ]);
        }

        $manuais = $this->manualModel->findAll();

        return view('manuais/busca', [
            'title'      => $q ? 'Busca: "' . $q . '"' : 'Pesquisar no MANCAT',
            'q'          => $q,
            'manual_id'  => $manualId,
            'manuais'    => $manuais,
            'resultados' => $resultados,
        ]);
    }

    // ---------------------------------------------------------------
    // AJAX GET /manuais/api/item/{id} — para futuro contexto AI
    // ---------------------------------------------------------------
    public function apiItem(int $id): \CodeIgniter\HTTP\ResponseInterface
    {
        $item = $this->itemModel->find($id);

        if (! $item) {
            return $this->response->setJSON(['erro' => 'Item não encontrado'])->setStatusCode(404);
        }

        // Filhos diretos
        $item['filhos'] = $this->itemModel->filhos($id);

        return $this->response->setJSON($item);
    }

    // ---------------------------------------------------------------
    // Helpers privados
    // ---------------------------------------------------------------
    private function _breadcrumb(array $capitulo, ?array $anexo = null): array
    {
        return [
            'manual'   => ['id' => $capitulo['manual_id']  ?? null, 'nome' => $capitulo['manual_nome']  ?? 'MANCAT'],
            'modulo'   => ['id' => $capitulo['modulo_id']  ?? null, 'numero' => $capitulo['modulo_numero'] ?? '', 'titulo' => $capitulo['modulo_titulo'] ?? ''],
            'capitulo' => ['id' => $capitulo['id'],                  'numero' => $capitulo['numero'],            'titulo' => $capitulo['titulo']],
            'anexo'    => $anexo,
        ];
    }
}
