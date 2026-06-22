<?php

namespace App\Models;

use CodeIgniter\Model;

class RegraModel extends Model
{
    protected $table      = 'regras';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'item_id', 'doc_tipo', 'doc_id', 'servico', 'tipo',
        'descricao', 'valor_numerico', 'unidade', 'contexto', 'fonte',
    ];

    // ── Labels para exibição ──────────────────────────────────────────────

    public static function tipoInfo(): array
    {
        return [
            'peso'          => ['label' => 'Peso',             'icone' => 'bi-box-seam',        'cor' => '#1565C0', 'bg' => '#e3f2fd'],
            'dimensao'      => ['label' => 'Dimensão',         'icone' => 'bi-rulers',           'cor' => '#6A1B9A', 'bg' => '#f3e5f5'],
            'prazo'         => ['label' => 'Prazo',            'icone' => 'bi-clock',            'cor' => '#2E7D32', 'bg' => '#e8f5e9'],
            'valor'         => ['label' => 'Valor (R$)',       'icone' => 'bi-currency-dollar',  'cor' => '#B71C1C', 'bg' => '#fce4ec'],
            'volume'        => ['label' => 'Volume mínimo',    'icone' => 'bi-stack',            'cor' => '#E65100', 'bg' => '#fff3e0'],
            'restricao'     => ['label' => 'Restrição',        'icone' => 'bi-slash-circle',     'cor' => '#B71C1C', 'bg' => '#fce4ec'],
            'elegibilidade' => ['label' => 'Elegibilidade',    'icone' => 'bi-person-check',     'cor' => '#00838F', 'bg' => '#e0f7fa'],
            'tolerancia'    => ['label' => 'Tolerância/Margem','icone' => 'bi-percent',          'cor' => '#5D4037', 'bg' => '#efebe9'],
            'outro'         => ['label' => 'Outro',            'icone' => 'bi-tag',              'cor' => '#546E7A', 'bg' => '#eceff1'],
        ];
    }

    // ── Dashboard: contagem por tipo ──────────────────────────────────────

    public function porTipo(): array
    {
        return $this->db->query(
            'SELECT tipo, COUNT(*) AS total FROM regras GROUP BY tipo ORDER BY total DESC'
        )->getResultArray();
    }

    // ── Dashboard: contagem por serviço ──────────────────────────────────

    public function porServico(): array
    {
        return $this->db->query(
            "SELECT COALESCE(servico, '(Geral)') AS servico, COUNT(*) AS total
             FROM regras
             GROUP BY servico
             ORDER BY total DESC"
        )->getResultArray();
    }

    // ── Listagem filtrada ─────────────────────────────────────────────────

    public function filtrar(array $filtros = [], int $perPage = 40): array
    {
        $builder = $this->db->table('regras');
        $builder->orderBy('tipo')->orderBy('servico');

        if (! empty($filtros['tipo'])) {
            $builder->where('tipo', $filtros['tipo']);
        }
        if (! empty($filtros['servico'])) {
            if ($filtros['servico'] === '_geral_') {
                $builder->where('servico IS NULL');
            } else {
                $builder->where('servico', $filtros['servico']);
            }
        }
        if (! empty($filtros['q'])) {
            $builder->groupStart()
                ->like('descricao', $filtros['q'])
                ->orLike('contexto', $filtros['q'])
                ->orLike('fonte', $filtros['q'])
                ->groupEnd();
        }

        $total   = $builder->countAllResults(false);
        $pagina  = (int) ($filtros['pagina'] ?? 1);
        $offset  = ($pagina - 1) * $perPage;
        $regras  = $builder->limit($perPage, $offset)->get()->getResultArray();

        return compact('regras', 'total', 'pagina', 'perPage');
    }

    // ── Comparativo de serviços ───────────────────────────────────────────

    public function comparar(array $servicos): array
    {
        if (empty($servicos)) return [];

        $tipos = ['peso', 'dimensao', 'prazo', 'valor', 'volume', 'restricao', 'elegibilidade'];
        $resultado = [];

        foreach ($tipos as $tipo) {
            $linha = ['tipo' => $tipo, 'servicos' => []];
            foreach ($servicos as $srv) {
                $where = $srv === '_geral_' ? 'servico IS NULL' : "servico = " . $this->db->escape($srv);
                $rows = $this->db->query(
                    "SELECT descricao, valor_numerico, unidade, fonte, item_id
                     FROM regras
                     WHERE tipo = ? AND {$where}
                     ORDER BY valor_numerico
                     LIMIT 5",
                    [$tipo]
                )->getResultArray();
                $linha['servicos'][$srv] = $rows;
            }
            $resultado[] = $linha;
        }

        return $resultado;
    }

    // ── Todos os serviços únicos ──────────────────────────────────────────

    public function servicosUnicos(): array
    {
        return $this->db->query(
            "SELECT DISTINCT servico FROM regras WHERE servico IS NOT NULL ORDER BY servico"
        )->getResultArray();
    }
}
