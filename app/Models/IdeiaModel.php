<?php

namespace App\Models;

use CodeIgniter\Model;

class IdeiaModel extends Model
{
    protected $table         = 'ideias';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    protected $allowedFields = [
        'eixo_id', 'titulo', 'campos', 'status', 'tags',
    ];

    protected $validationRules = [
        'eixo_id' => 'required|integer',
        'titulo'  => 'required|max_length[300]',
        'status'  => 'required|in_list[rascunho,em_analise,validada,descartada]',
    ];

    // ----------------------------------------------------------------
    // Campos padrão para uma nova ideia
    // ----------------------------------------------------------------
    public static function camposPadrao(): array
    {
        return [
            ['label' => 'Objetivo',       'valor' => '', 'tipo' => 'texto_longo'],
            ['label' => 'Hipótese',        'valor' => '', 'tipo' => 'texto_longo'],
            ['label' => 'Próximo passo',   'valor' => '', 'tipo' => 'texto_curto'],
        ];
    }

    // ----------------------------------------------------------------
    // Decodifica o JSON de campos para array PHP
    // ----------------------------------------------------------------
    public function camposArray(array $ideia): array
    {
        if (empty($ideia['campos'])) {
            return self::camposPadrao();
        }

        $decoded = json_decode($ideia['campos'], true);
        return is_array($decoded) ? $decoded : self::camposPadrao();
    }

    // ----------------------------------------------------------------
    // Encode de campos para JSON (sanitiza valores)
    // ----------------------------------------------------------------
    public static function camposJson(array $campos): string
    {
        $limpos = [];
        foreach ($campos as $campo) {
            if (! empty(trim($campo['label'] ?? ''))) {
                $limpos[] = [
                    'label' => trim($campo['label']),
                    'valor' => $campo['valor'] ?? '',
                    'tipo'  => $campo['tipo'] ?? 'texto_longo',
                ];
            }
        }
        return json_encode($limpos, JSON_UNESCAPED_UNICODE);
    }

    // ----------------------------------------------------------------
    // Ideias de um eixo com contagem por status
    // ----------------------------------------------------------------
    public function porEixo(int $eixoId): array
    {
        return $this->where('eixo_id', $eixoId)
                    ->orderBy('criado_em', 'DESC')
                    ->findAll();
    }

    // ----------------------------------------------------------------
    // Rótulos e cores dos status
    // ----------------------------------------------------------------
    public static function statusInfo(): array
    {
        return [
            'rascunho'    => ['label' => 'Rascunho',    'cor' => '#94a3b8', 'bg' => '#f1f5f9'],
            'em_analise'  => ['label' => 'Em análise',  'cor' => '#b45309', 'bg' => '#fef3c7'],
            'validada'    => ['label' => 'Validada',    'cor' => '#166534', 'bg' => '#dcfce7'],
            'descartada'  => ['label' => 'Descartada',  'cor' => '#991b1b', 'bg' => '#fee2e2'],
        ];
    }

    // ----------------------------------------------------------------
    // Tipos de campo disponíveis
    // ----------------------------------------------------------------
    public static function tiposCampo(): array
    {
        return [
            'texto_curto' => 'Texto curto',
            'texto_longo' => 'Texto longo',
            'referencia'  => 'Referência MANCAT',
            'lista'       => 'Lista de itens',
            'numero'      => 'Número',
            'data'        => 'Data',
        ];
    }
}
