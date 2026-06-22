<?= $this->extend('layouts/default') ?>
<?php $breadcrumbs = [['label' => 'Inteligência', 'url' => '']]; ?>

<?= $this->section('content') ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.4rem;font-weight:800;">
            <i class="bi bi-cpu-fill" style="color:var(--cor-primaria);"></i>
            Inteligência sobre o MANCAT
        </h1>
        <p class="subtitle">
            <?= number_format($totalRegras, 0, ',', '.') ?> regras extraídas automaticamente de 8.910 itens.
            Pesos, prazos, restrições, elegibilidade e mais — sem precisar abrir um arquivo Word.
        </p>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="<?= base_url('inteligencia/regras') ?>" class="btn btn-primary">
            <i class="bi bi-table"></i> Ver todas as regras
        </a>
        <a href="<?= base_url('inteligencia/comparar') ?>" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left-right"></i> Comparar serviços
        </a>
    </div>
</div>

<!-- ── Cards por tipo ──────────────────────────────────────────────── -->
<div class="row g-3 mb-4">
    <?php foreach ($porTipo as $t): ?>
    <?php $info = $t['info']; ?>
    <div class="col-md-4 col-sm-6">
        <a href="<?= base_url('inteligencia/regras?tipo=' . $t['tipo']) ?>" class="intel-tipo-card"
           style="--tc:<?= $info['cor'] ?>;--tbg:<?= $info['bg'] ?>;">
            <div class="intel-tipo-icone"><i class="bi <?= $info['icone'] ?>"></i></div>
            <div class="intel-tipo-info">
                <div class="intel-tipo-label"><?= $info['label'] ?></div>
                <div class="intel-tipo-count"><?= number_format($t['total'], 0, ',', '.') ?></div>
            </div>
            <i class="bi bi-chevron-right intel-tipo-arrow"></i>
        </a>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── Por Serviço ─────────────────────────────────────────────────── -->
<div class="tree-container mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h5 class="mb-0" style="font-weight:700;">
            <i class="bi bi-truck" style="color:var(--cor-primaria);"></i>
            Regras por Serviço
        </h5>
        <a href="<?= base_url('inteligencia/comparar') ?>" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-arrow-left-right"></i> Comparar lado a lado
        </a>
    </div>

    <div class="row g-2">
        <?php foreach ($porServico as $s): ?>
        <?php
        $srv = $s['servico'];
        $url = $srv === '(Geral)'
            ? base_url('inteligencia/regras?servico=_geral_')
            : base_url('inteligencia/servico/' . urlencode($srv));
        ?>
        <div class="col-auto">
            <a href="<?= $url ?>" class="servico-pill">
                <span class="servico-nome"><?= esc($srv) ?></span>
                <span class="servico-count"><?= $s['total'] ?></span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── CTA ─────────────────────────────────────────────────────────── -->
<div class="alert alert-info border-0 d-flex align-items-start gap-3" style="background:linear-gradient(135deg,#e8f0fe,#dbeafe);border-radius:12px;">
    <i class="bi bi-info-circle-fill fs-4 mt-1" style="color:#1565C0;flex-shrink:0;"></i>
    <div>
        <strong>Como manter atualizado?</strong><br>
        Execute <code>php spark regras:extract --reset</code> sempre que novos documentos forem importados.
        O processo leva menos de 1 minuto para os 8.910 itens.
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.intel-tipo-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    text-decoration: none;
    color: inherit;
    transition: all .2s;
    border-left: 4px solid var(--tc);
}
.intel-tipo-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,.08);
    transform: translateY(-2px);
    border-color: var(--tc);
}
.intel-tipo-icone {
    width: 42px; height: 42px;
    border-radius: 10px;
    background: var(--tbg);
    color: var(--tc);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}
.intel-tipo-info { flex: 1; }
.intel-tipo-label {
    font-size: .78rem;
    font-weight: 600;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.intel-tipo-count {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.1;
}
.intel-tipo-arrow { color: #cbd5e1; font-size: .85rem; }
.intel-tipo-card:hover .intel-tipo-arrow { color: var(--tc); }

.servico-pill {
    display: inline-flex;
    align-items: center;
    gap: .5rem;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: .35rem 1rem;
    text-decoration: none;
    color: #374151;
    font-size: .82rem;
    font-weight: 600;
    transition: all .15s;
}
.servico-pill:hover {
    background: var(--cor-primaria);
    border-color: var(--cor-primaria);
    color: white;
}
.servico-count {
    background: #f1f5f9;
    border-radius: 10px;
    padding: .05rem .45rem;
    font-size: .72rem;
    color: #64748b;
}
.servico-pill:hover .servico-count { background: rgba(255,255,255,.2); color: white; }
</style>
<?= $this->endSection() ?>
