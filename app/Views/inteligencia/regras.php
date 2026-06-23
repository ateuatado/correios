<?= $this->extend('layouts/default') ?>
<?php
$breadcrumbs = [
    ['label' => 'Inteligência', 'url' => base_url('inteligencia')],
    ['label' => 'Regras extraídas', 'url' => ''],
];
$tipoAtual    = $filtros['tipo']    ?? '';
$servicoAtual = $filtros['servico'] ?? '';
$qAtual       = $filtros['q']       ?? '';
$totalPages   = ceil($total / $perPage);
?>

<?= $this->section('content') ?>

<div class="page-header d-flex align-items-center justify-content-between flex-wrap gap-2">
    <div>
        <h1 style="font-size:1.3rem;font-weight:800;">
            <i class="bi bi-table" style="color:var(--cor-primaria);"></i>
            Regras extraídas do MANCAT
        </h1>
        <p class="subtitle">
            <strong><?= number_format($total, 0, ',', '.') ?></strong> regras encontradas.
            <?php if ($tipoAtual || $servicoAtual || $qAtual): ?>
            <a href="<?= base_url('inteligencia/regras') ?>" class="ms-2 text-muted" style="font-size:.8rem;">
                <i class="bi bi-x-circle"></i> Limpar filtros
            </a>
            <?php endif; ?>
        </p>
    </div>
    <a href="<?= base_url('inteligencia/comparar') ?>" class="btn btn-outline-primary btn-sm">
        <i class="bi bi-arrow-left-right"></i> Comparar serviços
    </a>
</div>

<!-- Filtros -->
<form method="get" action="<?= base_url('inteligencia/regras') ?>" class="tree-container mb-3">
    <div class="row g-2 align-items-end">

        <!-- Busca livre -->
        <div class="col-md-4">
            <label class="form-label fw-semibold" style="font-size:.8rem;">Buscar</label>
            <div class="input-group input-group-sm">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="q" class="form-control"
                       value="<?= esc($qAtual) ?>"
                       placeholder="Ex: prazo, peso, não permitido...">
            </div>
        </div>

        <!-- Tipo -->
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.8rem;">Tipo de Regra</label>
            <select name="tipo" class="form-select form-select-sm">
                <option value="">Todos os tipos</option>
                <?php foreach ($tipoInfo as $tKey => $tInfo): ?>
                <option value="<?= $tKey ?>" <?= $tipoAtual === $tKey ? 'selected' : '' ?>>
                    <?= $tInfo['label'] ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Serviço -->
        <div class="col-md-3">
            <label class="form-label fw-semibold" style="font-size:.8rem;">Serviço</label>
            <select name="servico" class="form-select form-select-sm">
                <option value="">Todos</option>
                <option value="_geral_" <?= $servicoAtual === '_geral_' ? 'selected' : '' ?>>
                    (Geral / sem serviço)
                </option>
                <?php foreach ($servicos as $s): ?>
                <option value="<?= esc($s['servico']) ?>"
                    <?= $servicoAtual === $s['servico'] ? 'selected' : '' ?>>
                    <?= esc($s['servico']) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-auto">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-funnel-fill"></i> Filtrar
            </button>
        </div>
    </div>
</form>

<!-- Tabela -->
<div class="tree-container p-0">
    <?php if (empty($regras)): ?>
    <p class="text-center text-muted py-5">
        <i class="bi bi-search fs-3 d-block mb-2"></i>
        Nenhuma regra encontrada com esses filtros.
    </p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle" style="font-size:.83rem;">
            <thead style="background:#f8fafc;font-size:.72rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">
                <tr>
                    <th style="width:110px;">Tipo</th>
                    <th style="width:90px;">Serviço</th>
                    <th>Descrição / Interpretação IA</th>
                    <th style="width:80px;">Valor</th>
                    <th style="width:160px;">Fonte</th>
                    <th style="width:70px;"></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($regras as $r): ?>
            <?php
            $ti    = $tipoInfo[$r['tipo']] ?? $tipoInfo['outro'];
            $srv   = $r['servico'] ?? null;
            $ia    = ! empty($r['interpretacao']) ? json_decode($r['interpretacao'], true) : null;
            $rowId = 'ia-' . $r['id'];
            ?>
            <tr class="regra-row" data-id="<?= $r['id'] ?>">
                <td>
                    <span class="regra-tipo-badge"
                          style="background:<?= $ti['bg'] ?>;color:<?= $ti['cor'] ?>;">
                        <i class="bi <?= $ti['icone'] ?>"></i>
                        <?= $ti['label'] ?>
                    </span>
                </td>
                <td>
                    <?php if ($srv): ?>
                    <a href="<?= base_url('inteligencia/servico/' . urlencode($srv)) ?>"
                       class="servico-chip"><?= esc($srv) ?></a>
                    <?php else: ?>
                    <span class="text-muted" style="font-size:.72rem;">geral</span>
                    <?php endif; ?>
                </td>
                <td>
                    <!-- Descrição bruta -->
                    <div style="font-weight:600;color:#1e293b;line-height:1.4;">
                        <?= esc(mb_substr($r['descricao'], 0, 180)) ?>
                    </div>
                    <?php if ($ia): ?>
                    <!-- Resumo executivo da IA -->
                    <div class="ia-resumo">
                        <i class="bi bi-stars" style="color:#F5A800;"></i>
                        <?= esc($ia['resumo_executivo'] ?? '') ?>
                    </div>
                    <!-- Painel expandido (oculto) -->
                    <div id="<?= $rowId ?>" class="ia-panel" style="display:none;">
                        <?php if (! empty($ia['o_que_e'])): ?>
                        <div class="ia-field">
                            <span class="ia-label">O que é</span>
                            <?= esc($ia['o_que_e']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($ia['quando_aplica'])): ?>
                        <div class="ia-field">
                            <span class="ia-label">Quando aplica</span>
                            <?= esc($ia['quando_aplica']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($ia['quem_se_aplica'])): ?>
                        <div class="ia-field">
                            <span class="ia-label">Para quem</span>
                            <?= esc($ia['quem_se_aplica']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($ia['impacto_comercial'])): ?>
                        <div class="ia-field ia-field-destaque">
                            <span class="ia-label">Impacto comercial</span>
                            <?= esc($ia['impacto_comercial']) ?>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($ia['condicoes'])): ?>
                        <div class="ia-field">
                            <span class="ia-label">Condições</span>
                            <ul style="margin:.25rem 0 0 1rem;padding:0;">
                            <?php foreach ($ia['condicoes'] as $c): ?>
                                <li><?= esc($c) ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($ia['restricoes'])): ?>
                        <div class="ia-field">
                            <span class="ia-label">Restrições</span>
                            <ul style="margin:.25rem 0 0 1rem;padding:0;">
                            <?php foreach ($ia['restricoes'] as $c): ?>
                                <li><?= esc($c) ?></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($ia['palavras_chave'])): ?>
                        <div class="ia-field">
                            <span class="ia-label">Tags</span>
                            <?php foreach ($ia['palavras_chave'] as $tag): ?>
                            <span class="ia-tag"><?= esc($tag) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php elseif (! empty($r['contexto'])): ?>
                    <div style="font-size:.73rem;color:#94a3b8;margin-top:.2rem;line-height:1.35;font-style:italic;">
                        <?= esc(mb_substr($r['contexto'], 0, 140)) ?>...
                    </div>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <?php if ($r['valor_numerico'] !== null): ?>
                    <span style="font-weight:700;color:<?= $ti['cor'] ?>;">
                        <?= number_format((float)$r['valor_numerico'], 0, ',', '.') ?>
                    </span>
                    <?php if ($r['unidade']): ?>
                    <span style="font-size:.68rem;color:#94a3b8;"><?= esc($r['unidade']) ?></span>
                    <?php endif; ?>
                    <?php endif; ?>
                </td>
                <td>
                    <span style="font-size:.72rem;color:#64748b;line-height:1.3;">
                        <?= esc($r['fonte']) ?>
                    </span>
                </td>
                <td style="white-space:nowrap;">
                    <?php if ($ia): ?>
                    <button class="btn btn-sm btn-ia toggle-ia" data-target="<?= $rowId ?>"
                            title="Ver interpretação IA" style="padding:.2rem .5rem;">
                        <i class="bi bi-stars"></i>
                    </button>
                    <?php endif; ?>
                    <a href="<?= base_url('manuais/' . $r['doc_tipo'] . '/' . $r['doc_id']) ?>"
                       target="_blank" title="Ver no MANCAT" class="btn btn-sm btn-outline-secondary" style="padding:.2rem .5rem;">
                        <i class="bi bi-box-arrow-up-right"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <?php if ($totalPages > 1): ?>
    <div class="d-flex align-items-center justify-content-between p-3 border-top"
         style="font-size:.8rem;">
        <span class="text-muted">
            Página <?= $pagina ?> de <?= $totalPages ?> —
            <?= number_format($total, 0, ',', '.') ?> regras
        </span>
        <div class="d-flex gap-1">
            <?php
            $baseUrl = base_url('inteligencia/regras?tipo=' . urlencode($tipoAtual)
                . '&servico=' . urlencode($servicoAtual)
                . '&q=' . urlencode($qAtual) . '&p=');
            $inicio  = max(1, $pagina - 2);
            $fim     = min($totalPages, $pagina + 2);
            ?>
            <?php if ($pagina > 1): ?>
            <a href="<?= $baseUrl . ($pagina - 1) ?>" class="btn btn-sm btn-outline-secondary">‹</a>
            <?php endif; ?>
            <?php for ($i = $inicio; $i <= $fim; $i++): ?>
            <a href="<?= $baseUrl . $i ?>"
               class="btn btn-sm <?= $i === $pagina ? 'btn-primary' : 'btn-outline-secondary' ?>">
                <?= $i ?>
            </a>
            <?php endfor; ?>
            <?php if ($pagina < $totalPages): ?>
            <a href="<?= $baseUrl . ($pagina + 1) ?>" class="btn btn-sm btn-outline-secondary">›</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.regra-tipo-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    border-radius: 20px; padding: .15rem .6rem;
    font-size: .68rem; font-weight: 700;
    white-space: nowrap;
}
.servico-chip {
    background: #f1f5f9; border: 1px solid #e2e8f0;
    border-radius: 6px; padding: .1rem .45rem;
    font-size: .72rem; font-weight: 700;
    color: #374151; text-decoration: none;
    white-space: nowrap;
}
.servico-chip:hover { background: var(--cor-primaria); color: white; border-color: var(--cor-primaria); }

/* ── Interpretação IA ────────────────────────────────── */
.ia-resumo {
    font-size: .78rem;
    color: #92400e;
    background: rgba(245,168,0,.08);
    border-left: 3px solid #F5A800;
    padding: .3rem .6rem;
    margin-top: .35rem;
    border-radius: 0 6px 6px 0;
    line-height: 1.45;
}
.ia-panel {
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 8px;
    padding: .75rem;
    margin-top: .5rem;
    font-size: .78rem;
    line-height: 1.5;
}
.ia-field {
    margin-bottom: .5rem;
    padding-bottom: .5rem;
    border-bottom: 1px solid #e0f2fe;
}
.ia-field:last-child { border-bottom: none; margin-bottom: 0; }
.ia-field-destaque { background: rgba(245,168,0,.07); border-radius: 6px; padding: .4rem .6rem; }
.ia-label {
    display: block;
    font-size: .65rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .07em;
    color: #0284c7;
    margin-bottom: .15rem;
}
.ia-tag {
    display: inline-block;
    background: #e0f2fe;
    color: #0369a1;
    border-radius: 10px;
    padding: .05rem .45rem;
    font-size: .68rem;
    font-weight: 600;
    margin: .1rem;
}
.btn-ia {
    background: rgba(245,168,0,.12);
    color: #92400e;
    border: 1px solid rgba(245,168,0,.3);
}
.btn-ia:hover, .btn-ia.active {
    background: #F5A800;
    color: white;
    border-color: #F5A800;
}
</style>
<script>
document.querySelectorAll('.toggle-ia').forEach(btn => {
    btn.addEventListener('click', () => {
        const panel = document.getElementById(btn.dataset.target);
        const visible = panel.style.display !== 'none';
        panel.style.display = visible ? 'none' : 'block';
        btn.classList.toggle('active', !visible);
    });
});
</script>
<?= $this->endSection() ?>
