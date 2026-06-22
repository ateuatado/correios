<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// ── Home ──────────────────────────────────────────────────────────
$routes->get('/', 'Home::index');

// ── Módulo: Manuais ──────────────────────────────────────────────
$routes->group('manuais', ['namespace' => 'App\Controllers'], function ($routes) {

    // Lista todos os manuais
    $routes->get('/',        'Manuais::index');

    // Árvore completa de um manual
    $routes->get('arvore/(:num)', 'Manuais::arvore/$1');

    // Detalhe de um módulo
    $routes->get('modulo/(:num)', 'Manuais::modulo/$1');

    // Detalhe de um capítulo (com anexos)
    $routes->get('capitulo/(:num)', 'Manuais::capitulo/$1');
});
