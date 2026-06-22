<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="CorreiosComercial — Plataforma de inteligência comercial dos Correios">
    <title><?= esc($title ?? 'CorreiosComercial') ?></title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap-icons.min.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/home.css') ?>">
</head>
<body class="home-body">

<!-- ══════════════════════════════════════════════════════════════
     TOPBAR
════════════════════════════════════════════════════════════════ -->
<header class="home-topbar">
    <a href="<?= base_url('/') ?>" class="home-brand">
        <span class="brand-cc">Correios</span><span class="brand-com">Comercial</span>
    </a>
    <nav class="home-topnav">
        <a href="<?= base_url('manuais/buscar') ?>" class="topnav-link">
            <i class="bi bi-search"></i> Pesquisar
        </a>
        <a href="<?= base_url('manuais') ?>" class="topnav-link">
            <i class="bi bi-journals"></i> Manuais
        </a>
    </nav>
</header>

<!-- ══════════════════════════════════════════════════════════════
     LAYOUT PRINCIPAL
════════════════════════════════════════════════════════════════ -->
<div class="home-layout">

    <!-- ── SIDEBAR ESQUERDA ──────────────────────────────────────── -->
    <aside class="home-sidebar">

        <!-- Avatar / título do sistema -->
        <div class="hs-brand">
            <div class="hs-logo">
                <i class="bi bi-envelope-fill"></i>
            </div>
            <div>
                <div class="hs-name">CorreiosComercial</div>
                <div class="hs-sub">Inteligência Comercial</div>
            </div>
        </div>

        <!-- Navegação principal -->
        <nav class="hs-nav">
            <div class="hs-nav-section">NAVEGAÇÃO</div>
            <a href="<?= base_url('/') ?>" class="hs-nav-link active">
                <i class="bi bi-house-door-fill"></i>
                <span>Início</span>
            </a>
            <a href="<?= base_url('manuais') ?>" class="hs-nav-link">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Manuais</span>
            </a>
            <a href="<?= base_url('manuais/buscar') ?>" class="hs-nav-link">
                <i class="bi bi-search"></i>
                <span>Pesquisar</span>
            </a>
        </nav>

        <!-- Pilares — lista rápida -->
        <div class="hs-pillars-nav">
            <div class="hs-nav-section">PILARES COMERCIAIS</div>
            <?php foreach ($pilares as $p): ?>
            <a href="#pilar-<?= $p['id'] ?>" class="hs-pilar-link">
                <i class="bi <?= $p['icone'] ?>" style="color:<?= $p['cor'] ?>"></i>
                <span><?= esc($p['titulo']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- Rodapé sidebar -->
        <div class="hs-footer">
            <div class="hs-footer-item">
                <i class="bi bi-database-fill"></i>
                <span>MANCAT · 8.910 itens</span>
            </div>
            <div class="hs-footer-item">
                <i class="bi bi-stars"></i>
                <span>IA em desenvolvimento</span>
            </div>
        </div>
    </aside>

    <!-- ── CONTEÚDO PRINCIPAL ────────────────────────────────────── -->
    <main class="home-main">

        <!-- Hero -->
        <section class="home-hero">
            <div class="hero-content">
                <div class="hero-eyebrow">
                    <span class="hero-badge"><i class="bi bi-lightning-charge-fill"></i> Plataforma de Inteligência Comercial</span>
                </div>
                <h1 class="hero-title">
                    Área Comercial<br>
                    <span class="hero-accent">dos Correios</span>
                </h1>
                <p class="hero-desc">
                    Explore os oito pilares do conhecimento comercial dos Correios,
                    estruturados a partir do Manual de Atendimento Comercial (MANCAT).
                    Em breve, responda perguntas com inteligência artificial.
                </p>
                <div class="hero-actions">
                    <a href="<?= base_url('manuais/arvore/1') ?>" class="hero-btn hero-btn-primary">
                        <i class="bi bi-diagram-3-fill"></i>
                        Ver estrutura do MANCAT
                    </a>
                    <a href="<?= base_url('manuais/buscar') ?>" class="hero-btn hero-btn-secondary">
                        <i class="bi bi-search"></i>
                        Pesquisar conteúdo
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-ring hero-ring-1"></div>
                <div class="hero-ring hero-ring-2"></div>
                <div class="hero-ring hero-ring-3"></div>
                <div class="hero-icon-center">
                    <i class="bi bi-envelope-paper-fill"></i>
                </div>
                <!-- Ícones orbitando -->
                <div class="hero-orbit-icon" style="--angle:0deg"><i class="bi bi-truck"></i></div>
                <div class="hero-orbit-icon" style="--angle:45deg"><i class="bi bi-globe-americas"></i></div>
                <div class="hero-orbit-icon" style="--angle:90deg"><i class="bi bi-calculator"></i></div>
                <div class="hero-orbit-icon" style="--angle:135deg"><i class="bi bi-shield-check"></i></div>
                <div class="hero-orbit-icon" style="--angle:180deg"><i class="bi bi-bank"></i></div>
                <div class="hero-orbit-icon" style="--angle:225deg"><i class="bi bi-cpu"></i></div>
                <div class="hero-orbit-icon" style="--angle:270deg"><i class="bi bi-file-earmark-ruled"></i></div>
                <div class="hero-orbit-icon" style="--angle:315deg"><i class="bi bi-bullseye"></i></div>
            </div>
        </section>

        <!-- Contador rápido -->
        <section class="home-stats">
            <div class="stat-pill">
                <i class="bi bi-folder2-open" style="color:#003F88"></i>
                <strong>24</strong> módulos
            </div>
            <div class="stat-pill">
                <i class="bi bi-file-text" style="color:#2e7d32"></i>
                <strong>106</strong> capítulos
            </div>
            <div class="stat-pill">
                <i class="bi bi-paperclip" style="color:#b45309"></i>
                <strong>309</strong> anexos
            </div>
            <div class="stat-pill">
                <i class="bi bi-card-list" style="color:#6d28d9"></i>
                <strong>8.910</strong> itens indexados
            </div>
            <div class="stat-pill stat-pill-ai">
                <i class="bi bi-stars" style="color:#f59e0b"></i>
                <strong>IA</strong> em breve
            </div>
        </section>

        <!-- Título da seção de cards -->
        <section class="pilares-section">
            <div class="pilares-header">
                <h2 class="pilares-title">
                    <i class="bi bi-grid-1x2-fill"></i>
                    Os 8 Pilares Comerciais
                </h2>
                <p class="pilares-sub">Selecione um pilar para explorar o conteúdo do MANCAT relacionado.</p>
            </div>

            <!-- Grid dos 8 cards -->
            <div class="pilares-grid">
                <?php foreach ($pilares as $idx => $p): ?>
                <article class="pilar-card" id="pilar-<?= $p['id'] ?>" style="--card-cor:<?= $p['cor'] ?>;--card-bg:<?= $p['cor_bg'] ?>">

                    <!-- Número de ordem -->
                    <div class="card-ordem"><?= str_pad($idx + 1, 2, '0', STR_PAD_LEFT) ?></div>

                    <!-- Ícone -->
                    <div class="card-icone">
                        <i class="bi <?= $p['icone'] ?>"></i>
                    </div>

                    <!-- Conteúdo -->
                    <div class="card-body-custom">
                        <h3 class="card-titulo"><?= esc($p['titulo']) ?></h3>
                        <p class="card-resumo"><?= esc($p['resumo']) ?></p>
                    </div>

                    <!-- Tags -->
                    <div class="card-tags">
                        <?php foreach ($p['tags'] as $tag): ?>
                        <span class="card-tag"><?= esc($tag) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Módulos relacionados -->
                    <div class="card-modulos">
                        <span class="card-modulos-label"><i class="bi bi-folder2"></i> MANCAT:</span>
                        <?php foreach ($p['modulos'] as $mod): ?>
                        <span class="card-mod-badge"><?= esc($mod) ?></span>
                        <?php endforeach; ?>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer-custom">
                        <a href="<?= base_url('manuais/buscar?q=' . urlencode($p['titulo'])) ?>" class="card-action">
                            <i class="bi bi-search"></i> Buscar no MANCAT
                        </a>
                        <?php if ($p['status'] === 'disponivel'): ?>
                        <span class="card-status status-ok"><i class="bi bi-check-circle-fill"></i> Disponível</span>
                        <?php else: ?>
                        <span class="card-status status-soon"><i class="bi bi-clock"></i> Em breve</span>
                        <?php endif; ?>
                    </div>

                    <!-- Barra decorativa lateral -->
                    <div class="card-accent-bar"></div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CTA inferior -->
        <section class="home-cta">
            <div class="cta-inner">
                <div class="cta-icon"><i class="bi bi-stars"></i></div>
                <div class="cta-text">
                    <h3>Inteligência Artificial chegando em breve</h3>
                    <p>Faça perguntas em linguagem natural e a IA responderá com base nos documentos do MANCAT.</p>
                </div>
                <a href="<?= base_url('manuais/buscar') ?>" class="cta-btn">
                    <i class="bi bi-search"></i> Começar a pesquisar
                </a>
            </div>
        </section>

        <footer class="home-footer">
            CorreiosComercial &mdash; Sistema de Gestão Documental &copy; <?= date('Y') ?>
        </footer>
    </main>
</div><!-- /home-layout -->

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script>
// Smooth scroll para links da sidebar
document.querySelectorAll('.hs-pilar-link[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const el = document.querySelector(link.getAttribute('href'));
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

// Highlight da sidebar ao scrollar
const cards = document.querySelectorAll('.pilar-card[id]');
const pilarLinks = document.querySelectorAll('.hs-pilar-link');

const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const id = entry.target.id;
            pilarLinks.forEach(l => l.classList.toggle('active', l.getAttribute('href') === '#' + id));
        }
    });
}, { threshold: 0.3 });

cards.forEach(c => obs.observe(c));
</script>
</body>
</html>
