<?= $this->extend('layouts/default') ?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1><i class="bi bi-journal-bookmark-fill me-2" style="color:var(--cor-primaria);"></i>Manuais</h1>
        <p class="subtitle">Selecione um manual para navegar pela estrutura de módulos, capítulos e anexos.</p>
    </div>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f0fe;">
                <i class="bi bi-journal-text" style="color:var(--cor-primaria);"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($manuais) ?></div>
                <div class="stat-label">Manuais</div>
            </div>
        </div>
    </div>
    <?php
        $totalModulos   = array_sum(array_column($manuais, 'total_modulos'));
    ?>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;">
                <i class="bi bi-folder2-open" style="color:#b45309;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalModulos ?></div>
                <div class="stat-label">Módulos</div>
            </div>
        </div>
    </div>
</div>

<!-- Listagem de Manuais -->
<?php if (empty($manuais)): ?>
<div class="text-center py-5" style="color:#6b7a8d;">
    <i class="bi bi-inbox" style="font-size:3rem;display:block;margin-bottom:1rem;"></i>
    <strong>Nenhum manual cadastrado.</strong><br>
    <small>Execute o script de importação do MANCAT para popular o banco de dados.</small>
</div>
<?php else: ?>
<div class="row g-3">
    <?php foreach ($manuais as $manual): ?>
    <div class="col-12 col-md-6 col-xl-4">
        <a href="<?= base_url("manuais/arvore/{$manual['id']}") ?>" class="card-manual">
            <span class="manual-badge"><?= esc($manual['codigo']) ?></span>
            <h4><?= esc($manual['nome']) ?></h4>
            <?php if (! empty($manual['sumario'])): ?>
            <p class="mb-2" style="font-size:.83rem;color:#4a5568;line-height:1.4;">
                <?= esc(mb_substr($manual['sumario'], 0, 120)) ?>…
            </p>
            <?php endif; ?>
            <div class="manual-meta d-flex gap-3 mt-2">
                <span><i class="bi bi-folder2"></i> <?= (int)$manual['total_modulos'] ?> módulo<?= (int)$manual['total_modulos'] !== 1 ? 's' : '' ?></span>
                <span class="ms-auto text-primary" style="font-weight:600;font-size:.78rem;">
                    Ver estrutura <i class="bi bi-arrow-right-short"></i>
                </span>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
