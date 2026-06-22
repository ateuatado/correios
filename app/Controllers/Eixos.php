<?php

namespace App\Controllers;

use App\Models\EixoModel;
use App\Models\IdeiaModel;

class Eixos extends BaseController
{
    private EixoModel  $eixoModel;
    private IdeiaModel $ideiaModel;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface  $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface           $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->eixoModel  = new EixoModel();
        $this->ideiaModel = new IdeiaModel();
    }

    // GET /eixos
    public function index(): string
    {
        $eixos = $this->eixoModel->orderBy('ordem')->findAll();
        return view('eixos/index', [
            'title' => 'Gerenciar Eixos',
            'eixos' => $eixos,
        ]);
    }

    // GET /eixos/novo
    public function novo(): string
    {
        return view('eixos/form', [
            'title' => 'Novo Eixo',
            'eixo'  => null,
        ]);
    }

    // POST /eixos/criar
    public function criar(): \CodeIgniter\HTTP\RedirectResponse
    {
        $dados = [
            'slug'      => EixoModel::gerarSlug($this->request->getPost('nome')),
            'nome'      => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'icone'     => $this->request->getPost('icone') ?: 'bi-lightbulb',
            'cor'       => $this->request->getPost('cor')   ?: '#003F88',
            'cor_bg'    => $this->request->getPost('cor_bg') ?: '#e8f0fe',
            'tags'      => $this->request->getPost('tags'),
            'ordem'     => (int) $this->request->getPost('ordem'),
            'ativo'     => $this->request->getPost('ativo') ? 1 : 0,
        ];

        if (! $this->eixoModel->insert($dados)) {
            return redirect()->back()->withInput()
                ->with('erro', implode(', ', $this->eixoModel->errors()));
        }

        return redirect()->to('/eixos')->with('sucesso', 'Eixo criado com sucesso!');
    }

    // GET /eixos/editar/{id}
    public function editar(int $id): string
    {
        $eixo = $this->eixoModel->find($id);
        if (! $eixo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('eixos/form', [
            'title' => 'Editar Eixo',
            'eixo'  => $eixo,
        ]);
    }

    // POST /eixos/atualizar/{id}
    public function atualizar(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $eixo = $this->eixoModel->find($id);
        if (! $eixo) {
            return redirect()->to('/eixos')->with('erro', 'Eixo não encontrado.');
        }

        $dados = [
            'slug'      => EixoModel::gerarSlug($this->request->getPost('nome')),
            'nome'      => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'icone'     => $this->request->getPost('icone') ?: 'bi-lightbulb',
            'cor'       => $this->request->getPost('cor')   ?: '#003F88',
            'cor_bg'    => $this->request->getPost('cor_bg') ?: '#e8f0fe',
            'tags'      => $this->request->getPost('tags'),
            'ordem'     => (int) $this->request->getPost('ordem'),
            'ativo'     => $this->request->getPost('ativo') ? 1 : 0,
        ];

        $this->eixoModel->update($id, $dados);
        return redirect()->to('/eixos')->with('sucesso', 'Eixo atualizado!');
    }

    // POST /eixos/deletar/{id}
    public function deletar(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $eixo = $this->eixoModel->find($id);
        if (! $eixo) {
            return redirect()->to('/eixos')->with('erro', 'Eixo não encontrado.');
        }

        // Conta ideias vinculadas
        $totalIdeias = $this->ideiaModel->where('eixo_id', $id)->countAllResults();
        if ($totalIdeias > 0) {
            return redirect()->to('/eixos')
                ->with('erro', "Não é possível excluir: este eixo tem {$totalIdeias} ideia(s) vinculada(s).");
        }

        $this->eixoModel->delete($id);
        return redirect()->to('/eixos')->with('sucesso', 'Eixo excluído.');
    }
}
