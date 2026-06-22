<?php

namespace App\Controllers;

use App\Models\ManualModel;
use App\Models\ModuloModel;
use App\Models\CapituloModel;
use App\Models\AnexoModel;

class Manuais extends BaseController
{
    protected ManualModel  $manualModel;
    protected ModuloModel  $moduloModel;
    protected CapituloModel $capituloModel;
    protected AnexoModel   $anexoModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->manualModel   = new ManualModel();
        $this->moduloModel   = new ModuloModel();
        $this->capituloModel = new CapituloModel();
        $this->anexoModel    = new AnexoModel();
    }

    // ---------------------------------------------------------------
    // GET /manuais — Listagem de todos os manuais com árvore completa
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
    // GET /manuais/arvore/{id} — Árvore completa de um manual
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
    // GET /manuais/modulo/{id} — Detalhe de um módulo
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
    // GET /manuais/capitulo/{id} — Detalhe de um capítulo com anexos
    // ---------------------------------------------------------------
    public function capitulo(int $id): string
    {
        $capitulo = $this->capituloModel->comContexto($id);

        if (! $capitulo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound(
                "Capítulo #{$id} não encontrado."
            );
        }

        return view('manuais/capitulo', [
            'title'    => "Capítulo {$capitulo['numero']} — {$capitulo['titulo']}",
            'capitulo' => $capitulo,
        ]);
    }
}
