<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        // Os 8 pilares da área comercial dos Correios
        $pilares = [
            [
                'id'      => 'posicionamento',
                'icone'   => 'bi-bullseye',
                'cor'     => '#003F88',
                'cor_bg'  => '#e8f0fe',
                'titulo'  => 'Posicionamento e Estratégia',
                'resumo'  => 'Diretrizes estratégicas, segmentação de mercado, proposta de valor e posicionamento competitivo dos serviços comerciais dos Correios.',
                'tags'    => ['Estratégia', 'Mercado', 'Segmentação'],
                'status'  => 'em_breve',
                'modulos' => ['Módulo 1', 'Módulo 3'],
            ],
            [
                'id'      => 'estrutura-contratual',
                'icone'   => 'bi-file-earmark-ruled',
                'cor'     => '#1565C0',
                'cor_bg'  => '#e3f2fd',
                'titulo'  => 'Estrutura Contratual',
                'resumo'  => 'Tipos de contratos comerciais, cláusulas essenciais, vigência, renovação, aditivos e gestão do ciclo de vida contratual com clientes B2B.',
                'tags'    => ['Contratos', 'B2B', 'Juridico'],
                'status'  => 'em_breve',
                'modulos' => ['Módulo 5', 'Módulo 6'],
            ],
            [
                'id'      => 'precificacao',
                'icone'   => 'bi-calculator',
                'cor'     => '#2E7D32',
                'cor_bg'  => '#e8f5e9',
                'titulo'  => 'Precificação e Faturamento',
                'resumo'  => 'Metodologias de precificação, tabelas tarifárias, descontos por volume, faturamento eletrônico e gestão de cobranças contratuais.',
                'tags'    => ['Tarifas', 'Faturamento', 'Descontos'],
                'status'  => 'disponivel',
                'modulos' => ['Módulo 2', 'Módulo 7'],
            ],
            [
                'id'      => 'portfolio-b2b',
                'icone'   => 'bi-grid-1x2',
                'cor'     => '#6A1B9A',
                'cor_bg'  => '#f3e5f5',
                'titulo'  => 'Portfólio Central de Soluções B2B',
                'resumo'  => 'Catálogo completo de produtos e serviços para clientes corporativos: SEDEX, PAC, Malote, Banco Postal, logística reversa e soluções integradas.',
                'tags'    => ['SEDEX', 'Logística', 'Produtos'],
                'status'  => 'disponivel',
                'modulos' => ['Módulo 4', 'Módulo 8'],
            ],
            [
                'id'      => 'tecnologia',
                'icone'   => 'bi-cpu',
                'cor'     => '#00838F',
                'cor_bg'  => '#e0f7fa',
                'titulo'  => 'Tecnologia, Integração e Automação',
                'resumo'  => 'APIs de rastreamento e postagem, integração com ERPs, automação de processos, EDI, WebService e plataformas digitais para clientes corporativos.',
                'tags'    => ['API', 'Integração', 'Digital'],
                'status'  => 'em_breve',
                'modulos' => ['Módulo 10', 'Módulo 11'],
            ],
            [
                'id'      => 'pos-venda',
                'icone'   => 'bi-shield-check',
                'cor'     => '#B71C1C',
                'cor_bg'  => '#fce4ec',
                'titulo'  => 'Pós-Venda, Indenizações e Seguros',
                'resumo'  => 'Gestão de reclamações, SLA de atendimento, processo de indenização por extravio/avaria, seguros opcionais e gestão de risco comercial.',
                'tags'    => ['SLA', 'Indenização', 'Risco'],
                'status'  => 'em_breve',
                'modulos' => ['Módulo 12', 'Módulo 13'],
            ],
            [
                'id'      => 'credito-cobranca',
                'icone'   => 'bi-bank',
                'cor'     => '#E65100',
                'cor_bg'  => '#fff3e0',
                'titulo'  => 'Gestão de Crédito, Cobrança e Compliance',
                'resumo'  => 'Análise de crédito para clientes PJ, limites de faturamento, régua de cobrança, inadimplência, compliance contratual e auditoria de contratos.',
                'tags'    => ['Crédito', 'Compliance', 'Cobrança'],
                'status'  => 'em_breve',
                'modulos' => ['Módulo 14', 'Módulo 15'],
            ],
            [
                'id'      => 'solucoes-internacionais',
                'icone'   => 'bi-globe-americas',
                'cor'     => '#0277BD',
                'cor_bg'  => '#e1f5fe',
                'titulo'  => 'Soluções Internacionais (Cross-Border)',
                'resumo'  => 'Exportação e importação postal, Remessa Internacional, EMS, e-commerce transfronteiriço, alfandegamento, acordos bilaterais e UPU.',
                'tags'    => ['Exportação', 'Cross-Border', 'UPU'],
                'status'  => 'em_breve',
                'modulos' => ['Módulo 16', 'Módulo 17'],
            ],
        ];

        return view('home/index', [
            'title'   => 'Início — CorreiosComercial',
            'pilares' => $pilares,
        ]);
    }
}
