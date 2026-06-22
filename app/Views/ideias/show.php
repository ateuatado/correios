<?= $this->extend('layouts/default') ?>
<?php
$breadcrumbs = [
    ['label' => 'Início',      'url' => base_url('/')],
    ['label' => $eixo['nome'], 'url' => base_url("/#pilar-{$eixo['slug']}")],
    ['label' => $ideia['titulo'], 'url' => ''],
];
$si       = $statusInfo[$ideia['status']] ?? $statusInfo['rascunho'];
$tagsArr  = array_filter(array_map('trim', explode(',', $ideia['tags'] ?? '')));
?>

<?= $this->section('content') ?>

<?php if (session()->has('sucesso')): ?>
<div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill"></i> <?= session('sucesso') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="row g-4">

    <!-- ── Coluna principal ──────────────────────────────────────── -->
    <div class="col-lg-8">

        <!-- Cabeçalho da ideia -->
        <div class="idea-show-header mb-4" style="--card-cor:<?= esc($eixo['cor']) ?>;--card-bg:<?= esc($eixo['cor_bg']) ?>">
            <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                <!-- Eixo badge -->
                <div class="eixo-badge" style="background:<?= esc($eixo['cor_bg']) ?>;color:<?= esc($eixo['cor']) ?>;">
                    <i class="bi <?= esc($eixo['icone']) ?>"></i>
                    <?= esc($eixo['nome']) ?>
                </div>
                <!-- Status badge -->
                <span class="status-badge" style="background:<?= $si['bg'] ?>;color:<?= $si['cor'] ?>;">
                    <?= $si['label'] ?>
                </span>
            </div>

            <h1 class="ideia-titulo"><?= esc($ideia['titulo']) ?></h1>

            <!-- Tags -->
            <?php if (! empty($tagsArr)): ?>
            <div class="d-flex flex-wrap gap-1 mt-2">
                <?php foreach ($tagsArr as $tag): ?>
                <span class="tag-chip"><?= esc($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Datas -->
            <div class="meta-datas mt-3">
                <span><i class="bi bi-calendar-plus"></i> Criada em <?= date('d/m/Y H:i', strtotime($ideia['criado_em'])) ?></span>
                <?php if ($ideia['atualizado_em'] !== $ideia['criado_em']): ?>
                <span><i class="bi bi-pencil-square"></i> Editada em <?= date('d/m/Y H:i', strtotime($ideia['atualizado_em'])) ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Campos estruturados -->
        <?php foreach ($campos as $campo): ?>
        <?php
        $tipo  = $campo['tipo'] ?? 'texto_longo';
        $valor = $campo['valor'] ?? '';
        if (trim($valor) === '' && trim($campo['label']) === '') continue;
        ?>
        <div class="campo-show mb-3">
            <div class="campo-show-label">
                <?php if ($tipo === 'referencia'): ?>
                <i class="bi bi-journal-bookmark" style="color:var(--cor-primaria);"></i>
                <?php elseif ($tipo === 'lista'): ?>
                <i class="bi bi-list-ul" style="color:#64748b;"></i>
                <?php elseif ($tipo === 'numero'): ?>
                <i class="bi bi-hash" style="color:#64748b;"></i>
                <?php elseif ($tipo === 'data'): ?>
                <i class="bi bi-calendar" style="color:#64748b;"></i>
                <?php else: ?>
                <i class="bi bi-card-text" style="color:#64748b;"></i>
                <?php endif; ?>
                <?= esc($campo['label']) ?>
            </div>

            <div class="campo-show-valor">
                <?php if (trim($valor) === ''): ?>
                <em style="color:#94a3b8;font-size:.85rem;">— não preenchido —</em>
                <?php elseif ($tipo === 'referencia'): ?>
                <div class="d-flex align-items-center gap-2">
                    <span><?= esc($valor) ?></span>
                    <a href="<?= base_url('manuais/buscar?q=' . urlencode($valor)) ?>"
                       target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:.75rem;">
                        <i class="bi bi-search"></i> Buscar no MANCAT
                    </a>
                </div>
                <?php elseif ($tipo === 'lista'): ?>
                <ul class="mb-0 ps-3">
                    <?php foreach (explode("\n", $valor) as $linha): ?>
                    <?php if (trim($linha) !== ''): ?>
                    <li><?= esc(trim($linha)) ?></li>
                    <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <?php elseif ($tipo === 'data'): ?>
                <?= date('d/m/Y', strtotime($valor)) ?>
                <?php else: ?>
                <?= nl2br(esc($valor)) ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Coluna lateral ────────────────────────────────────────── -->
    <div class="col-lg-4">

        <!-- Ações -->
        <div class="idea-card mb-3">
            <h6 class="fw-bold mb-3"><i class="bi bi-gear"></i> Ações</h6>
            <div class="d-grid gap-2">
                <a href="<?= base_url("ideias/{$ideia['id']}/editar") ?>"
                   class="btn btn-primary" style="background:<?= esc($eixo['cor']) ?>;border-color:<?= esc($eixo['cor']) ?>;">
                    <i class="bi bi-pencil"></i> Editar ideia
                </a>
                <a href="<?= base_url('manuais/buscar?q=' . urlencode($ideia['titulo'])) ?>"
                   target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-search"></i> Buscar no MANCAT
                </a>
                <form method="post" action="<?= base_url("ideias/{$ideia['id']}/deletar") ?>"
                      onsubmit="return confirm('Excluir a ideia «<?= esc($ideia['titulo']) ?>»? Esta ação não pode ser desfeita.')">
                    <?= csrf_field() ?>
                    <button class="btn btn-outline-danger w-100">
                        <i class="bi bi-trash"></i> Excluir ideia
                    </button>
                </form>
            </div>
        </div>

        <!-- Info do eixo -->
        <div class="idea-card" style="background:<?= esc($eixo['cor_bg']) ?>;border-color:<?= esc($eixo['cor']) ?>30;">
            <div class="d-flex align-items-center gap-2 mb-2">
                <div style="width:36px;height:36px;border-radius:8px;background:white;display:flex;align-items:center;justify-content:center;font-size:1rem;color:<?= esc($eixo['cor']) ?>;">
                    <i class="bi <?= esc($eixo['icone']) ?>"></i>
                </div>
                <span style="font-size:.82rem;font-weight:700;color:<?= esc($eixo['cor']) ?>;">
                    <?= esc($eixo['nome']) ?>
                </span>
            </div>
            <p style="font-size:.78rem;color:#475569;line-height:1.5;margin:0;">
                <?= esc($eixo['descricao']) ?>
            </p>
            <a href="<?= base_url('/') ?>#pilar-<?= esc($eixo['slug']) ?>" class="btn btn-sm mt-2 w-100"
               style="background:<?= esc($eixo['cor']) ?>;color:white;">
                <i class="bi bi-arrow-left"></i> Voltar ao eixo
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
.idea-show-header {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 1.5rem;
    border-left: 5px solid var(--card-cor);
}
.ideia-titulo {
    font-size: 1.6rem;
    font-weight: 800;
    color: #0f172a;
    line-height: 1.25;
    margin: 0;
}
.eixo-badge {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    border-radius: 20px;
    padding: .25rem .75rem;
    font-size: .75rem;
    font-weight: 700;
}
.status-badge {
    display: inline-flex;
    align-items: center;
    border-radius: 20px;
    padding: .25rem .75rem;
    font-size: .75rem;
    font-weight: 700;
}
.tag-chip {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 20px;
    padding: .18rem .6rem;
    font-size: .72rem;
    color: #475569;
}
.meta-datas {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
    font-size: .75rem;
    color: #94a3b8;
}
.meta-datas i { margin-right: .25rem; }
.campo-show {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
}
.campo-show-label {
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: .5rem 1rem;
    font-size: .75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: .05em;
    color: #64748b;
    display: flex;
    align-items: center;
    gap: .4rem;
}
.campo-show-valor {
    padding: 1rem;
    font-size: .9rem;
    color: #1e293b;
    line-height: 1.7;
}
.idea-card {
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 1.25rem;
}
</style>
<?= $this->endSection() ?>
