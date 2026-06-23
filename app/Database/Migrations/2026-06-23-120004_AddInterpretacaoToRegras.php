<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInterpretacaoToRegras extends Migration
{
    public function up(): void
    {
        // Coluna principal: JSON interpretado pela IA
        $this->forge->addColumn('regras', [
            'interpretacao' => [
                'type'    => 'LONGTEXT',
                'null'    => true,
                'default' => null,
                'after'   => 'fonte',
                'comment' => 'JSON gerado pela Gemini API com análise semântica da regra',
            ],
            'interpretado_em' => [
                'type'    => 'DATETIME',
                'null'    => true,
                'default' => null,
                'after'   => 'interpretacao',
                'comment' => 'Quando a IA interpretou esta regra',
            ],
            'interpretado_por' => [
                'type'       => 'VARCHAR',
                'constraint' => 60,
                'null'       => true,
                'default'    => null,
                'after'      => 'interpretado_em',
                'comment'    => 'Modelo de IA usado (ex: gemini-2.0-flash)',
            ],
        ]);
    }

    public function down(): void
    {
        $this->forge->dropColumn('regras', ['interpretacao', 'interpretado_em', 'interpretado_por']);
    }
}
