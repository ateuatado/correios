<?php

namespace App\Controllers;

use App\Models\EixoModel;
use App\Models\IdeiaModel;

class Home extends BaseController
{
    public function index(): string
    {
        $eixoModel  = new EixoModel();
        $ideiaModel = new IdeiaModel();
        $statusInfo = IdeiaModel::statusInfo();

        // Carrega eixos ativos do banco com total de ideias
        $eixos = $eixoModel->paraHome();

        // Carrega as ideias de cada eixo
        foreach ($eixos as &$eixo) {
            $eixo['ideias']     = $ideiaModel->porEixo($eixo['id']);
            $eixo['tags_array'] = array_filter(
                array_map('trim', explode(',', $eixo['tags'] ?? ''))
            );
        }

        return view('home/index', [
            'title'      => 'Início — CorreiosComercial',
            'eixos'      => $eixos,
            'statusInfo' => $statusInfo,
        ]);
    }
}
