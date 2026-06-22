<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Home ──────────────────────────────────────────────────────────
$routes->get('/', 'Home::index');

// ── Módulo: Manuais ──────────────────────────────────────────────
$routes->group('manuais', ['namespace' => 'App\Controllers'], function ($routes) {

    // Lista todos os manuais
    $routes->get('/',                 'Manuais::index');

    // Árvore completa de um manual
    $routes->get('arvore/(:num)',     'Manuais::arvore/$1');

    // Detalhe de um módulo
    $routes->get('modulo/(:num)',     'Manuais::modulo/$1');

    // Leitura de um capítulo (itens + subitens)
    $routes->get('capitulo/(:num)',   'Manuais::capitulo/$1');

    // Leitura de um anexo (itens + subitens)
    $routes->get('anexo/(:num)',      'Manuais::anexo/$1');

    // Busca textual (fundação para AI)
    $routes->get('buscar',            'Manuais::buscar');
    $routes->post('buscar',           'Manuais::buscar');

    // API JSON — contexto para LLM (futuro)
    $routes->get('api/item/(:num)',   'Manuais::apiItem/$1');
});

// ── Módulo: Eixos (CRUD dos pilares) ─────────────────────────────
$routes->group('eixos', ['namespace' => 'App\\Controllers'], function ($routes) {
    $routes->get('/',              'Eixos::index');
    $routes->get('novo',           'Eixos::novo');
    $routes->post('criar',         'Eixos::criar');
    $routes->get('editar/(:num)',  'Eixos::editar/$1');
    $routes->post('atualizar/(:num)', 'Eixos::atualizar/$1');
    $routes->post('deletar/(:num)',   'Eixos::deletar/$1');
});

// ── Módulo: Ideias (CRUD com campos JSON) ────────────────────────
$routes->group('ideias', ['namespace' => 'App\\Controllers'], function ($routes) {
    $routes->get('nova/(:num)',         'Ideias::nova/$1');
    $routes->post('salvar',            'Ideias::salvar');
    $routes->get('(:num)',             'Ideias::show/$1');
    $routes->get('(:num)/editar',      'Ideias::editar/$1');
    $routes->post('(:num)/atualizar',  'Ideias::atualizar/$1');
    $routes->post('(:num)/deletar',    'Ideias::deletar/$1');
});

// ── Módulo: Inteligência (regras extraídas + comparativo) ─────────
$routes->group('inteligencia', ['namespace' => 'App\\Controllers'], function ($routes) {
    $routes->get('/',                'Inteligencia::index');
    $routes->get('regras',          'Inteligencia::regras');
    $routes->get('comparar',        'Inteligencia::comparar');
    $routes->get('servico/(:any)',  'Inteligencia::servico/$1');
});


