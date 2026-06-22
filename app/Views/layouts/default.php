<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CorreiosComercial — Sistema de gestão documental do MANCAT">
    <title><?= esc($title ?? 'Início') ?> | CorreiosComercial</title>

    <!-- Bootstrap 5 -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <!-- App CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>
<body>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- TOPBAR                                                      -->
<!-- ═══════════════════════════════════════════════════════════ -->
<?= view('partials/_navbar') ?>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- OVERLAY (mobile sidebar)                                    -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div id="sidebarOverlay" class="d-none position-fixed top-0 start-0 w-100 h-100" style="background:rgba(0,0,0,.5);z-index:1029;"></div>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- SIDEBAR                                                     -->
<!-- ═══════════════════════════════════════════════════════════ -->
<nav id="mainSidebar" class="sidebar">
    <div class="sidebar-header">Navegação</div>
    <ul class="tree">
        <li class="tree-item">
            <a href="<?= base_url('/') ?>" class="tree-link">
                <i class="bi bi-house-door"></i>
                <span>Início</span>
            </a>
        </li>
        <li class="tree-item">
            <a href="<?= base_url('manuais') ?>" class="tree-link">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Manuais</span>
            </a>
        </li>
    </ul>

    <?php if (isset($sidebar_manual)): ?>
    <div class="sidebar-divider"></div>
    <div class="sidebar-header"><?= esc($sidebar_manual['codigo']) ?></div>
    <ul class="tree">
        <?php foreach ($sidebar_manual['modulos'] as $mi => $mod): ?>
        <li class="tree-item">
            <span class="tree-link" aria-expanded="false"
                  data-target="sidebar-mod-<?= $mod['id'] ?>">
                <i class="bi bi-folder2"></i>
                <span class="text-truncate">Módulo <?= $mod['numero'] ?></span>
                <i class="bi bi-chevron-right toggle-icon"></i>
            </span>
            <ul class="tree-children d-none" id="sidebar-mod-<?= $mod['id'] ?>">
                <?php foreach ($mod['capitulos'] as $cap): ?>
                <li class="tree-item">
                    <a href="<?= base_url("manuais/capitulo/{$cap['id']}") ?>" class="tree-link">
                        <i class="bi bi-file-text"></i>
                        <span class="text-truncate">Cap. <?= $cap['numero'] ?></span>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</nav>

<!-- ═══════════════════════════════════════════════════════════ -->
<!-- CONTEÚDO PRINCIPAL                                          -->
<!-- ═══════════════════════════════════════════════════════════ -->
<div class="main-wrapper">

    <!-- Breadcrumb -->
    <?php if (isset($breadcrumbs) && count($breadcrumbs) > 0): ?>
    <div class="breadcrumb-bar">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= base_url('/') ?>"><i class="bi bi-house-door"></i></a>
                </li>
                <?php foreach ($breadcrumbs as $bc): ?>
                    <?php if (! empty($bc['url'])): ?>
                    <li class="breadcrumb-item">
                        <a href="<?= esc($bc['url']) ?>"><?= esc($bc['label']) ?></a>
                    </li>
                    <?php else: ?>
                    <li class="breadcrumb-item active" aria-current="page">
                        <?= esc($bc['label']) ?>
                    </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ol>
        </nav>
    </div>
    <?php endif; ?>

    <main class="main-content">
        <?= $this->renderSection('content') ?>
    </main>

    <footer class="text-center py-3 border-top" style="font-size:.75rem;color:#aaa;">
        CorreiosComercial &mdash; Sistema de Gestão Documental &copy; <?= date('Y') ?>
    </footer>
</div>

<!-- Bootstrap JS -->
<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<!-- App JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

<?= $this->renderSection('scripts') ?>

</body>
</html>
