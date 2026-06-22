<?= $this->extend('layouts/default') ?>
<?php
$editando   = ! empty($eixo);
$breadcrumbs = [
    ['label' => 'Eixos', 'url' => base_url('eixos')],
    ['label' => $editando ? 'Editar' : 'Novo', 'url' => ''],
];
$icones = [
    'bi-bullseye','bi-file-earmark-ruled','bi-calculator','bi-grid-1x2',
    'bi-cpu','bi-shield-check','bi-bank','bi-globe-americas',
    'bi-lightbulb','bi-rocket','bi-graph-up','bi-people',
    'bi-patch-check','bi-truck','bi-box','bi-chat-dots',
];
?>

<?= $this->section('content') ?>

<div class="page-header">
    <h1 style="font-size:1.3rem;font-weight:800;">
        <i class="bi bi-<?= $editando ? 'pencil' : 'plus-lg' ?>" style="color:var(--cor-primaria);"></i>
        <?= esc($title) ?>
    </h1>
</div>

<form method="post" action="<?= base_url($editando ? "eixos/atualizar/{$eixo['id']}" : 'eixos/criar') ?>" class="row g-3" style="max-width:720px;">
    <?= csrf_field() ?>

    <!-- Nome -->
    <div class="col-12">
        <label class="form-label fw-semibold">Nome do Eixo <span class="text-danger">*</span></label>
        <input type="text" name="nome" class="form-control"
               value="<?= esc($eixo['nome'] ?? '') ?>"
               placeholder="Ex: Posicionamento e Estratégia" required>
    </div>

    <!-- Descrição -->
    <div class="col-12">
        <label class="form-label fw-semibold">Descrição</label>
        <textarea name="descricao" class="form-control" rows="3"
                  placeholder="Resumo do que abrange este eixo..."><?= esc($eixo['descricao'] ?? '') ?></textarea>
    </div>

    <!-- Ícone + Prévia -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Ícone Bootstrap</label>
        <div class="input-group">
            <span class="input-group-text" id="icon-preview" style="background:<?= esc($eixo['cor_bg'] ?? '#e8f0fe') ?>;color:<?= esc($eixo['cor'] ?? '#003F88') ?>;">
                <i class="bi <?= esc($eixo['icone'] ?? 'bi-lightbulb') ?>" id="icon-preview-i"></i>
            </span>
            <select name="icone" class="form-select" id="icone-select">
                <?php foreach ($icones as $ic): ?>
                <option value="<?= $ic ?>" <?= ($eixo['icone'] ?? 'bi-lightbulb') === $ic ? 'selected' : '' ?>>
                    <?= $ic ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <!-- Ordem -->
    <div class="col-md-6">
        <label class="form-label fw-semibold">Ordem de exibição</label>
        <input type="number" name="ordem" class="form-control"
               value="<?= esc($eixo['ordem'] ?? 99) ?>" min="1" max="99">
    </div>

    <!-- Cor principal + Cor de fundo -->
    <div class="col-md-4">
        <label class="form-label fw-semibold">Cor principal</label>
        <div class="input-group">
            <input type="color" name="cor" id="cor-picker" class="form-control form-control-color"
                   value="<?= esc($eixo['cor'] ?? '#003F88') ?>" style="max-width:54px;">
            <input type="text" id="cor-text" class="form-control" readonly
                   value="<?= esc($eixo['cor'] ?? '#003F88') ?>">
        </div>
    </div>

    <div class="col-md-4">
        <label class="form-label fw-semibold">Cor de fundo</label>
        <div class="input-group">
            <input type="color" name="cor_bg" id="corbg-picker" class="form-control form-control-color"
                   value="<?= esc($eixo['cor_bg'] ?? '#e8f0fe') ?>" style="max-width:54px;">
            <input type="text" id="corbg-text" class="form-control" readonly
                   value="<?= esc($eixo['cor_bg'] ?? '#e8f0fe') ?>">
        </div>
    </div>

    <!-- Tags -->
    <div class="col-md-4">
        <label class="form-label fw-semibold">Tags <span style="font-weight:400;color:#94a3b8;">(separadas por vírgula)</span></label>
        <input type="text" name="tags" class="form-control"
               value="<?= esc($eixo['tags'] ?? '') ?>"
               placeholder="Ex: Estratégia,Mercado">
    </div>

    <!-- Ativo -->
    <div class="col-12">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="ativo" id="ativo" value="1"
                   <?= ($eixo['ativo'] ?? 1) ? 'checked' : '' ?>>
            <label class="form-check-label fw-semibold" for="ativo">Ativo (visível na home)</label>
        </div>
    </div>

    <!-- Prévia do card -->
    <div class="col-12">
        <label class="form-label fw-semibold">Prévia</label>
        <div id="card-preview" style="background:white;border:1px solid #e2e8f0;border-radius:12px;padding:1rem;max-width:320px;position:relative;overflow:hidden;border-left:4px solid <?= esc($eixo['cor'] ?? '#003F88') ?>;">
            <div id="prev-icon" style="width:44px;height:44px;border-radius:10px;background:<?= esc($eixo['cor_bg'] ?? '#e8f0fe') ?>;display:flex;align-items:center;justify-content:center;font-size:1.3rem;color:<?= esc($eixo['cor'] ?? '#003F88') ?>;margin-bottom:.75rem;">
                <i class="bi <?= esc($eixo['icone'] ?? 'bi-lightbulb') ?>" id="prev-icon-i"></i>
            </div>
            <div id="prev-nome" style="font-size:.95rem;font-weight:700;color:#0f172a;"><?= esc($eixo['nome'] ?? 'Nome do eixo') ?></div>
        </div>
    </div>

    <!-- Ações -->
    <div class="col-12 d-flex gap-2 pt-2">
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-floppy"></i> <?= $editando ? 'Salvar alterações' : 'Criar eixo' ?>
        </button>
        <a href="<?= base_url('eixos') ?>" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
// Prévia em tempo real
const nomeInput  = document.querySelector('[name="nome"]');
const iconeSel   = document.getElementById('icone-select');
const corPicker  = document.getElementById('cor-picker');
const corbgPicker= document.getElementById('corbg-picker');
const corText    = document.getElementById('cor-text');
const corbgText  = document.getElementById('corbg-text');
const preview    = document.getElementById('card-preview');
const prevIcon   = document.getElementById('prev-icon');
const prevIconI  = document.getElementById('prev-icon-i');
const prevIconPre= document.getElementById('icon-preview');
const prevIconPreI = document.getElementById('icon-preview-i');
const prevNome   = document.getElementById('prev-nome');

function atualizarPrevia() {
    const cor   = corPicker.value;
    const corbg = corbgPicker.value;
    const icone = iconeSel.value;
    const nome  = nomeInput.value || 'Nome do eixo';

    corText.value  = cor;
    corbgText.value = corbg;

    preview.style.borderLeftColor = cor;
    prevIcon.style.background     = corbg;
    prevIcon.style.color          = cor;
    prevIconI.className           = 'bi ' + icone;
    prevNome.textContent          = nome;

    prevIconPre.style.background = corbg;
    prevIconPre.style.color      = cor;
    prevIconPreI.className       = 'bi ' + icone;
}

nomeInput.addEventListener('input', atualizarPrevia);
iconeSel.addEventListener('change', atualizarPrevia);
corPicker.addEventListener('input', atualizarPrevia);
corbgPicker.addEventListener('input', atualizarPrevia);
</script>
<?= $this->endSection() ?>
