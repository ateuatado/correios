<?= $this->extend('layouts/default') ?>
<?= $this->section('content') ?>

<div class="busca-page">

    <!-- ── Cabeçalho ─────────────────────────────────────────────── -->
    <div class="busca-hero">
        <div class="busca-hero-inner">
            <div class="busca-hero-icon"><i class="bi bi-search"></i></div>
            <h1 class="busca-hero-title">Pesquisar no MANCAT</h1>
            <p class="busca-hero-sub">Busque por termos, procedimentos ou serviços em todos os módulos e capítulos.</p>

            <form action="<?= base_url('manuais/buscar') ?>" method="get" class="busca-form" id="busca-form">
                <div class="busca-input-group">
                    <i class="bi bi-search busca-icon"></i>
                    <input
                        type="text"
                        name="q"
                        id="busca-input"
                        class="busca-input"
                        value="<?= esc($q ?? '') ?>"
                        placeholder="Ex: Agência Franqueada, SEDEX, franqueamento…"
                        autocomplete="off"
                        autofocus
                    >
                    <?php if (! empty($manuais)): ?>
                    <select name="manual_id" class="busca-select">
                        <option value="">Todos os manuais</option>
                        <?php foreach ($manuais as $man): ?>
                            <option value="<?= $man['id'] ?>" <?= ($manual_id ?? 0) == $man['id'] ? 'selected' : '' ?>>
                                <?= esc($man['codigo']) ?> — <?= esc($man['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php endif; ?>
                    <button type="submit" class="busca-btn">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>

                <!-- Sugestões rápidas -->
                <div class="busca-tags">
                    <span class="busca-tags-label">Sugestões:</span>
                    <?php foreach (['Agência Franqueada', 'SEDEX', 'Malote', 'Banco Postal', 'Alçadas', 'Franqueamento'] as $sug): ?>
                    <button type="button" class="busca-tag" onclick="document.getElementById('busca-input').value='<?= $sug ?>'; document.getElementById('busca-form').submit()">
                        <?= $sug ?>
                    </button>
                    <?php endforeach; ?>
                </div>
            </form>

            <!-- Placeholder AI ─────────────────────────────────── -->
            <div class="busca-ai-teaser">
                <i class="bi bi-stars"></i>
                <span><strong>Em breve:</strong> faça perguntas em linguagem natural e a IA buscará a resposta nos documentos.</span>
            </div>
        </div>
    </div>

    <!-- ── Resultados ────────────────────────────────────────────── -->
    <?php if (! empty($q)): ?>
    <div class="busca-resultados-wrap">

        <?php if (empty($resultados)): ?>
        <div class="busca-empty">
            <i class="bi bi-emoji-frown"></i>
            <h2>Nenhum resultado para <em>"<?= esc($q) ?>"</em></h2>
            <p>Tente palavras diferentes ou termos mais gerais.</p>
        </div>

        <?php else: ?>
        <div class="busca-stats">
            <i class="bi bi-check-circle-fill"></i>
            <strong><?= count($resultados) ?></strong> resultado<?= count($resultados) !== 1 ? 's' : '' ?> para
            <em>"<?= esc($q) ?>"</em>
        </div>

        <div class="resultados-grid">
            <?php foreach ($resultados as $item):
                $link = $item['doc_tipo'] === 'capitulo'
                    ? base_url("manuais/capitulo/{$item['doc_id']}#item-{$item['id']}")
                    : base_url("manuais/anexo/{$item['doc_id']}#item-{$item['id']}");

                $badge = match((int) $item['nivel']) {
                    1 => ['label' => 'Seção',        'cls' => 'n1'],
                    2 => ['label' => 'Subseção',     'cls' => 'n2'],
                    3 => ['label' => 'Sub-subseção', 'cls' => 'n3'],
                    4 => ['label' => 'Alínea',       'cls' => 'n4'],
                    default => ['label' => 'Item',   'cls' => 'n1'],
                };

                $tipo_badge = $item['doc_tipo'] === 'capitulo' ? 'Capítulo' : 'Anexo';

                // Destacar termo no título
                $tituloHl = preg_replace(
                    '/(' . preg_quote(esc($q), '/') . ')/iu',
                    '<mark>$1</mark>',
                    esc($item['titulo'])
                );

                // Snippet do conteúdo
                $conteudo   = $item['conteudo'] ?? '';
                $posicao    = mb_stripos($conteudo, $q);
                $snippetRaw = $posicao !== false
                    ? mb_substr($conteudo, max(0, $posicao - 80), 200)
                    : mb_substr($conteudo, 0, 200);
                $snippet = preg_replace(
                    '/(' . preg_quote(esc($q), '/') . ')/iu',
                    '<mark>$1</mark>',
                    esc($snippetRaw)
                );
            ?>
            <a href="<?= $link ?>" class="resultado-card">
                <div class="resultado-badges">
                    <span class="resultado-tipo"><?= $tipo_badge ?></span>
                    <span class="resultado-nivel <?= $badge['cls'] ?>"><?= $badge['label'] ?></span>
                    <span class="resultado-num"><?= esc($item['numero']) ?></span>
                </div>
                <h3 class="resultado-titulo"><?= $tituloHl ?></h3>
                <?php if ($snippet): ?>
                <p class="resultado-snippet">…<?= $snippet ?>…</p>
                <?php endif; ?>
                <div class="resultado-footer">
                    <i class="bi bi-arrow-right-circle"></i> Ver no documento
                </div>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</div><!-- /busca-page -->

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Foco automático no campo de busca
    const input = document.getElementById('busca-input');
    if (input && !input.value) input.focus();

    // Envio por Enter
    input?.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('busca-form').submit();
        }
    });
});
</script>
<?= $this->endSection() ?>
