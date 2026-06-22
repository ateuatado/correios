<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegrasTable extends Migration
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
            // Vínculo com o item de origem
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'doc_tipo' => [
                'type'       => 'ENUM',
                'constraint' => ['capitulo', 'anexo'],
                'null'       => false,
            ],
            'doc_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // Serviço detectado no contexto
            'servico' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
                'default'    => null,
                'comment'    => 'SEDEX, PAC, MALOTE, CARTA, etc. Null = regra geral',
            ],
            // Categoria da regra
            'tipo' => [
                'type'       => 'ENUM',
                'constraint' => [
                    'peso',
                    'dimensao',
                    'prazo',
                    'valor',
                    'volume',
                    'restricao',
                    'elegibilidade',
                    'tolerancia',
                    'outro',
                ],
                'default' => 'outro',
            ],
            // Descrição legível extraída
            'descricao' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            // Valor numérico quando aplicável (ex: 30 para "30 kg")
            'valor_numerico' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,4',
                'null'       => true,
                'default'    => null,
            ],
            'unidade' => [
                'type'       => 'VARCHAR',
                'constraint' => 30,
                'null'       => true,
                'default'    => null,
                'comment'    => 'kg, g, cm, dias, R$, %, unidade',
            ],
            // Trecho original do item (contexto)
            'contexto' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            // Breadcrumb de origem
            'fonte' => [
                'type'       => 'VARCHAR',
                'constraint' => 400,
                'null'       => true,
            ],
            'criado_em' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('item_id');
        $this->forge->addKey('servico');
        $this->forge->addKey('tipo');
        $this->forge->createTable('regras');
    }

    public function down(): void
    {
        $this->forge->dropTable('regras', true);
    }
}
