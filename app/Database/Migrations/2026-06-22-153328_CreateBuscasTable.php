<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela de buscas — log de pesquisas realizadas.
 * Fundação para futura integração com AI/LLM.
 *
 * - Armazena a query do usuário
 * - Contexto: manual/módulo/capítulo pesquisado
 * - Resposta da AI (futura)
 * - Itens relevantes encontrados (JSON)
 */
class CreateBuscasTable extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            // Contexto da busca (opcional — null = busca global)
            'manual_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            'modulo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,
                'default'    => null,
            ],
            // Pergunta do usuário
            'query' => [
                'type'       => 'VARCHAR',
                'constraint' => 1000,
                'null'       => false,
            ],
            // Resposta gerada (futura — AI/LLM)
            'resposta' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            // IDs dos itens relevantes encontrados (JSON array)
            'itens_ids' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Métricas de qualidade (futura)
            'score'     => [
                'type'    => 'FLOAT',
                'null'    => true,
                'default' => null,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('manual_id');
        $this->forge->addKey('criado_em');
        $this->forge->createTable('buscas');
    }

    public function down(): void
    {
        $this->forge->dropTable('buscas', true);
    }
}
