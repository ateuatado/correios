<nav class="topbar">
    <!-- Toggler mobile -->
    <button id="sidebarToggler" class="btn btn-sm me-3 d-lg-none"
            style="background:rgba(255,255,255,.15);border:none;color:#fff;"
            type="button" aria-label="Abrir menu">
        <i class="bi bi-list" style="font-size:1.3rem;"></i>
    </button>

    <!-- Brand -->
    <a href="<?= base_url('/') ?>" class="topbar-brand">
        <i class="bi bi-envelope-fill" style="font-size:1.4rem;color:var(--cor-secundaria);"></i>
        <span>Correios<span class="brand-accent">Comercial</span></span>
    </a>

    <!-- Links de topo -->
    <div class="topbar-nav ms-auto">
        <a href="<?= base_url('manuais') ?>" class="nav-link <?= (current_url(true)->getPath() === '/manuais') ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark me-1"></i> Manuais
        </a>
    </div>
</nav>
