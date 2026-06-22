<?= $this->extend('layouts/default') ?>
<?php
$editando   = ! empty($ideia);
$breadcrumbs = [
    ['label' => 'Início',      'url' => base_url('/')],
    ['label' => $eixo['nome'], 'url' => base_url("/#pilar-{$eixo['slug']}")],
    ['label' => $editando ? 'Editar ideia' : 'Nova ideia', 'url' => ''],
];
$statusAtual = $editando ? ($ideia['status'] ?? 'rascunho') : 'rascunho';
?>

<?= $this->section('content') ?>

<!-- Cabeçalho da página -->
<div class="page-header">
    <div class="d-flex align-items-center gap-3 mb-2 flex-wrap">
        <div style="width:46px;height:46px;border-radius:12px;background:<?= esc($eixo['cor_bg']) ?>;display:flex;align-items:center;justify-content:center;font-size:1.25rem;color:<?= esc($eixo['cor']) ?>;">
            <i class="bi <?= esc($eixo['icone']) ?>"></i>
        </div>
        <div>
            <div style="font-size:.75rem;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:.06em;">
                <?= esc($eixo['nome']) ?>
            </div>
            <h1 style="font-size:1.3rem;font-weight:800;margin:0;">
                <?= $editando ? 'Editar Ideia' : 'Nova Ideia' ?>
            </h1>
        </div>
    </div>
</div>

<?php if (session()->has('erro')): ?>
<div class="alert alert-danger alert-dismissible fade show">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= session('erro') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<form method="post"
      action="<?= base_url($editando ? "ideias/{$ideia['id']}/atualizar" : 'ideias/salvar') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="eixo_id" value="<?= $eixo['id'] ?>">

    <div class="row g-4">

        <!-- ── Coluna principal ──────────────────────────────────── -->
        <div class="col-lg-8">

            <!-- Título -->
            <div class="idea-card mb-3">
                <label class="form-label fw-bold">Título da Ideia <span class="text-danger">*</span></label>
                <input type="text" name="titulo" class="form-control form-control-lg"
                       value="<?= esc($ideia['titulo'] ?? '') ?>"
                       placeholder="Ex: Volume e fidelização" required
                       style="font-size:1.1rem;font-weight:600;">
            </div>

            <!-- Campos dinâmicos -->
            <div class="idea-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0" style="font-weight:700;">
                        <i class="bi bi-card-list" style="color:<?= esc($eixo['cor']) ?>;"></i>
                        Campos da Ideia
                    </h5>
                    <button type="button" id="btn-add-campo" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-plus-lg"></i> Adicionar campo
                    </button>
                </div>

                <div id="campos-container">
                    <?php foreach ($campos as $idx => $campo): ?>
                    <div class="campo-row" data-index="<?= $idx ?>">
                        <div class="campo-row-header">
                            <input type="text"
                                   name="campo_label[]"
                                   class="form-control campo-label"
                                   value="<?= esc($campo['label']) ?>"
                                   placeholder="Nome do campo (ex: Objetivo)"
                                   style="font-weight:600;font-size:.85rem;max-width:220px;">
                            <select name="campo_tipo[]" class="form-select campo-tipo" style="max-width:160px;font-size:.82rem;">
                                <?php foreach ($tipos as $tKey => $tLabel): ?>
                                <option value="<?= $tKey ?>" <?= ($campo['tipo'] ?? '') === $tKey ? 'selected' : '' ?>>
                                    <?= esc($tLabel) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn-remove-campo btn btn-sm btn-outline-danger" title="Remover campo">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                        <?php
                        $tipo  = $campo['tipo'] ?? 'texto_longo';
                        $valor = $campo['valor'] ?? '';
                        ?>
                        <?php if ($tipo === 'texto_longo'): ?>
                        <textarea name="campo_valor[]" class="form-control campo-valor mt-2" rows="3"
                                  placeholder="Escreva aqui..."><?= esc($valor) ?></textarea>
                        <?php elseif ($tipo === 'referencia'): ?>
                        <div class="input-group mt-2">
                            <input type="text" name="campo_valor[]" class="form-control campo-valor"
                                   value="<?= esc($valor) ?>"
                                   placeholder="Módulo X, Cap. Y — Título">
                            <a href="<?= base_url('manuais/buscar') ?>" target="_blank"
                               class="btn btn-outline-secondary" title="Buscar no MANCAT">
                                <i class="bi bi-search"></i>
                            </a>
                        </div>
                        <?php elseif ($tipo === 'lista'): ?>
                        <textarea name="campo_valor[]" class="form-control campo-valor mt-2" rows="4"
                                  placeholder="Um item por linha..."><?= esc($valor) ?></textarea>
                        <?php elseif ($tipo === 'numero'): ?>
                        <input type="number" name="campo_valor[]" class="form-control campo-valor mt-2"
                               value="<?= esc($valor) ?>" placeholder="0">
                        <?php elseif ($tipo === 'data'): ?>
                        <input type="date" name="campo_valor[]" class="form-control campo-valor mt-2"
                               value="<?= esc($valor) ?>">
                        <?php else: ?>
                        <input type="text" name="campo_valor[]" class="form-control campo-valor mt-2"
                               value="<?= esc($valor) ?>"
                               placeholder="Valor...">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>

                <p id="sem-campos" class="text-muted text-center py-3 <?= count($campos) > 0 ? 'd-none' : '' ?>"
                   style="font-size:.85rem;">
                    <i class="bi bi-info-circle"></i>
                    Nenhum campo ainda. Clique em <strong>Adicionar campo</strong> para começar.
                </p>
            </div>
        </div>

        <!-- ── Coluna lateral ────────────────────────────────────── -->
        <div class="col-lg-4">

            <!-- Status -->
            <div class="idea-card mb-3">
                <label class="form-label fw-bold">Status</label>
                <div class="d-flex flex-column gap-2">
                    <?php foreach ($statusInfo as $sKey => $sInfo): ?>
                    <label class="status-radio <?= $statusAtual === $sKey ? 'status-radio-checked' : '' ?>"
                           style="--s-cor:<?= $sInfo['cor'] ?>;--s-bg:<?= $sInfo['bg'] ?>;">
                        <input type="radio" name="status" value="<?= $sKey ?>"
                               <?= $statusAtual === $sKey ? 'checked' : '' ?> class="d-none status-radio-input">
                        <span class="status-dot"></span>
                        <?= esc($sInfo['label']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tags -->
            <div class="idea-card mb-3">
                <label class="form-label fw-bold">Tags</label>
                <input type="text" name="tags" class="form-control"
                       value="<?= esc($ideia['tags'] ?? '') ?>"
                       placeholder="volume, fidelização, b2b">
                <div class="form-text">Separadas por vírgula.</div>
            </div>

            <!-- Buscar no MANCAT -->
            <div class="idea-card mb-3" style="background:<?= esc($eixo['cor_bg']) ?>;border-color:<?= esc($eixo['cor']) ?>30;">
                <p class="mb-2" style="font-size:.82rem;font-weight:600;color:<?= esc($eixo['cor']) ?>;">
                    <i class="bi bi-journal-bookmark-fill"></i> Explorar no MANCAT
                </p>
                <p style="font-size:.78rem;color:#475569;line-height:1.5;">
                    Use a pesquisa para encontrar embasamento nos documentos do manual.
                </p>
                <a href="<?= base_url('manuais/buscar') ?>" target="_blank" class="btn btn-sm w-100"
                   style="background:<?= esc($eixo['cor']) ?>;color:white;">
                    <i class="bi bi-search"></i> Abrir pesquisa
                </a>
            </div>

            <!-- Salvar -->
            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg" style="background:<?= esc($eixo['cor']) ?>;border-color:<?= esc($eixo['cor']) ?>;">
                    <i class="bi bi-floppy-fill"></i>
                    <?= $editando ? 'Salvar alterações' : 'Criar ideia' ?>
                </button>
                <a href="<?= base_url('/') ?>" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </div>

    </div>
</form>

<!-- Template oculto para novos campos -->
<template id="campo-template">
    <div class="campo-row" data-index="__IDX__">
        <div class="campo-row-header">
            <input type="text" name="campo_label[]" class="form-control campo-label"
                   placeholder="Nome do campo" style="font-weight:600;font-size:.85rem;max-width:220px;">
            <select name="campo_tipo[]" class="form-select campo-tipo" style="max-width:160px;font-size:.82rem;">
                <?php foreach ($tipos as $tKey => $tLabel): ?>
                <option value="<?= $tKey ?>"><?= esc($tLabel) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="button" class="btn-remove-campo btn btn-sm btn-outline-danger">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <textarea name="campo_valor[]" class="form-control campo-valor mt-2" rows="3"
                  placeholder="Escreva aqui..."></textarea>
    </div>
</template>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.idea-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
}
.campo-row {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1rem;
    margin-bottom: .75rem;
    transition: box-shadow .15s;
}
.campo-row:hover { box-shadow: 0 2px 8px rgba(0,0,0,.07); }
.campo-row-header {
    display: flex;
    align-items: center;
    gap: .5rem;
    flex-wrap: wrap;
}
.status-radio {
    display: flex;
    align-items: center;
    gap: .5rem;
    padding: .5rem .85rem;
    border-radius: 8px;
    border: 1.5px solid #e2e8f0;
    cursor: pointer;
    font-size: .84rem;
    font-weight: 500;
    transition: all .15s;
}
.status-radio:hover { border-color: var(--s-cor); background: var(--s-bg); }
.status-radio-checked { border-color: var(--s-cor); background: var(--s-bg); font-weight: 700; }
.status-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    background: var(--s-cor);
    flex-shrink: 0;
}
</style>
<script>
let campoIdx = <?= count($campos) ?>;

// Adicionar campo
document.getElementById('btn-add-campo').addEventListener('click', () => {
    const tmpl   = document.getElementById('campo-template').innerHTML;
    const html   = tmpl.replace(/__IDX__/g, campoIdx++);
    const div    = document.createElement('div');
    div.innerHTML = html;
    const novoRow = div.firstElementChild;
    document.getElementById('campos-container').appendChild(novoRow);
    document.getElementById('sem-campos').classList.add('d-none');
    novoRow.querySelector('.campo-label')?.focus();

    // Listener de tipo no novo campo
    novoRow.querySelector('.campo-tipo').addEventListener('change', trocarTipoCampo);
    novoRow.querySelector('.btn-remove-campo').addEventListener('click', removerCampo);
});

// Remover campo
function removerCampo() {
    const row = this.closest('.campo-row');
    row.remove();
    const container = document.getElementById('campos-container');
    if (container.querySelectorAll('.campo-row').length === 0) {
        document.getElementById('sem-campos').classList.remove('d-none');
    }
}

// Trocar tipo de campo (muda o input)
function trocarTipoCampo() {
    const row  = this.closest('.campo-row');
    const tipo = this.value;
    let valorEl = row.querySelector('.campo-valor');
    const valorAtual = valorEl ? valorEl.value : '';

    let novoInput;
    if (tipo === 'texto_longo' || tipo === 'lista') {
        novoInput = document.createElement('textarea');
        novoInput.rows = tipo === 'lista' ? 4 : 3;
        novoInput.placeholder = tipo === 'lista' ? 'Um item por linha...' : 'Escreva aqui...';
    } else if (tipo === 'numero') {
        novoInput = document.createElement('input');
        novoInput.type = 'number';
        novoInput.placeholder = '0';
    } else if (tipo === 'data') {
        novoInput = document.createElement('input');
        novoInput.type = 'date';
    } else if (tipo === 'referencia') {
        const wrapper = document.createElement('div');
        wrapper.className = 'input-group mt-2';
        const inp = document.createElement('input');
        inp.type = 'text'; inp.name = 'campo_valor[]';
        inp.className = 'form-control campo-valor';
        inp.placeholder = 'Módulo X, Cap. Y — Título';
        inp.value = valorAtual;
        const btn = document.createElement('a');
        btn.href = '<?= base_url('manuais/buscar') ?>'; btn.target = '_blank';
        btn.className = 'btn btn-outline-secondary'; btn.title = 'Buscar no MANCAT';
        btn.innerHTML = '<i class="bi bi-search"></i>';
        wrapper.appendChild(inp); wrapper.appendChild(btn);
        if (valorEl) valorEl.replaceWith ? valorEl.replaceWith(wrapper) : valorEl.parentNode.replaceChild(wrapper, valorEl);
        return;
    } else {
        novoInput = document.createElement('input');
        novoInput.type = 'text';
        novoInput.placeholder = 'Valor...';
    }
    novoInput.name = 'campo_valor[]';
    novoInput.className = 'form-control campo-valor mt-2';
    novoInput.value = valorAtual;
    if (valorEl) valorEl.replaceWith ? valorEl.replaceWith(novoInput) : valorEl.parentNode.replaceChild(novoInput, valorEl);
}

// Listener nos campos existentes
document.querySelectorAll('.campo-tipo').forEach(sel => sel.addEventListener('change', trocarTipoCampo));
document.querySelectorAll('.btn-remove-campo').forEach(btn => btn.addEventListener('click', removerCampo));

// Status radio visual
document.querySelectorAll('.status-radio-input').forEach(radio => {
    radio.addEventListener('change', () => {
        document.querySelectorAll('.status-radio').forEach(l => l.classList.remove('status-radio-checked'));
        if (radio.checked) radio.closest('.status-radio').classList.add('status-radio-checked');
    });
});
</script>
<?= $this->endSection() ?>
