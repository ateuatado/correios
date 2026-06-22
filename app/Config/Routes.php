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
