<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAnexosTable extends Migration
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
            'capitulo_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'numero' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => false,
            ],
            'titulo' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => false,
            ],
            'arquivo_nome' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'arquivo_caminho' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('capitulo_id');
        $this->forge->addForeignKey('capitulo_id', 'capitulos', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('anexos');
    }

    public function down(): void
    {
        $this->forge->dropTable('anexos', true);
    }
}
