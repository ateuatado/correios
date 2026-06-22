<?= $this->extend('layouts/default') ?>
<?php
$breadcrumbs = [
    ['label' => 'Inteligência', 'url' => base_url('inteligencia')],
    ['label' => 'Comparativo de Serviços', 'url' => ''],
];
?>

<?= $this->section('content') ?>

<div class="page-header">
    <h1 style="font-size:1.3rem;font-weight:800;">
        <i class="bi bi-arrow-left-right" style="color:var(--cor-primaria);"></i>
        Comparativo de Serviços
    </h1>
    <p class="subtitle">Selecione 2 ou mais serviços para comparar seus limites, prazos e restrições lado a lado.</p>
</div>

<!-- Seletor de serviços -->
<form method="get" action="<?= base_url('inteligencia/comparar') ?>" class="tree-container mb-4">
    <div class="fw-bold mb-2" style="font-size:.85rem;">Selecione os serviços a comparar:</div>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <?php foreach ($todosServicos as $s): ?>
        <?php $selecionado = in_array($s['servico'], $servicosSel); ?>
        <label class="servico-check <?= $selecionado ? 'selecionado' : '' ?>">
            <input type="checkbox" name="s[]" value="<?= esc($s['servico']) ?>"
                   <?= $selecionado ? 'checked' : '' ?> class="d-none servico-cb">
            <?= esc($s['servico']) ?>
        </label>
        <?php endforeach; ?>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">
        <i class="bi bi-arrow-left-right"></i> Comparar selecionados
    </button>
    <?php if (! empty($servicosSel)): ?>
    <a href="<?= base_url('inteligencia/comparar') ?>" class="btn btn-outline-secondary btn-sm ms-2">
        Limpar
    </a>
    <?php endif; ?>
</form>

<!-- Tabela comparativa -->
<?php if (! empty($resultado) && ! empty($servicosSel)): ?>
<div class="tree-container p-0 overflow-auto">
    <table class="table mb-0 align-middle comparar-table">
        <thead>
            <tr style="background:#f8fafc;">
                <th style="min-width:160px;font-size:.78rem;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">
                    Dimensão
                </th>
                <?php foreach ($servicosSel as $srv): ?>
                <th style="min-width:220px;text-align:left;">
                    <div class="srv-header"><?= esc($srv === '_geral_' ? 'Geral' : $srv) ?></div>
                </th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($resultado as $linha): ?>
        <?php
        $tipo = $linha['tipo'];
        $ti   = $tipoInfo[$tipo] ?? $tipoInfo['outro'];
        // Verifica se há alguma dado nesta linha
        $temDados = false;
        foreach ($linha['servicos'] as $rows) {
            if (! empty($rows)) { $temDados = true; break; }
        }
        if (! $temDados) continue;
        ?>
        <tr>
            <td style="background:#f8fafc;vertical-align:top;">
                <span class="regra-tipo-badge"
                      style="background:<?= $ti['bg'] ?>;color:<?= $ti['cor'] ?>;">
                    <i class="bi <?= $ti['icone'] ?>"></i>
                    <?= $ti['label'] ?>
                </span>
            </td>
            <?php foreach ($servicosSel as $srv): ?>
            <?php $rows = $linha['servicos'][$srv] ?? []; ?>
            <td style="vertical-align:top;border-left:2px solid #f1f5f9;">
                <?php if (empty($rows)): ?>
                <span style="color:#cbd5e1;font-size:.78rem;">— sem dados —</span>
                <?php else: ?>
                <?php foreach ($rows as $row): ?>
                <div class="comparar-item">
                    <div style="font-size:.8rem;color:#1e293b;font-weight:600;line-height:1.4;">
                        <?php if ($row['valor_numerico'] !== null): ?>
                        <span class="comparar-valor"><?= number_format((float)$row['valor_numerico'], 0, ',', '.') ?></span>
                        <?php if (! empty($row['unidade'])): ?>
                        <span style="font-size:.72rem;color:#94a3b8;"> <?= esc($row['unidade']) ?></span>
                        <?php endif; ?>
                        <span style="color:#94a3b8;font-size:.73rem;font-weight:400;"> — </span>
                        <?php endif; ?>
                        <?= esc(mb_substr($row['descricao'], 0, 120)) ?>
                    </div>
                    <div style="font-size:.7rem;color:#94a3b8;margin-top:.15rem;">
                        <i class="bi bi-geo-alt"></i>
                        <?= esc($row['fonte']) ?>
                        <a href="<?= base_url('manuais/' . ($row['doc_tipo'] ?? 'capitulo') . '/' . ($row['doc_id'] ?? '')) ?>"
                           target="_blank" class="ms-1" style="color:var(--cor-primaria);">
                            <i class="bi bi-box-arrow-up-right" style="font-size:.65rem;"></i>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php elseif (! empty($servicosSel)): ?>
<div class="alert alert-warning">Nenhuma regra encontrada para os serviços selecionados.</div>
<?php else: ?>
<div class="text-center text-muted py-5">
    <i class="bi bi-arrow-left-right fs-1 d-block mb-3" style="color:#cbd5e1;"></i>
    Selecione pelo menos 2 serviços acima para ver a comparação.
</div>
<?php endif; ?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.servico-check {
    display: inline-flex; align-items: center;
    background: #f8fafc; border: 2px solid #e2e8f0;
    border-radius: 8px; padding: .35rem .85rem;
    font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .15s;
    color: #374151;
}
.servico-check:hover { border-color: var(--cor-primaria); color: var(--cor-primaria); }
.servico-check.selecionado {
    background: var(--cor-primaria);
    border-color: var(--cor-primaria);
    color: white;
}
.servico-cb:checked + label, .selecionado { background: var(--cor-primaria); }

.comparar-table th, .comparar-table td { padding: .75rem 1rem; }
.srv-header {
    font-size: .9rem; font-weight: 800;
    color: #0f172a;
}
.comparar-item {
    padding: .4rem 0;
    border-bottom: 1px solid #f1f5f9;
}
.comparar-item:last-child { border-bottom: none; }
.comparar-valor {
    font-size: 1rem;
    font-weight: 900;
    color: var(--cor-primaria);
}
.regra-tipo-badge {
    display: inline-flex; align-items: center; gap: .3rem;
    border-radius: 20px; padding: .2rem .7rem;
    font-size: .72rem; font-weight: 700;
    white-space: nowrap;
}
</style>
<script>
// Toggle visual do checkbox
document.querySelectorAll('.servico-cb').forEach(cb => {
    cb.addEventListener('change', () => {
        cb.closest('.servico-check').classList.toggle('selecionado', cb.checked);
    });
});
</script>
<?= $this->endSection() ?>
