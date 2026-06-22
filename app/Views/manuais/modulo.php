<?= $this->extend('layouts/default') ?>

<?php
$breadcrumbs = [
    ['label' => 'Manuais',                                           'url' => base_url('manuais')],
    ['label' => $manual['codigo'],                                   'url' => base_url("manuais/arvore/{$manual['id']}")],
    ['label' => "Módulo {$modulo['numero']} — {$modulo['titulo']}", 'url' => ''],
];
?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="d-flex align-items-center gap-2 mb-1">
        <span class="badge" style="background:var(--cor-primaria);font-size:.75rem;">
            <?= esc($manual['codigo']) ?>
        </span>
    </div>
    <h1>Módulo <?= $modulo['numero'] ?> — <?= esc($modulo['titulo']) ?></h1>
</div>

<!-- Capítulos -->
<div class="row g-3">
    <?php if (empty($modulo['capitulos'])): ?>
    <div class="col-12 text-center py-5" style="color:#6b7a8d;">
        <i class="bi bi-folder2-open" style="font-size:2rem;"></i>
        <p class="mt-2">Nenhum capítulo encontrado neste módulo.</p>
    </div>
    <?php else: ?>
    <?php foreach ($modulo['capitulos'] as $cap): ?>
    <div class="col-12">
        <div class="card-manual p-0" style="border-radius:.75rem;overflow:hidden;">
            <div class="d-flex">
                <div style="background:var(--cor-primaria);color:#fff;padding:.75rem 1rem;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;min-width:80px;text-align:center;white-space:nowrap;">
                    Cap. <?= $cap['numero'] ?>
                </div>
                <div class="p-3 flex-fill">
                    <div class="fw-semibold mb-1"><?= esc($cap['titulo']) ?></div>
                    <?php if (! empty($cap['anexos'])): ?>
                    <div style="font-size:.78rem;color:#6b7a8d;">
                        <i class="bi bi-paperclip me-1"></i>
                        <?= count($cap['anexos']) ?> anexo<?= count($cap['anexos']) !== 1 ? 's' : '' ?>:
                        <?php foreach ($cap['anexos'] as $i => $anx): ?>
                            <?php if ($i > 0): ?>, <?php endif; ?>
                            <span>Anx. <?= $anx['numero'] ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <a href="<?= base_url("manuais/capitulo/{$cap['id']}") ?>"
                   class="d-flex align-items-center px-3 border-start"
                   style="color:var(--cor-primaria);text-decoration:none;"
                   title="Ver detalhes">
                    <i class="bi bi-arrow-right-circle" style="font-size:1.3rem;"></i>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
