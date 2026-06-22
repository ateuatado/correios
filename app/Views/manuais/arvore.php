<?= $this->extend('layouts/default') ?>

<?php
// Breadcrumbs
$breadcrumbs = [
    ['label' => 'Manuais', 'url' => base_url('manuais')],
    ['label' => $manual['codigo'] . ' — ' . $manual['nome'], 'url' => ''],
];
// Sidebar com contexto
$sidebar_manual = $manual;
?>

<?= $this->section('content') ?>

<!-- Page Header -->
<div class="page-header">
    <div class="d-flex align-items-start gap-3">
        <div style="background:var(--cor-primaria);color:#fff;border-radius:.625rem;padding:.75rem 1rem;font-size:1.1rem;font-weight:700;white-space:nowrap;">
            <?= esc($manual['codigo']) ?>
        </div>
        <div>
            <h1 style="font-size:1.3rem;"><?= esc($manual['nome']) ?></h1>
            <?php if (! empty($manual['sumario'])): ?>
            <p class="subtitle mb-0"><?= esc($manual['sumario']) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Estatísticas do manual -->
<?php
    $totalCaps   = 0;
    $totalAnexos = 0;
    foreach ($manual['modulos'] as $mod) {
        foreach ($mod['capitulos'] as $cap) {
            $totalCaps++;
            $totalAnexos += count($cap['anexos']);
        }
    }
?>
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f0fe;">
                <i class="bi bi-folder2" style="color:var(--cor-primaria);"></i>
            </div>
            <div>
                <div class="stat-value"><?= count($manual['modulos']) ?></div>
                <div class="stat-label">Módulos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e8f5e9;">
                <i class="bi bi-file-text" style="color:#2e7d32;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalCaps ?></div>
                <div class="stat-label">Capítulos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fff8e1;">
                <i class="bi bi-paperclip" style="color:#b45309;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalAnexos ?></div>
                <div class="stat-label">Anexos</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-icon" style="background:#fce4ec;">
                <i class="bi bi-files" style="color:#c62828;"></i>
            </div>
            <div>
                <div class="stat-value"><?= $totalCaps + $totalAnexos ?></div>
                <div class="stat-label">Total de Docs</div>
            </div>
        </div>
    </div>
</div>

<!-- Árvore de módulos / capítulos / anexos -->
<div class="tree-container">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0" style="font-weight:700;">
            <i class="bi bi-diagram-3 me-2" style="color:var(--cor-primaria);"></i>
            Estrutura do Manual
        </h5>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnExpandAll">
                <i class="bi bi-arrows-expand"></i> Expandir tudo
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCollapseAll">
                <i class="bi bi-arrows-collapse"></i> Recolher tudo
            </button>
        </div>
    </div>

    <?php if (empty($manual['modulos'])): ?>
    <div class="text-center py-4" style="color:#6b7a8d;">
        <i class="bi bi-folder2-open" style="font-size:2rem;display:block;margin-bottom:.5rem;"></i>
        Nenhum módulo encontrado neste manual.
    </div>
    <?php else: ?>
    <ul class="tree-main">
        <?php foreach ($manual['modulos'] as $modIdx => $modulo): ?>
        <li>
            <!-- ── Módulo ─────────────────────────────────────── -->
            <div class="tree-modulo-header"
                 data-target="mod-<?= $modulo['id'] ?>"
                 aria-expanded="<?= $modIdx === 0 ? 'true' : 'false' ?>">
                <i class="bi bi-folder2 text-primary"></i>
                <span class="badge-modulo">Módulo <?= $modulo['numero'] ?></span>
                <span class="modulo-titulo"><?= esc($modulo['titulo']) ?></span>
                <span style="font-size:.75rem;color:#999;" class="me-2">
                    <?= count($modulo['capitulos']) ?> cap.
                </span>
                <i class="bi bi-chevron-right toggle-chevron"></i>
            </div>

            <!-- ── Capítulos do módulo ───────────────────────── -->
            <ul class="tree-capitulo-list <?= $modIdx === 0 ? '' : 'd-none' ?>"
                id="mod-<?= $modulo['id'] ?>">
                <?php if (empty($modulo['capitulos'])): ?>
                <li class="ps-4 py-2" style="font-size:.8rem;color:#999;">
                    <i class="bi bi-dash"></i> Sem capítulos
                </li>
                <?php else: ?>
                <?php foreach ($modulo['capitulos'] as $capIdx => $cap): ?>
                <li class="tree-capitulo-item">
                    <?php $hasAnexos = ! empty($cap['anexos']); ?>

                    <?php if ($hasAnexos): ?>
                    <!-- Capítulo com anexos: clicável para expandir -->
                    <div class="tree-capitulo-header"
                         data-target="cap-<?= $cap['id'] ?>"
                         aria-expanded="false">
                        <i class="bi bi-file-earmark-text" style="color:#2e7d32;"></i>
                        <span class="badge-capitulo">Cap. <?= $cap['numero'] ?></span>
                        <span class="capitulo-titulo"><?= esc($cap['titulo']) ?></span>
                        <span style="font-size:.72rem;color:#999;" class="me-1">
                            <?= count($cap['anexos']) ?> anx.
                        </span>
                        <i class="bi bi-chevron-right toggle-chevron" style="font-size:.7rem;color:#999;margin-left:auto;transition:transform .2s;"></i>
                    </div>
                    <?php else: ?>
                    <!-- Capítulo sem anexos: link direto (quando implementado) -->
                    <div class="tree-capitulo-header">
                        <i class="bi bi-file-earmark-text" style="color:#2e7d32;"></i>
                        <span class="badge-capitulo">Cap. <?= $cap['numero'] ?></span>
                        <span class="capitulo-titulo"><?= esc($cap['titulo']) ?></span>
                    </div>
                    <?php endif; ?>

                    <!-- Anexos do capítulo -->
                    <?php if ($hasAnexos): ?>
                    <ul class="tree-anexo-list d-none" id="cap-<?= $cap['id'] ?>">
                        <?php foreach ($cap['anexos'] as $anx): ?>
                        <li class="tree-anexo-item">
                            <span class="tree-anexo-link">
                                <i class="bi bi-paperclip" style="color:#b45309;"></i>
                                <span class="badge-anexo">Anx. <?= $anx['numero'] ?></span>
                                <span><?= esc($anx['titulo']) ?></span>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php endif; ?>
            </ul>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.getElementById('btnExpandAll')?.addEventListener('click', function () {
    document.querySelectorAll('.tree-modulo-header').forEach(h => {
        const t = document.getElementById(h.dataset.target);
        if (t) { t.classList.remove('d-none'); h.setAttribute('aria-expanded','true'); }
    });
    document.querySelectorAll('.tree-capitulo-header[data-target]').forEach(h => {
        const t = document.getElementById(h.dataset.target);
        if (t) { t.classList.remove('d-none'); h.setAttribute('aria-expanded','true'); }
    });
});
document.getElementById('btnCollapseAll')?.addEventListener('click', function () {
    document.querySelectorAll('.tree-modulo-header').forEach(h => {
        const t = document.getElementById(h.dataset.target);
        if (t) { t.classList.add('d-none'); h.setAttribute('aria-expanded','false'); }
    });
    document.querySelectorAll('.tree-capitulo-header[data-target]').forEach(h => {
        const t = document.getElementById(h.dataset.target);
        if (t) { t.classList.add('d-none'); h.setAttribute('aria-expanded','false'); }
    });
});
// Toggle chevron em capítulos
document.querySelectorAll('.tree-capitulo-header[data-target]').forEach(h => {
    h.addEventListener('click', function(){
        const chevron = this.querySelector('.toggle-chevron');
        const isOpen = this.getAttribute('aria-expanded') === 'true';
        if (chevron) chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(90deg)';
    });
});
</script>
<?= $this->endSection() ?>
