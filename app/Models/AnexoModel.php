<?php

namespace App\Models;

use CodeIgniter\Model;

class AnexoModel extends Model
{
    protected $table            = 'anexos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields    = ['capitulo_id', 'numero', 'titulo', 'arquivo_nome', 'arquivo_caminho'];

    protected $useTimestamps    = false;

    protected $validationRules = [
        'capitulo_id' => 'required|integer',
        'numero'      => 'required|integer',
        'titulo'      => 'required|max_length[500]',
    ];

    // ---------------------------------------------------------------
    // Anexo com contexto completo (capítulo → módulo → manual)
    // ---------------------------------------------------------------
    public function comContexto(int $anexoId): ?array
    {
        return $this->db->table('anexos a')
            ->select('a.*, c.numero AS capitulo_numero, c.titulo AS capitulo_titulo, c.modulo_id, mo.numero AS modulo_numero, mo.titulo AS modulo_titulo, mo.manual_id, ma.codigo AS manual_codigo, ma.nome AS manual_nome')
            ->join('capitulos c', 'c.id = a.capitulo_id')
            ->join('modulos mo', 'mo.id = c.modulo_id')
            ->join('manuais ma', 'ma.id = mo.manual_id')
            ->where('a.id', $anexoId)
            ->get()
            ->getRowArray();
    }
}
