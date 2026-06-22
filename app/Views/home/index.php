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
        <a href="<?= base_url('eixos') ?>" class="topnav-link">
            <i class="bi bi-grid-1x2"></i> Eixos
        </a>
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
        <div class="hs-brand">
            <div class="hs-logo"><i class="bi bi-envelope-fill"></i></div>
            <div>
                <div class="hs-name">CorreiosComercial</div>
                <div class="hs-sub">Inteligência Comercial</div>
            </div>
        </div>

        <nav class="hs-nav">
            <div class="hs-nav-section">NAVEGAÇÃO</div>
            <a href="<?= base_url('/') ?>" class="hs-nav-link active">
                <i class="bi bi-house-door-fill"></i><span>Início</span>
            </a>
            <a href="<?= base_url('manuais') ?>" class="hs-nav-link">
                <i class="bi bi-journal-bookmark-fill"></i><span>Manuais</span>
            </a>
            <a href="<?= base_url('manuais/buscar') ?>" class="hs-nav-link">
                <i class="bi bi-search"></i><span>Pesquisar</span>
            </a>
            <a href="<?= base_url('eixos') ?>" class="hs-nav-link">
                <i class="bi bi-grid-1x2"></i><span>Gerenciar Eixos</span>
            </a>
        </nav>

        <div class="hs-pillars-nav">
            <div class="hs-nav-section">PILARES COMERCIAIS</div>
            <?php foreach ($eixos as $e): ?>
            <a href="#pilar-<?= esc($e['slug']) ?>" class="hs-pilar-link">
                <i class="bi <?= esc($e['icone']) ?>" style="color:<?= esc($e['cor']) ?>"></i>
                <span><?= esc($e['nome']) ?></span>
                <?php if ($e['total_ideias'] > 0): ?>
                <span class="hs-idea-count"><?= $e['total_ideias'] ?></span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>

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
                    estruturados a partir do MANCAT. Registre ideias, hipóteses e
                    estratégias — em breve, com inteligência artificial.
                </p>
                <div class="hero-actions">
                    <a href="<?= base_url('manuais/arvore/1') ?>" class="hero-btn hero-btn-primary">
                        <i class="bi bi-diagram-3-fill"></i> Ver estrutura do MANCAT
                    </a>
                    <a href="<?= base_url('manuais/buscar') ?>" class="hero-btn hero-btn-secondary">
                        <i class="bi bi-search"></i> Pesquisar conteúdo
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-ring hero-ring-1"></div>
                <div class="hero-ring hero-ring-2"></div>
                <div class="hero-ring hero-ring-3"></div>
                <div class="hero-icon-center"><i class="bi bi-envelope-paper-fill"></i></div>
                <?php $angulos = [0,45,90,135,180,225,270,315]; ?>
                <?php foreach ($eixos as $hi => $he): ?>
                <div class="hero-orbit-icon" style="--angle:<?= $angulos[$hi % 8] ?>deg">
                    <i class="bi <?= esc($he['icone']) ?>"></i>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Stats -->
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
            <?php $totalIdeias = array_sum(array_column($eixos, 'total_ideias')); ?>
            <div class="stat-pill" style="border-color:rgba(245,168,0,.3);background:rgba(245,168,0,.05);">
                <i class="bi bi-lightbulb-fill" style="color:#F5A800"></i>
                <strong><?= $totalIdeias ?></strong> <?= $totalIdeias === 1 ? 'ideia' : 'ideias' ?>
            </div>
            <div class="stat-pill stat-pill-ai">
                <i class="bi bi-stars" style="color:#f59e0b"></i>
                <strong>IA</strong> em breve
            </div>
        </section>

        <!-- Grid dos pilares -->
        <section class="pilares-section">
            <div class="pilares-header">
                <h2 class="pilares-title">
                    <i class="bi bi-grid-1x2-fill"></i> Os <?= count($eixos) ?> Pilares Comerciais
                </h2>
                <p class="pilares-sub">Registre ideias em cada pilar e explore o conteúdo do MANCAT relacionado.</p>
            </div>

            <div class="pilares-grid">
                <?php foreach ($eixos as $idx => $p): ?>
                <article class="pilar-card" id="pilar-<?= esc($p['slug']) ?>"
                         style="--card-cor:<?= esc($p['cor']) ?>;--card-bg:<?= esc($p['cor_bg']) ?>">

                    <div class="card-ordem"><?= str_pad($p['ordem'], 2, '0', STR_PAD_LEFT) ?></div>

                    <div class="card-icone">
                        <i class="bi <?= esc($p['icone']) ?>"></i>
                    </div>

                    <div class="card-body-custom">
                        <h3 class="card-titulo"><?= esc($p['nome']) ?></h3>
                        <p class="card-resumo"><?= esc($p['descricao']) ?></p>
                    </div>

                    <!-- Tags do eixo -->
                    <?php if (! empty($p['tags_array'])): ?>
                    <div class="card-tags">
                        <?php foreach ($p['tags_array'] as $tag): ?>
                        <span class="card-tag"><?= esc($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <!-- ── Seção de Ideias ───────────────────────── -->
                    <div class="card-ideas-section">
                        <div class="card-ideas-header">
                            <span class="card-ideas-title">
                                <i class="bi bi-lightbulb-fill"></i>
                                Ideias
                                <?php if (! empty($p['ideias'])): ?>
                                <span class="ideas-count"><?= count($p['ideias']) ?></span>
                                <?php endif; ?>
                            </span>
                            <a href="<?= base_url("ideias/nova/{$p['id']}") ?>"
                               class="btn-nova-ideia" title="Nova ideia neste eixo">
                                <i class="bi bi-plus-lg"></i> Nova ideia
                            </a>
                        </div>

                        <?php if (! empty($p['ideias'])): ?>
                        <ul class="ideas-list">
                            <?php foreach ($p['ideias'] as $ideia): ?>
                            <?php $si = $statusInfo[$ideia['status']] ?? $statusInfo['rascunho']; ?>
                            <li class="idea-item">
                                <a href="<?= base_url("ideias/{$ideia['id']}") ?>" class="idea-link">
                                    <span class="idea-status-dot" style="background:<?= $si['cor'] ?>;" title="<?= $si['label'] ?>"></span>
                                    <span class="idea-titulo"><?= esc($ideia['titulo']) ?></span>
                                    <span class="idea-status-label" style="color:<?= $si['cor'] ?>;background:<?= $si['bg'] ?>;">
                                        <?= $si['label'] ?>
                                    </span>
                                    <i class="bi bi-chevron-right idea-arrow"></i>
                                </a>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php else: ?>
                        <p class="ideas-empty">
                            <i class="bi bi-lightbulb"></i>
                            Nenhuma ideia ainda. Comece registrando a primeira.
                        </p>
                        <?php endif; ?>
                    </div>

                    <!-- Footer -->
                    <div class="card-footer-custom">
                        <a href="<?= base_url('manuais/buscar?q=' . urlencode($p['nome'])) ?>"
                           class="card-action">
                            <i class="bi bi-search"></i> Buscar no MANCAT
                        </a>
                        <a href="<?= base_url('eixos/editar/' . $p['id']) ?>"
                           class="card-action" style="font-size:.72rem;color:#94a3b8;">
                            <i class="bi bi-pencil"></i>
                        </a>
                    </div>

                    <div class="card-accent-bar"></div>
                </article>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- CTA -->
        <section class="home-cta">
            <div class="cta-inner">
                <div class="cta-icon"><i class="bi bi-stars"></i></div>
                <div class="cta-text">
                    <h3>Inteligência Artificial chegando em breve</h3>
                    <p>Faça perguntas em linguagem natural e a IA responderá com base nos documentos do MANCAT e nas suas ideias registradas.</p>
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
</div>

<script src="<?= base_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
<script>
// Smooth scroll sidebar → card
document.querySelectorAll('.hs-pilar-link[href^="#"]').forEach(link => {
    link.addEventListener('click', e => {
        e.preventDefault();
        const el = document.querySelector(link.getAttribute('href'));
        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

// Scroll spy
const cards = document.querySelectorAll('.pilar-card[id]');
const pilarLinks = document.querySelectorAll('.hs-pilar-link');
const obs = new IntersectionObserver(entries => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const id = entry.target.id;
            pilarLinks.forEach(l => l.classList.toggle('active',
                l.getAttribute('href') === '#' + id));
        }
    });
}, { threshold: 0.3 });
cards.forEach(c => obs.observe(c));
</script>
</body>
</html>
