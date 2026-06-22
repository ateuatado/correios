<?= $this->extend('layouts/default') ?>
<?php
$breadcrumbs = [
    ['label' => 'Eixos', 'url' => ''],
];
?>

<?= $this->section('content') ?>

<div class="page-header d-flex align-items-center justify-content-between">
    <div>
        <h1 style="font-size:1.4rem;font-weight:800;">
            <i class="bi bi-grid-1x2-fill" style="color:var(--cor-primaria);"></i>
            Gerenciar Eixos
        </h1>
        <p class="subtitle">Os pilares estratégicos da área comercial.</p>
    </div>
    <a href="<?= base_url('eixos/novo') ?>" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Novo Eixo
    </a>
</div>

<?php if (session()->has('sucesso')): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill"></i> <?= session('sucesso') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<?php if (session()->has('erro')): ?>
<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <i class="bi bi-exclamation-triangle-fill"></i> <?= session('erro') ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="tree-container">
    <?php if (empty($eixos)): ?>
    <p class="text-center text-muted py-4">Nenhum eixo cadastrado ainda.</p>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background:#f8fafc;font-size:.78rem;text-transform:uppercase;letter-spacing:.06em;color:#64748b;">
                <tr>
                    <th style="width:40px;">#</th>
                    <th>Eixo</th>
                    <th style="width:80px;">Ordem</th>
                    <th style="width:80px;">Ativo</th>
                    <th style="width:120px;">Ações</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($eixos as $e): ?>
            <tr>
                <td><span style="font-size:.75rem;color:#94a3b8;"><?= $e['id'] ?></span></td>
                <td>
                    <div class="d-flex align-items-center gap-2">
                        <div style="width:34px;height:34px;border-radius:8px;background:<?= esc($e['cor_bg']) ?>;display:flex;align-items:center;justify-content:center;font-size:1rem;color:<?= esc($e['cor']) ?>;">
                            <i class="bi <?= esc($e['icone']) ?>"></i>
                        </div>
                        <div>
                            <div style="font-weight:600;font-size:.88rem;"><?= esc($e['nome']) ?></div>
                            <div style="font-size:.74rem;color:#94a3b8;"><?= esc($e['slug']) ?></div>
                        </div>
                    </div>
                </td>
                <td><span class="badge bg-secondary"><?= $e['ordem'] ?></span></td>
                <td>
                    <?php if ($e['ativo']): ?>
                    <span class="badge" style="background:#dcfce7;color:#166534;">Sim</span>
                    <?php else: ?>
                    <span class="badge" style="background:#fee2e2;color:#991b1b;">Não</span>
                    <?php endif; ?>
                </td>
                <td>
                    <a href="<?= base_url("eixos/editar/{$e['id']}") ?>" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form method="post" action="<?= base_url("eixos/deletar/{$e['id']}") ?>" class="d-inline"
                          onsubmit="return confirm('Excluir o eixo «<?= esc($e['nome']) ?>»? Esta ação não pode ser desfeita.')">
                        <?= csrf_field() ?>
                        <button class="btn btn-sm btn-outline-danger" title="Excluir">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
