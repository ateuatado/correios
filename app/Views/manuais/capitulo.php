<?= $this->extend('layouts/default') ?>

<?php
$breadcrumbs = [
    ['label' => 'Manuais',                                                   'url' => base_url('manuais')],
    ['label' => $capitulo['manual_codigo'],                                  'url' => base_url("manuais/arvore/{$capitulo['manual_id']}")],
    ['label' => "Módulo {$capitulo['modulo_numero']}",                       'url' => base_url("manuais/modulo/{$capitulo['modulo_id']}")],
    ['label' => "Capítulo {$capitulo['numero']} — {$capitulo['titulo']}",   'url' => ''],
];
?>

<?= $this->section('content') ?>

<div class="page-header">
    <div class="d-flex gap-2 align-items-center mb-1" style="flex-wrap:wrap;">
        <span class="badge" style="background:var(--cor-primaria);font-size:.72rem;"><?= esc($capitulo['manual_codigo']) ?></span>
        <span class="badge bg-secondary" style="font-size:.72rem;">Módulo <?= $capitulo['modulo_numero'] ?></span>
    </div>
    <h1>Capítulo <?= $capitulo['numero'] ?></h1>
    <p class="subtitle"><?= esc($capitulo['titulo']) ?></p>
</div>

<!-- Arquivo do capítulo -->
<?php if (! empty($capitulo['arquivo_nome'])): ?>
<div class="alert alert-info d-flex align-items-center gap-2 mb-4" style="border-radius:.625rem;">
    <i class="bi bi-file-earmark-word fs-5"></i>
    <div>
        <strong>Documento:</strong> <?= esc($capitulo['arquivo_nome']) ?>
    </div>
</div>
<?php endif; ?>

<!-- Anexos -->
<?php if (! empty($capitulo['anexos'])): ?>
<h5 class="mb-3" style="font-weight:700;">
    <i class="bi bi-paperclip me-2" style="color:var(--cor-primaria);"></i>
    Anexos <span class="badge bg-warning text-dark ms-1"><?= count($capitulo['anexos']) ?></span>
</h5>

<div class="row g-2">
    <?php foreach ($capitulo['anexos'] as $anx): ?>
    <div class="col-12">
        <div class="d-flex align-items-center gap-3 p-3 bg-white border rounded-3"
             style="transition:box-shadow .15s;"
             onmouseover="this.style.boxShadow='0 2px 12px rgba(0,63,136,.1)'"
             onmouseout="this.style.boxShadow='none'">
            <div style="background:#fff8e1;color:#b45309;border-radius:.5rem;width:44px;height:44px;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;">
                Anx.<br><?= $anx['numero'] ?>
            </div>
            <div class="flex-fill">
                <div class="fw-semibold" style="font-size:.9rem;"><?= esc($anx['titulo']) ?></div>
                <?php if (! empty($anx['arquivo_nome'])): ?>
                <div style="font-size:.75rem;color:#6b7a8d;margin-top:.2rem;">
                    <i class="bi bi-file-earmark me-1"></i><?= esc($anx['arquivo_nome']) ?>
                </div>
                <?php endif; ?>
            </div>
            <?php if (! empty($anx['arquivo_caminho'])): ?>
            <a href="#" class="btn btn-sm btn-outline-primary" title="Abrir documento">
                <i class="bi bi-box-arrow-up-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php else: ?>
<div class="text-center py-5" style="color:#6b7a8d;">
    <i class="bi bi-folder-x" style="font-size:2.5rem;display:block;margin-bottom:.75rem;"></i>
    <strong>Este capítulo não possui anexos.</strong>
</div>
<?php endif; ?>

<?= $this->endSection() ?>
