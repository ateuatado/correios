<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Tabela de itens e subitens dos documentos do MANCAT.
 *
 * Estrutura hierárquica via adjacency list (pai_id):
 *   - Nível 1 : "1 FINALIDADE DO MANUAL"          (numero = "1",   nivel = 1)
 *   - Nível 2 : "1.1 Fornecer as informações..."  (numero = "1.1", nivel = 2)
 *   - Nível 3 : "1.1.1 ..."                        (numero = "1.1.1", nivel = 3)
 *   - Alíneas : "a) ..."                            (numero = "a",  nivel = 4)
 *
 * Cada item pertence a um DOCUMENTO, que pode ser um capítulo ou um anexo.
 * doc_tipo: 'capitulo' | 'anexo'
 * doc_id  : FK para a tabela correspondente
 */
class CreateItensTable extends Migration
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
            // ── Referência ao documento pai ────────────────────────
            'doc_tipo' => [
                'type'       => "ENUM('capitulo','anexo')",
                'null'       => false,
            ],
            'doc_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            // ── Hierarquia ─────────────────────────────────────────
            'pai_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => true,      // NULL = item raiz
                'default'    => null,
            ],
            'nivel' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
            ],
            // ── Conteúdo ───────────────────────────────────────────
            'numero' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,        // "1", "1.1", "1.1.1", "a", "b"...
                'null'       => false,
            ],
            'titulo' => [
                'type'       => 'TEXT',    // primeiro parágrafo / cabeçalho
                'null'       => false,
            ],
            'conteudo' => [
                'type' => 'LONGTEXT',      // texto completo do item
                'null' => true,
            ],
            // ── Ordem de exibição ──────────────────────────────────
            'ordem' => [
                'type'       => 'SMALLINT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['doc_tipo', 'doc_id']);
        $this->forge->addKey('pai_id');
        $this->forge->addKey(['doc_tipo', 'doc_id', 'nivel']);
        $this->forge->createTable('itens');
    }

    public function down(): void
    {
        $this->forge->dropTable('itens', true);
    }
}
