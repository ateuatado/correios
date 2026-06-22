<?php

namespace App\Models;

use CodeIgniter\Model;

class ManualModel extends Model
{
    protected $table            = 'manuais';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['codigo', 'nome', 'sumario'];

    protected $useTimestamps    = true;
    protected $createdField     = 'criado_em';
    protected $updatedField     = 'atualizado_em';

    protected $validationRules = [
        'codigo' => 'required|max_length[20]|is_unique[manuais.codigo,id,{id}]',
        'nome'   => 'required|max_length[200]',
    ];

    // ---------------------------------------------------------------
    // Retorna todos os manuais com contagem de módulos
    // ---------------------------------------------------------------
    public function listarComContagem(): array
    {
        return $this->db->table('manuais m')
            ->select('m.*, COUNT(mo.id) AS total_modulos')
            ->join('modulos mo', 'mo.manual_id = m.id', 'left')
            ->groupBy('m.id')
            ->orderBy('m.codigo', 'ASC')
            ->get()
            ->getResultArray();
    }

    // ---------------------------------------------------------------
    // Retorna o manual com todos os módulos e capítulos (árvore completa)
    // ---------------------------------------------------------------
    public function arvoreCompleta(int $manualId): ?array
    {
        $manual = $this->find($manualId);
        if (! $manual) {
            return null;
        }

        $modulos = $this->db->table('modulos')
            ->where('manual_id', $manualId)
            ->orderBy('numero', 'ASC')
            ->get()
            ->getResultArray();

        foreach ($modulos as &$modulo) {
            $capitulos = $this->db->table('capitulos')
                ->where('modulo_id', $modulo['id'])
                ->orderBy('numero', 'ASC')
                ->get()
                ->getResultArray();

            foreach ($capitulos as &$capitulo) {
                $capitulo['anexos'] = $this->db->table('anexos')
                    ->where('capitulo_id', $capitulo['id'])
                    ->orderBy('numero', 'ASC')
                    ->get()
                    ->getResultArray();
            }

            $modulo['capitulos'] = $capitulos;
        }

        $manual['modulos'] = $modulos;

        return $manual;
    }
}
