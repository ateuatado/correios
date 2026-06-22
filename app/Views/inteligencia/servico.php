<?= $this->extend('layouts/default') ?>
<?php
$breadcrumbs = [
    ['label' => 'Inteligência',  'url' => base_url('inteligencia')],
    ['label' => $servico,        'url' => ''],
];
?>

<?= $this->section('content') ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.5rem;font-weight:800;">
            <i class="bi bi-truck" style="color:var(--cor-primaria);"></i>
            <?= esc($servico) ?>
        </h1>
        <p class="subtitle">Ficha completa de regras extraídas do MANCAT para este serviço.</p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= base_url('inteligencia/regras?servico=' . urlencode($servico)) ?>"
           class="btn btn-outline-primary btn-sm">
            <i class="bi bi-table"></i> Todas as regras
        </a>
        <a href="<?= base_url('manuais/buscar?q=' . urlencode($servico)) ?>"
           class="btn btn-outline-secondary btn-sm" target="_blank">
            <i class="bi bi-search"></i> Buscar no MANCAT
        </a>
    </div>
</div>

<?php
$temQualquer = false;
foreach ($resultado as $l) {
    if (! empty($l['servicos'][$servico])) { $temQualquer = true; break; }
}
?>

<?php if (! $temQualquer): ?>
<div class="alert alert-info">
    Nenhuma regra estruturada encontrada especificamente para <strong><?= esc($servico) ?></strong>.
    <a href="<?= base_url('manuais/buscar?q=' . urlencode($servico)) ?>" target="_blank">
        Busque nos textos completos →
    </a>
</div>
<?php else: ?>

<?php foreach ($resultado as $linha): ?>
<?php
$tipo  = $linha['tipo'];
$ti    = $tipoInfo[$tipo] ?? $tipoInfo['outro'];
$rows  = $linha['servicos'][$servico] ?? [];
if (empty($rows)) continue;
?>
<div class="tree-container mb-3">
    <div class="d-flex align-items-center gap-2 mb-3">
        <div style="width:36px;height:36px;border-radius:9px;background:<?= $ti['bg'] ?>;
                    color:<?= $ti['cor'] ?>;display:flex;align-items:center;justify-content:center;font-size:1rem;">
            <i class="bi <?= $ti['icone'] ?>"></i>
        </div>
        <h5 class="mb-0" style="font-weight:700;"><?= $ti['label'] ?></h5>
        <span class="badge ms-1" style="background:<?= $ti['bg'] ?>;color:<?= $ti['cor'] ?>;font-size:.72rem;">
            <?= count($rows) ?> <?= count($rows) === 1 ? 'regra' : 'regras' ?>
        </span>
    </div>

    <div class="d-flex flex-column gap-2">
    <?php foreach ($rows as $row): ?>
    <div class="ficha-regra-item">
        <div class="d-flex align-items-start justify-content-between gap-2 flex-wrap">
            <div style="flex:1;">
                <?php if ($row['valor_numerico'] !== null): ?>
                <span style="font-size:1.4rem;font-weight:900;color:<?= $ti['cor'] ?>;">
                    <?= number_format((float)$row['valor_numerico'], 0, ',', '.') ?>
                    <?php if (! empty($row['unidade'])): ?>
                    <span style="font-size:.8rem;font-weight:600;color:#64748b;"><?= esc($row['unidade']) ?></span>
                    <?php endif; ?>
                </span>
                <br>
                <?php endif; ?>
                <span style="font-size:.87rem;color:#1e293b;line-height:1.5;">
                    <?= esc($row['descricao']) ?>
                </span>
            </div>
        </div>
        <?php if (! empty($row['contexto'])): ?>
        <div class="ficha-contexto">
            <i class="bi bi-quote" style="opacity:.4;"></i>
            <?= esc(mb_substr($row['contexto'], 0, 300)) ?>
        </div>
        <?php endif; ?>
        <div class="d-flex align-items-center gap-2 mt-2">
            <span style="font-size:.72rem;color:#94a3b8;">
                <i class="bi bi-geo-alt"></i> <?= esc($row['fonte']) ?>
            </span>
            <a href="<?= base_url('manuais/capitulo/' . $row['item_id']) ?>"
               target="_blank" class="btn btn-sm btn-outline-secondary" style="padding:.15rem .5rem;font-size:.72rem;">
                <i class="bi bi-box-arrow-up-right"></i> Ver no MANCAT
            </a>
        </div>
    </div>
    <?php endforeach; ?>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.ficha-regra-item {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
}
.ficha-contexto {
    font-size: .78rem;
    color: #64748b;
    font-style: italic;
    line-height: 1.5;
    margin-top: .5rem;
    padding: .5rem;
    background: white;
    border-left: 3px solid #e2e8f0;
    border-radius: 0 6px 6px 0;
}
</style>
<?= $this->endSection() ?>
