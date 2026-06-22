<?php

namespace App\Models;

use CodeIgniter\Model;

class BuscaModel extends Model
{
    protected $table         = 'buscas';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = '';

    protected $allowedFields = [
        'manual_id', 'modulo_id', 'query', 'resposta', 'itens_ids', 'score',
    ];

    // ---------------------------------------------------------------
    // Busca textual simples nos itens (FULLTEXT ou LIKE)
    // Fundação para futura integração semântica com LLM/embeddings
    // ---------------------------------------------------------------
    public function buscarItens(string $termo, ?int $manualId = null, int $limite = 30): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('itens i')
            ->select('i.id, i.doc_tipo, i.doc_id, i.nivel, i.numero, i.titulo, i.conteudo, i.ordem')
            ->select('i.pai_id');

        // Contexto: filtrar por manual se informado
        if ($manualId !== null) {
            // Filtra capítulos do manual
            $capsIds = $db->table('capitulos c')
                ->select('c.id')
                ->join('modulos mo', 'mo.id = c.modulo_id')
                ->where('mo.manual_id', $manualId)
                ->get()
                ->getResultArray();

            $anxIds = $db->table('anexos a')
                ->select('a.id')
                ->join('capitulos c', 'c.id = a.capitulo_id')
                ->join('modulos mo', 'mo.id = c.modulo_id')
                ->where('mo.manual_id', $manualId)
                ->get()
                ->getResultArray();

            $capIdsArr = array_column($capsIds, 'id');
            $anxIdsArr = array_column($anxIds,  'id');

            if (! empty($capIdsArr) || ! empty($anxIdsArr)) {
                $builder->groupStart();
                if (! empty($capIdsArr)) {
                    $builder->orGroupStart()
                        ->where("i.doc_tipo = 'capitulo'")
                        ->whereIn('i.doc_id', $capIdsArr)
                        ->groupEnd();
                }
                if (! empty($anxIdsArr)) {
                    $builder->orGroupStart()
                        ->where("i.doc_tipo = 'anexo'")
                        ->whereIn('i.doc_id', $anxIdsArr)
                        ->groupEnd();
                }
                $builder->groupEnd();
            }
        }

        // Busca por LIKE em título e conteúdo
        $termoLike = '%' . $db->escapeLikeString($termo) . '%';
        $builder->groupStart()
            ->like('i.titulo',   $termo, 'both', null, true)
            ->orLike('i.conteudo', $termo, 'both', null, true)
            ->groupEnd();

        $builder->orderBy('i.nivel', 'ASC')
                ->limit($limite);

        return $builder->get()->getResultArray();
    }

    // ---------------------------------------------------------------
    // Histórico de buscas recentes
    // ---------------------------------------------------------------
    public function recentes(int $limite = 10): array
    {
        return $this->orderBy('criado_em', 'DESC')
                    ->limit($limite)
                    ->findAll();
    }
}
