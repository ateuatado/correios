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
        <a href="<?= base_url('manuais') ?>" class="nav-link <?= str_starts_with(uri_string(), 'manuais') ? 'active' : '' ?>">
            <i class="bi bi-journal-bookmark me-1"></i> Manuais
        </a>
        <a href="<?= base_url('inteligencia') ?>" class="nav-link <?= str_starts_with(uri_string(), 'inteligencia') ? 'active' : '' ?>">
            <i class="bi bi-bar-chart-steps me-1"></i> Inteligência
        </a>
        <a href="<?= base_url('assistente') ?>" class="nav-link <?= str_starts_with(uri_string(), 'assistente') ? 'active' : '' ?>" style="position:relative;">
            <i class="bi bi-stars me-1" style="color:var(--cor-secundaria);"></i> Assistente IA
            <span style="position:absolute;top:-5px;right:-8px;background:#F5A800;color:white;font-size:.55rem;font-weight:800;padding:.1rem .3rem;border-radius:6px;line-height:1.4;">NOVO</span>
        </a>
    </div>
</nav>
