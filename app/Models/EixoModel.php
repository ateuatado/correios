<?php

namespace App\Models;

use CodeIgniter\Model;

class EixoModel extends Model
{
    protected $table         = 'eixos';
    protected $primaryKey    = 'id';
    protected $returnType    = 'array';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $createdField  = 'criado_em';
    protected $updatedField  = 'atualizado_em';

    protected $allowedFields = [
        'slug', 'nome', 'descricao', 'icone', 'cor', 'cor_bg',
        'tags', 'ordem', 'ativo',
    ];

    protected $validationRules = [
        'nome'  => 'required|max_length[300]',
        'slug'  => 'required|max_length[100]|is_unique[eixos.slug,id,{id}]',
        'ordem' => 'required|integer',
    ];

    // ----------------------------------------------------------------
    // Todos os eixos ativos, ordenados, com contagem de ideias
    // ----------------------------------------------------------------
    public function paraHome(): array
    {
        return $this->db->table('eixos e')
            ->select('e.*, COUNT(i.id) AS total_ideias')
            ->join('ideias i', 'i.eixo_id = e.id', 'left')
            ->where('e.ativo', 1)
            ->groupBy('e.id')
            ->orderBy('e.ordem', 'ASC')
            ->get()
            ->getResultArray();
    }

    // ----------------------------------------------------------------
    // Um eixo com todas as suas ideias (para o card expandido)
    // ----------------------------------------------------------------
    public function comIdeias(int $id): ?array
    {
        $eixo = $this->find($id);
        if (! $eixo) return null;

        $eixo['ideias'] = $this->db->table('ideias')
            ->where('eixo_id', $id)
            ->orderBy('criado_em', 'DESC')
            ->get()
            ->getResultArray();

        return $eixo;
    }

    // ----------------------------------------------------------------
    // Gera slug a partir do nome
    // ----------------------------------------------------------------
    public static function gerarSlug(string $nome): string
    {
        $slug = mb_strtolower($nome, 'UTF-8');
        $slug = str_replace(
            ['á','à','â','ã','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','ô','õ','ö','ú','ù','û','ü','ç','ñ'],
            ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c','n'],
            $slug
        );
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', trim($slug));
        return $slug;
    }
}
