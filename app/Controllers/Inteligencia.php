<?php

namespace App\Controllers;

use App\Models\RegraModel;

class Inteligencia extends BaseController
{
    private RegraModel $regraModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface           $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->regraModel = new RegraModel();
    }

    // GET /inteligencia — Dashboard
    public function index(): string
    {
        $tipoInfo = RegraModel::tipoInfo();
        $porTipo  = $this->regraModel->porTipo();
        $porServico = $this->regraModel->porServico();
        $totalRegras = array_sum(array_column($porTipo, 'total'));

        // Enriquece tipos com info visual
        foreach ($porTipo as &$t) {
            $t['info'] = $tipoInfo[$t['tipo']] ?? $tipoInfo['outro'];
        }

        return view('inteligencia/index', [
            'title'       => 'Inteligência — CorreiosComercial',
            'tipoInfo'    => $tipoInfo,
            'porTipo'     => $porTipo,
            'porServico'  => $porServico,
            'totalRegras' => $totalRegras,
        ]);
    }

    // GET /inteligencia/regras — Lista com filtros
    public function regras(): string
    {
        $filtros = [
            'tipo'    => $this->request->getGet('tipo'),
            'servico' => $this->request->getGet('servico'),
            'q'       => $this->request->getGet('q'),
            'pagina'  => (int) ($this->request->getGet('p') ?? 1),
        ];

        $resultado = $this->regraModel->filtrar($filtros, 50);
        $servicos  = $this->regraModel->servicosUnicos();
        $tipoInfo  = RegraModel::tipoInfo();

        return view('inteligencia/regras', [
            'title'    => 'Regras extraídas — Inteligência',
            'filtros'  => $filtros,
            'regras'   => $resultado['regras'],
            'total'    => $resultado['total'],
            'pagina'   => $resultado['pagina'],
            'perPage'  => $resultado['perPage'],
            'servicos' => $servicos,
            'tipoInfo' => $tipoInfo,
        ]);
    }

    // GET /inteligencia/comparar — Comparativo de serviços
    public function comparar(): string
    {
        $servicosSel = $this->request->getGet('s') ?? [];
        if (is_string($servicosSel)) {
            $servicosSel = [$servicosSel];
        }

        $resultado = ! empty($servicosSel)
            ? $this->regraModel->comparar($servicosSel)
            : [];

        $todosServicos = $this->regraModel->servicosUnicos();
        $tipoInfo      = RegraModel::tipoInfo();

        return view('inteligencia/comparar', [
            'title'        => 'Comparativo de Serviços — Inteligência',
            'servicosSel'  => $servicosSel,
            'todosServicos'=> $todosServicos,
            'resultado'    => $resultado,
            'tipoInfo'     => $tipoInfo,
        ]);
    }

    // GET /inteligencia/servico/{nome} — Ficha de um serviço
    public function servico(string $nome): string
    {
        $nome = strtoupper(urldecode($nome));
        $resultado = $this->regraModel->comparar([$nome]);
        $tipoInfo  = RegraModel::tipoInfo();

        return view('inteligencia/servico', [
            'title'    => "Ficha: {$nome} — Inteligência",
            'servico'  => $nome,
            'resultado'=> $resultado,
            'tipoInfo' => $tipoInfo,
        ]);
    }
}
