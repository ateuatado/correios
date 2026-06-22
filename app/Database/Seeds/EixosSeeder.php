<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class EixosSeeder extends Seeder
{
    public function run(): void
    {
        $eixos = [
            [
                'slug'      => 'posicionamento',
                'nome'      => 'Posicionamento e Estratégia',
                'descricao' => 'Diretrizes estratégicas, segmentação de mercado, proposta de valor e posicionamento competitivo dos serviços comerciais dos Correios.',
                'icone'     => 'bi-bullseye',
                'cor'       => '#003F88',
                'cor_bg'    => '#e8f0fe',
                'tags'      => 'Estratégia,Mercado,Segmentação',
                'ordem'     => 1,
            ],
            [
                'slug'      => 'estrutura-contratual',
                'nome'      => 'Estrutura Contratual',
                'descricao' => 'Tipos de contratos comerciais, cláusulas essenciais, vigência, renovação, aditivos e gestão do ciclo de vida contratual com clientes B2B.',
                'icone'     => 'bi-file-earmark-ruled',
                'cor'       => '#1565C0',
                'cor_bg'    => '#e3f2fd',
                'tags'      => 'Contratos,B2B,Juridico',
                'ordem'     => 2,
            ],
            [
                'slug'      => 'precificacao',
                'nome'      => 'Precificação e Faturamento',
                'descricao' => 'Metodologias de precificação, tabelas tarifárias, descontos por volume, faturamento eletrônico e gestão de cobranças contratuais.',
                'icone'     => 'bi-calculator',
                'cor'       => '#2E7D32',
                'cor_bg'    => '#e8f5e9',
                'tags'      => 'Tarifas,Faturamento,Descontos',
                'ordem'     => 3,
            ],
            [
                'slug'      => 'portfolio-b2b',
                'nome'      => 'Portfólio Central de Soluções B2B',
                'descricao' => 'Catálogo completo de produtos e serviços para clientes corporativos: SEDEX, PAC, Malote, Banco Postal, logística reversa e soluções integradas.',
                'icone'     => 'bi-grid-1x2',
                'cor'       => '#6A1B9A',
                'cor_bg'    => '#f3e5f5',
                'tags'      => 'SEDEX,Logística,Produtos',
                'ordem'     => 4,
            ],
            [
                'slug'      => 'tecnologia',
                'nome'      => 'Tecnologia, Integração e Automação',
                'descricao' => 'APIs de rastreamento e postagem, integração com ERPs, automação de processos, EDI, WebService e plataformas digitais para clientes corporativos.',
                'icone'     => 'bi-cpu',
                'cor'       => '#00838F',
                'cor_bg'    => '#e0f7fa',
                'tags'      => 'API,Integração,Digital',
                'ordem'     => 5,
            ],
            [
                'slug'      => 'pos-venda',
                'nome'      => 'Pós-Venda, Indenizações e Seguros',
                'descricao' => 'Gestão de reclamações, SLA de atendimento, processo de indenização por extravio/avaria, seguros opcionais e gestão de risco comercial.',
                'icone'     => 'bi-shield-check',
                'cor'       => '#B71C1C',
                'cor_bg'    => '#fce4ec',
                'tags'      => 'SLA,Indenização,Risco',
                'ordem'     => 6,
            ],
            [
                'slug'      => 'credito-cobranca',
                'nome'      => 'Gestão de Crédito, Cobrança e Compliance',
                'descricao' => 'Análise de crédito para clientes PJ, limites de faturamento, régua de cobrança, inadimplência, compliance contratual e auditoria de contratos.',
                'icone'     => 'bi-bank',
                'cor'       => '#E65100',
                'cor_bg'    => '#fff3e0',
                'tags'      => 'Crédito,Compliance,Cobrança',
                'ordem'     => 7,
            ],
            [
                'slug'      => 'solucoes-internacionais',
                'nome'      => 'Soluções Internacionais (Cross-Border)',
                'descricao' => 'Exportação e importação postal, Remessa Internacional, EMS, e-commerce transfronteiriço, alfandegamento, acordos bilaterais e UPU.',
                'icone'     => 'bi-globe-americas',
                'cor'       => '#0277BD',
                'cor_bg'    => '#e1f5fe',
                'tags'      => 'Exportação,Cross-Border,UPU',
                'ordem'     => 8,
            ],
        ];

        $now = date('Y-m-d H:i:s');
        foreach ($eixos as &$e) {
            $e['criado_em']     = $now;
            $e['atualizado_em'] = $now;
        }

        $this->db->table('eixos')->insertBatch($eixos);
        echo '   ✔  8 eixos inseridos.' . PHP_EOL;
    }
}
