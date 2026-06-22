<?php

namespace App\Controllers;

use App\Models\EixoModel;
use App\Models\IdeiaModel;

class Ideias extends BaseController
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

    // GET /ideias/nova/{eixo_id}
    public function nova(int $eixoId): string
    {
        $eixo = $this->eixoModel->find($eixoId);
        if (! $eixo) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        return view('ideias/form', [
            'title'      => 'Nova Ideia — ' . $eixo['nome'],
            'eixo'       => $eixo,
            'ideia'      => null,
            'campos'     => IdeiaModel::camposPadrao(),
            'statusInfo' => IdeiaModel::statusInfo(),
            'tipos'      => IdeiaModel::tiposCampo(),
        ]);
    }

    // POST /ideias/salvar
    public function salvar(): \CodeIgniter\HTTP\RedirectResponse
    {
        $eixoId = (int) $this->request->getPost('eixo_id');
        $eixo   = $this->eixoModel->find($eixoId);

        if (! $eixo) {
            return redirect()->to('/')->with('erro', 'Eixo não encontrado.');
        }

        // Monta o array de campos a partir dos POST arrays
        $labels = $this->request->getPost('campo_label') ?? [];
        $values = $this->request->getPost('campo_valor') ?? [];
        $tipos  = $this->request->getPost('campo_tipo')  ?? [];

        $campos = [];
        foreach ($labels as $i => $label) {
            if (trim($label) !== '') {
                $campos[] = [
                    'label' => trim($label),
                    'valor' => $values[$i] ?? '',
                    'tipo'  => $tipos[$i]  ?? 'texto_longo',
                ];
            }
        }

        $dados = [
            'eixo_id' => $eixoId,
            'titulo'  => trim($this->request->getPost('titulo')),
            'campos'  => IdeiaModel::camposJson($campos),
            'status'  => $this->request->getPost('status') ?: 'rascunho',
            'tags'    => trim($this->request->getPost('tags') ?? ''),
        ];

        if (empty($dados['titulo'])) {
            return redirect()->back()->withInput()->with('erro', 'O título da ideia é obrigatório.');
        }

        $id = $this->ideiaModel->insert($dados, true);
        return redirect()->to("/ideias/{$id}")->with('sucesso', 'Ideia salva com sucesso!');
    }

    // GET /ideias/{id}
    public function show(int $id): string
    {
        $ideia = $this->ideiaModel->find($id);
        if (! $ideia) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $eixo   = $this->eixoModel->find($ideia['eixo_id']);
        $campos = $this->ideiaModel->camposArray($ideia);

        return view('ideias/show', [
            'title'      => $ideia['titulo'] . ' — ' . ($eixo['nome'] ?? ''),
            'ideia'      => $ideia,
            'eixo'       => $eixo,
            'campos'     => $campos,
            'statusInfo' => IdeiaModel::statusInfo(),
        ]);
    }

    // GET /ideias/{id}/editar
    public function editar(int $id): string
    {
        $ideia = $this->ideiaModel->find($id);
        if (! $ideia) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $eixo   = $this->eixoModel->find($ideia['eixo_id']);
        $campos = $this->ideiaModel->camposArray($ideia);

        return view('ideias/form', [
            'title'      => 'Editar: ' . $ideia['titulo'],
            'eixo'       => $eixo,
            'ideia'      => $ideia,
            'campos'     => $campos,
            'statusInfo' => IdeiaModel::statusInfo(),
            'tipos'      => IdeiaModel::tiposCampo(),
        ]);
    }

    // POST /ideias/{id}/atualizar
    public function atualizar(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $ideia = $this->ideiaModel->find($id);
        if (! $ideia) {
            return redirect()->to('/')->with('erro', 'Ideia não encontrada.');
        }

        $labels = $this->request->getPost('campo_label') ?? [];
        $values = $this->request->getPost('campo_valor') ?? [];
        $tipos  = $this->request->getPost('campo_tipo')  ?? [];

        $campos = [];
        foreach ($labels as $i => $label) {
            if (trim($label) !== '') {
                $campos[] = [
                    'label' => trim($label),
                    'valor' => $values[$i] ?? '',
                    'tipo'  => $tipos[$i]  ?? 'texto_longo',
                ];
            }
        }

        $this->ideiaModel->update($id, [
            'titulo'  => trim($this->request->getPost('titulo')),
            'campos'  => IdeiaModel::camposJson($campos),
            'status'  => $this->request->getPost('status') ?: 'rascunho',
            'tags'    => trim($this->request->getPost('tags') ?? ''),
        ]);

        return redirect()->to("/ideias/{$id}")->with('sucesso', 'Ideia atualizada!');
    }

    // POST /ideias/{id}/deletar
    public function deletar(int $id): \CodeIgniter\HTTP\RedirectResponse
    {
        $ideia = $this->ideiaModel->find($id);
        if (! $ideia) {
            return redirect()->to('/')->with('erro', 'Ideia não encontrada.');
        }

        $eixoId = $ideia['eixo_id'];
        $this->ideiaModel->delete($id);
        return redirect()->to("/#pilar-" . ($this->eixoModel->find($eixoId)['slug'] ?? ''))
            ->with('sucesso', 'Ideia excluída.');
    }
}
