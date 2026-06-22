<?php

namespace App\Models;

use CodeIgniter\Model;

class ModuloModel extends Model
{
    protected $table            = 'modulos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['manual_id', 'numero', 'titulo'];

    protected $useTimestamps    = false;

    protected $validationRules = [
        'manual_id' => 'required|integer',
        'numero'    => 'required|integer',
        'titulo'    => 'required|max_length[400]',
    ];

    // ---------------------------------------------------------------
    // Módulo com capítulos e anexos aninhados
    // ---------------------------------------------------------------
    public function comCapitulosEAnexos(int $moduloId): ?array
    {
        $modulo = $this->find($moduloId);
        if (! $modulo) {
            return null;
        }

        $capitulos = $this->db->table('capitulos')
            ->where('modulo_id', $moduloId)
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

        return $modulo;
    }

    // ---------------------------------------------------------------
    // Módulos de um manual, com contagem de capítulos
    // ---------------------------------------------------------------
    public function porManual(int $manualId): array
    {
        return $this->db->table('modulos m')
            ->select('m.*, COUNT(c.id) AS total_capitulos')
            ->join('capitulos c', 'c.modulo_id = m.id', 'left')
            ->where('m.manual_id', $manualId)
            ->groupBy('m.id')
            ->orderBy('m.numero', 'ASC')
            ->get()
            ->getResultArray();
    }
}
