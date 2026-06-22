<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModulosTable extends Migration
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
            'manual_id' => [
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
                'constraint' => 400,
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('manual_id');
        $this->forge->addForeignKey('manual_id', 'manuais', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('modulos');
    }

    public function down(): void
    {
        $this->forge->dropTable('modulos', true);
    }
}
