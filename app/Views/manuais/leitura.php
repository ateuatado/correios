<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<?php
// ── Resolve contexto para breadcrumb ──────────────────────────────
$isAnexo   = ($tipo === 'anexo');
$docTitulo = esc($doc['titulo'] ?? '');
$docNumero = esc($doc['numero'] ?? '');
$ctxManual = $contexto['manual']   ?? [];
$ctxModulo = $contexto['modulo']   ?? [];
$ctxCap    = $contexto['capitulo'] ?? [];
$ctxAnexo  = $contexto['anexo']    ?? null;
?>

<div class="leitura-layout">

    <!-- ══════════════════════════════════════════════════════════════
         SIDEBAR — Índice do documento
    ═══════════════════════════════════════════════════════════════ -->
    <aside class="leitura-sidebar" id="leitura-sidebar">
        <div class="sidebar-header">
            <span class="sidebar-badge"><?= $isAnexo ? 'ANEXO' : 'CAPÍTULO' ?></span>
            <h2 class="sidebar-title">
                <?= $isAnexo ? "Anexo {$docNumero}" : "Cap. {$docNumero}" ?>
            </h2>
            <p class="sidebar-subtitle"><?= mb_substr($docTitulo, 0, 60) ?><?= mb_strlen($docTitulo) > 60 ? '…' : '' ?></p>
        </div>

        <!-- Barra de pesquisa inline rápida -->
        <div class="sidebar-search">
            <div class="search-input-wrap">
                <i class="bi bi-search"></i>
                <input type="text" id="filtro-itens" placeholder="Filtrar nesta página…" autocomplete="off">
            </div>
        </div>

        <!-- Índice dinâmico -->
        <nav class="sidebar-nav" id="sidebar-nav">
            <?php if (empty($itens)): ?>
                <p class="sidebar-empty">Conteúdo sendo extraído…</p>
            <?php else: ?>
                <?= renderNavItens($itens) ?>
            <?php endif; ?>
        </nav>

        <?php if (! empty($anexos)): ?>
        <div class="sidebar-anexos">
            <div class="sidebar-section-title"><i class="bi bi-paperclip"></i> Anexos</div>
            <?php foreach ($anexos as $anx): ?>
                <a href="<?= base_url("manuais/anexo/{$anx['id']}") ?>" class="sidebar-anexo-link">
                    <span class="badge-anexo"><?= esc($anx['numero']) ?></span>
                    <span><?= esc(mb_substr($anx['titulo'], 0, 50)) ?><?= mb_strlen($anx['titulo']) > 50 ? '…' : '' ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Toggle mobile -->
    <button class="sidebar-toggle" id="sidebar-toggle" aria-label="Abrir índice">
        <i class="bi bi-list-ul"></i>
    </button>

    <!-- ══════════════════════════════════════════════════════════════
         ÁREA PRINCIPAL — Conteúdo do documento
    ═══════════════════════════════════════════════════════════════ -->
    <main class="leitura-main" id="leitura-main">

        <!-- Breadcrumb -->
        <nav class="doc-breadcrumb" aria-label="Breadcrumb">
            <a href="<?= base_url('manuais') ?>"><i class="bi bi-journals"></i> Manuais</a>
            <span class="bc-sep">/</span>
            <?php if (! empty($ctxManual['id'])): ?>
            <a href="<?= base_url("manuais/arvore/{$ctxManual['id']}") ?>"><?= esc($ctxManual['nome'] ?? 'MANCAT') ?></a>
            <span class="bc-sep">/</span>
            <?php endif; ?>
            <?php if (! empty($ctxModulo['id'])): ?>
            <a href="<?= base_url("manuais/modulo/{$ctxModulo['id']}") ?>">Módulo <?= esc($ctxModulo['numero']) ?></a>
            <span class="bc-sep">/</span>
            <?php endif; ?>
            <?php if ($isAnexo && ! empty($ctxCap['id'])): ?>
            <a href="<?= base_url("manuais/capitulo/{$ctxCap['id']}") ?>">Cap. <?= esc($ctxCap['numero']) ?></a>
            <span class="bc-sep">/</span>
            <span class="bc-active">Anexo <?= $docNumero ?></span>
            <?php else: ?>
            <span class="bc-active">Cap. <?= $docNumero ?></span>
            <?php endif; ?>
        </nav>

        <!-- Cabeçalho do documento -->
        <header class="doc-header">
            <div class="doc-type-badge <?= $isAnexo ? 'is-anexo' : 'is-capitulo' ?>">
                <?= $isAnexo ? '<i class="bi bi-paperclip"></i> ANEXO' : '<i class="bi bi-book"></i> CAPÍTULO' ?>
            </div>
            <h1 class="doc-title">
                <span class="doc-numero"><?= $docNumero ?>.</span>
                <?= $docTitulo ?>
            </h1>
            <div class="doc-meta">
                <?php if (! empty($ctxModulo['titulo'])): ?>
                <span><i class="bi bi-folder2"></i> Módulo <?= esc($ctxModulo['numero']) ?> — <?= esc($ctxModulo['titulo']) ?></span>
                <?php endif; ?>
                <?php if (! empty($itens)): ?>
                <span><i class="bi bi-card-list"></i> <?= count($itens) ?> seção<?= count($itens) !== 1 ? 'ões' : '' ?></span>
                <?php endif; ?>
                <?php if (! empty($doc['arquivo_nome'])): ?>
                <span><i class="bi bi-file-earmark-word"></i> <?= esc($doc['arquivo_nome']) ?></span>
                <?php endif; ?>
            </div>
        </header>

        <!-- Barra de ações rápidas -->
        <div class="doc-actions">
            <a href="<?= base_url('manuais/buscar?q=') . urlencode($docTitulo) ?>" class="btn-action">
                <i class="bi bi-search"></i> Buscar tema
            </a>
            <button class="btn-action" id="btn-expandir-tudo">
                <i class="bi bi-arrows-expand"></i> Expandir tudo
            </button>
            <button class="btn-action" id="btn-colapsar-tudo">
                <i class="bi bi-arrows-collapse"></i> Colapsar
            </button>
            <button class="btn-action" onclick="window.print()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
            <!-- Placeholder para AI — será ativado no futuro -->
            <button class="btn-action btn-ai" id="btn-ai" title="Pergunte à IA sobre este documento (em breve)">
                <i class="bi bi-stars"></i> Perguntar à IA
            </button>
        </div>

        <!-- Conteúdo dos itens -->
        <div class="doc-content" id="doc-content">
            <?php if (empty($itens)): ?>
                <div class="doc-empty">
                    <i class="bi bi-hourglass-split"></i>
                    <p>O conteúdo deste documento está sendo extraído.</p>
                    <p class="doc-empty-sub">Aguarde alguns minutos e recarregue a página.</p>
                </div>
            <?php else: ?>
                <?= renderItens($itens) ?>
            <?php endif; ?>
        </div>

        <!-- Footer do documento -->
        <footer class="doc-footer">
            <div class="doc-footer-nav">
                <?php if (! empty($ctxCap['id']) && $isAnexo): ?>
                <a href="<?= base_url("manuais/capitulo/{$ctxCap['id']}") ?>" class="footer-nav-btn">
                    <i class="bi bi-arrow-left-circle"></i> Voltar ao capítulo
                </a>
                <?php elseif (! empty($ctxModulo['id'])): ?>
                <a href="<?= base_url("manuais/modulo/{$ctxModulo['id']}") ?>" class="footer-nav-btn">
                    <i class="bi bi-arrow-left-circle"></i> Voltar ao módulo
                </a>
                <?php endif; ?>

                <?php if (! empty($ctxManual['id'])): ?>
                <a href="<?= base_url("manuais/arvore/{$ctxManual['id']}") ?>" class="footer-nav-btn">
                    <i class="bi bi-diagram-3"></i> Ver árvore completa
                </a>
                <?php endif; ?>
            </div>
        </footer>

    </main><!-- /leitura-main -->
</div><!-- /leitura-layout -->

<!-- ── Modal AI (placeholder para futuro) ─────────────────────── -->
<div class="ai-modal" id="ai-modal" aria-hidden="true">
    <div class="ai-modal-backdrop" id="ai-modal-backdrop"></div>
    <div class="ai-modal-box">
        <div class="ai-modal-header">
            <span class="ai-modal-title"><i class="bi bi-stars"></i> Perguntar à IA</span>
            <button class="ai-modal-close" id="ai-modal-close">✕</button>
        </div>
        <div class="ai-modal-body">
            <div class="ai-context-badge">
                Contexto: <strong><?= $isAnexo ? 'Anexo' : 'Cap.' ?> <?= $docNumero ?> — <?= mb_substr($docTitulo, 0, 40) ?><?= mb_strlen($docTitulo) > 40 ? '…' : '' ?></strong>
            </div>
            <textarea id="ai-input" placeholder="Ex: Quais são os requisitos para postagem de malote? O que é Agência Franqueada?" rows="3"></textarea>
            <div class="ai-footer">
                <span class="ai-coming-soon"><i class="bi bi-clock-history"></i> Recurso em desenvolvimento — em breve disponível!</span>
                <button class="btn-ai-send" disabled><i class="bi bi-send"></i> Enviar</button>
            </div>
        </div>
    </div>
</div>

<?php
// ── Funções de renderização ────────────────────────────────────────
function renderNavItens(array $itens, int $depth = 0): string
{
    if (empty($itens)) return '';
    $html = $depth === 0 ? '<ul class="nav-list">' : '<ul class="nav-sublist">';
    foreach ($itens as $item) {
        $anchorId = 'item-' . $item['id'];
        $html .= '<li class="nav-item nav-nivel-' . $item['nivel'] . '">';
        $html .= '<a href="#' . $anchorId . '" class="nav-link" data-titulo="' . esc(strtolower($item['titulo'])) . '">';
        $html .= '<span class="nav-num">' . esc($item['numero']) . '</span>';
        $html .= '<span class="nav-text">' . esc(mb_substr($item['titulo'], 0, 55)) . (mb_strlen($item['titulo']) > 55 ? '…' : '') . '</span>';
        $html .= '</a>';
        if (! empty($item['filhos'])) {
            $html .= renderNavItens($item['filhos'], $depth + 1);
        }
        $html .= '</li>';
    }
    $html .= '</ul>';
    return $html;
}

function renderItens(array $itens, int $depth = 0): string
{
    if (empty($itens)) return '';
    $html = '';
    foreach ($itens as $item) {
        $nivel    = (int) $item['nivel'];
        $anchorId = 'item-' . $item['id'];
        $hasFilhos = ! empty($item['filhos']);

        $html .= '<section class="item-secao nivel-' . $nivel . '" id="' . $anchorId . '">';

        // Cabeçalho do item
        $html .= '<div class="item-header' . ($hasFilhos ? ' has-filhos' : '') . '" data-target="filhos-' . $item['id'] . '">';
        if ($hasFilhos) {
            $html .= '<button class="item-toggle" aria-expanded="true" aria-controls="filhos-' . $item['id'] . '"><i class="bi bi-chevron-down"></i></button>';
        }
        $html .= '<span class="item-numero">' . esc($item['numero']) . '</span>';
        $html .= '<h' . min($nivel + 2, 6) . ' class="item-titulo">' . esc($item['titulo']) . '</h' . min($nivel + 2, 6) . '>';
        $html .= '</div>';

        // Conteúdo textual
        $conteudo = trim($item['conteudo'] ?? '');
        if ($conteudo !== '') {
            $html .= '<div class="item-conteudo">';
            foreach (explode("\n", $conteudo) as $paragrafo) {
                $p = trim($paragrafo);
                if ($p !== '') {
                    $html .= '<p>' . esc($p) . '</p>';
                }
            }
            $html .= '</div>';
        }

        // Filhos recursivos
        if ($hasFilhos) {
            $html .= '<div class="item-filhos" id="filhos-' . $item['id'] . '">';
            $html .= renderItens($item['filhos'], $depth + 1);
            $html .= '</div>';
        }

        $html .= '</section>';
    }
    return $html;
}
?>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ── Sidebar toggle mobile ─────────────────────────────────────
    const sidebar = document.getElementById('leitura-sidebar');
    const toggle  = document.getElementById('sidebar-toggle');
    if (toggle) {
        toggle.addEventListener('click', () => {
            sidebar.classList.toggle('sidebar-open');
        });
    }

    // ── Smooth scroll para links do índice ────────────────────────
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                // Fechar sidebar no mobile
                if (window.innerWidth < 992) sidebar.classList.remove('sidebar-open');
                // Destacar
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            }
        });
    });

    // ── Highlight do item ativo no scroll ─────────────────────────
    const sections = document.querySelectorAll('.item-secao[id]');
    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const id  = entry.target.getAttribute('id');
                const nav = document.querySelector(`.nav-link[href="#${id}"]`);
                document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
                if (nav) {
                    nav.classList.add('active');
                    nav.scrollIntoView({ block: 'nearest' });
                }
            }
        });
    }, { threshold: 0.15, rootMargin: '-60px 0px -60% 0px' });

    sections.forEach(s => observer.observe(s));

    // ── Collapse/Expand de itens ──────────────────────────────────
    document.querySelectorAll('.item-toggle').forEach(btn => {
        btn.addEventListener('click', () => {
            const expanded = btn.getAttribute('aria-expanded') === 'true';
            const target   = document.getElementById(btn.getAttribute('aria-controls'));
            if (target) {
                target.style.display = expanded ? 'none' : '';
                btn.setAttribute('aria-expanded', !expanded);
                btn.querySelector('i').className = expanded ? 'bi bi-chevron-right' : 'bi bi-chevron-down';
            }
        });
    });

    // Expandir / Colapsar tudo
    document.getElementById('btn-expandir-tudo')?.addEventListener('click', () => {
        document.querySelectorAll('.item-filhos').forEach(el => el.style.display = '');
        document.querySelectorAll('.item-toggle').forEach(btn => {
            btn.setAttribute('aria-expanded', 'true');
            btn.querySelector('i').className = 'bi bi-chevron-down';
        });
    });
    document.getElementById('btn-colapsar-tudo')?.addEventListener('click', () => {
        document.querySelectorAll('.item-filhos').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.item-toggle').forEach(btn => {
            btn.setAttribute('aria-expanded', 'false');
            btn.querySelector('i').className = 'bi bi-chevron-right';
        });
    });

    // ── Filtro inline de itens no sidebar ────────────────────────
    const filtro = document.getElementById('filtro-itens');
    if (filtro) {
        filtro.addEventListener('input', () => {
            const q = filtro.value.toLowerCase().trim();
            document.querySelectorAll('.nav-link').forEach(link => {
                const titulo = link.getAttribute('data-titulo') || '';
                const li     = link.closest('li');
                if (li) li.style.display = (q === '' || titulo.includes(q)) ? '' : 'none';
            });
        });
    }

    // ── Modal AI ─────────────────────────────────────────────────
    const btnAi      = document.getElementById('btn-ai');
    const aiModal    = document.getElementById('ai-modal');
    const aiClose    = document.getElementById('ai-modal-close');
    const aiBackdrop = document.getElementById('ai-modal-backdrop');

    const openAi = () => {
        aiModal.classList.add('ai-modal-open');
        aiModal.setAttribute('aria-hidden', 'false');
        document.getElementById('ai-input')?.focus();
    };
    const closeAi = () => {
        aiModal.classList.remove('ai-modal-open');
        aiModal.setAttribute('aria-hidden', 'true');
    };

    btnAi?.addEventListener('click', openAi);
    aiClose?.addEventListener('click', closeAi);
    aiBackdrop?.addEventListener('click', closeAi);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAi(); });

});
</script>
<?= $this->endSection() ?>
