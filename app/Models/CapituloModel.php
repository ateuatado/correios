<?php

namespace App\Models;

use CodeIgniter\Model;

class CapituloModel extends Model
{
    protected $table            = 'capitulos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['modulo_id', 'numero', 'titulo', 'arquivo_nome', 'arquivo_caminho'];

    protected $useTimestamps    = false;

    protected $validationRules = [
        'modulo_id' => 'required|integer',
        'numero'    => 'required|integer',
        'titulo'    => 'required|max_length[500]',
    ];

    // ---------------------------------------------------------------
    // Capítulo com módulo pai, manual e lista de anexos
    // ---------------------------------------------------------------
    public function comContexto(int $capituloId): ?array
    {
        $row = $this->db->table('capitulos c')
            ->select('c.*, mo.id AS modulo_id, mo.numero AS modulo_numero, mo.titulo AS modulo_titulo, mo.manual_id, ma.codigo AS manual_codigo, ma.nome AS manual_nome')
            ->join('modulos mo', 'mo.id = c.modulo_id')
            ->join('manuais ma', 'ma.id = mo.manual_id')
            ->where('c.id', $capituloId)
            ->get()
            ->getRowArray();

        if (! $row) {
            return null;
        }

        $row['anexos'] = $this->db->table('anexos')
            ->where('capitulo_id', $capituloId)
            ->orderBy('numero', 'ASC')
            ->get()
            ->getResultArray();

        return $row;
    }

    // ---------------------------------------------------------------
    // Capítulos de um módulo com contagem de anexos
    // ---------------------------------------------------------------
    public function porModulo(int $moduloId): array
    {
        return $this->db->table('capitulos c')
            ->select('c.*, COUNT(a.id) AS total_anexos')
            ->join('anexos a', 'a.capitulo_id = c.id', 'left')
            ->where('c.modulo_id', $moduloId)
            ->groupBy('c.id')
            ->orderBy('c.numero', 'ASC')
            ->get()
            ->getResultArray();
    }
}
