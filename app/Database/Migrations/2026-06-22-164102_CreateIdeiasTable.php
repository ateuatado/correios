<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIdeiasTable extends Migration
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
            'eixo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 300,
                'null'       => false,
            ],
            // Array JSON de {label, valor, tipo} — campos livres definidos pelo analista
            'campos' => [
                'type' => 'LONGTEXT',
                'null' => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['rascunho', 'em_analise', 'validada', 'descartada'],
                'default'    => 'rascunho',
            ],
            'tags' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'atualizado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('eixo_id');
        $this->forge->addKey('status');
        $this->forge->addKey('criado_em');
        $this->forge->addForeignKey('eixo_id', 'eixos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('ideias');
    }

    public function down(): void
    {
        $this->forge->dropTable('ideias', true);
    }
}
