<?php

namespace App\Models;

use CodeIgniter\Model;

class ItemModel extends Model
{
    protected $table            = 'itens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $useTimestamps    = false;

    protected $allowedFields = [
        'doc_tipo', 'doc_id', 'pai_id', 'nivel', 'numero', 'titulo', 'conteudo', 'ordem',
    ];

    protected $validationRules = [
        'doc_tipo' => 'required|in_list[capitulo,anexo]',
        'doc_id'   => 'required|integer',
        'nivel'    => 'required|integer',
        'numero'   => 'required|max_length[20]',
        'titulo'   => 'required',
    ];

    // ---------------------------------------------------------------
    // Itens de um documento em árvore (pai → filhos)
    // ---------------------------------------------------------------
    public function arvore(string $docTipo, int $docId): array
    {
        $todos = $this->where('doc_tipo', $docTipo)
                      ->where('doc_id', $docId)
                      ->orderBy('ordem', 'ASC')
                      ->findAll();

        return $this->construirArvore($todos, null);
    }

    private function construirArvore(array $todos, ?int $paiId): array
    {
        $resultado = [];
        foreach ($todos as $item) {
            $itemPaiId = $item['pai_id'] === null ? null : (int) $item['pai_id'];
            if ($itemPaiId === $paiId) {
                $item['filhos'] = $this->construirArvore($todos, (int) $item['id']);
                $resultado[]    = $item;
            }
        }
        return $resultado;
    }

    // ---------------------------------------------------------------
    // Itens raiz de um documento (nível 1)
    // ---------------------------------------------------------------
    public function itensRaiz(string $docTipo, int $docId): array
    {
        return $this->where('doc_tipo', $docTipo)
                    ->where('doc_id', $docId)
                    ->where('nivel', 1)
                    ->orderBy('ordem', 'ASC')
                    ->findAll();
    }

    // ---------------------------------------------------------------
    // Filhos diretos de um item
    // ---------------------------------------------------------------
    public function filhos(int $paiId): array
    {
        return $this->where('pai_id', $paiId)
                    ->orderBy('ordem', 'ASC')
                    ->findAll();
    }
}
