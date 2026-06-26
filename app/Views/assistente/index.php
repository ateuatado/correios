<?php $this->extend('layouts/default'); ?>
<?php $this->section('content'); ?>

<div class="assistente-wrapper">

    <!-- ═══ CABEÇALHO ═══════════════════════════════════════════════ -->
    <div class="assistente-header">
        <div class="d-flex align-items-center gap-3">
            <div class="assistente-avatar">
                <i class="bi bi-stars"></i>
            </div>
            <div>
                <h1 class="assistente-titulo">Assistente IA</h1>
                <p class="assistente-subtitulo">
                    Tire dúvidas sobre o MANCAT e os serviços dos Correios
                </p>
            </div>
        </div>
        <?php if (! empty($historico)): ?>
        <a href="<?= base_url('assistente/limpar') ?>" class="btn-limpar" title="Limpar conversa">
            <i class="bi bi-trash3"></i> Limpar
        </a>
        <?php endif; ?>
    </div>

    <!-- ═══ ÁREA DO CHAT ═════════════════════════════════════════════ -->
    <div class="chat-area" id="chatArea">

        <!-- Mensagem de boas-vindas (só quando histórico vazio) -->
        <?php if (empty($historico)): ?>
        <div class="msg-boas-vindas" id="boasVindas">
            <div class="bv-icon"><i class="bi bi-robot"></i></div>
            <div class="bv-texto">
                <p>Olá! Sou o <strong>Assistente Comercial dos Correios</strong>.</p>
                <p>Posso responder perguntas sobre regras, prazos, limites e procedimentos do <strong>MANCAT</strong>.</p>
            </div>
            <div class="sugestoes">
                <p class="sugestao-titulo">Experimente perguntar:</p>
                <div class="sugestao-grid">
                    <button class="sugestao-btn" data-q="Qual o prazo de entrega do SEDEX?">
                        <i class="bi bi-clock-history"></i>
                        Qual o prazo de entrega do SEDEX?
                    </button>
                    <button class="sugestao-btn" data-q="Qual o peso máximo permitido para PAC?">
                        <i class="bi bi-box-seam"></i>
                        Qual o peso máximo para PAC?
                    </button>
                    <button class="sugestao-btn" data-q="Como funciona a Logística Reversa dos Correios?">
                        <i class="bi bi-arrow-repeat"></i>
                        Como funciona a Logística Reversa?
                    </button>
                    <button class="sugestao-btn" data-q="Quais serviços os Correios oferecem para e-commerce?">
                        <i class="bi bi-bag-check"></i>
                        Serviços para e-commerce?
                    </button>
                    <button class="sugestao-btn" data-q="O que é necessário para fazer um contrato com os Correios?">
                        <i class="bi bi-file-earmark-text"></i>
                        Como fazer contrato com Correios?
                    </button>
                    <button class="sugestao-btn" data-q="Quais são as dimensões máximas para encomendas?">
                        <i class="bi bi-rulers"></i>
                        Dimensões máximas para encomendas?
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Histórico de mensagens anteriores (da sessão) -->
        <?php foreach ($historico as $troca): ?>
        <div class="msg-par">
            <!-- Pergunta do usuário -->
            <div class="msg msg-usuario">
                <div class="msg-bubble msg-bubble-usuario">
                    <?= esc($troca['pergunta']) ?>
                </div>
                <div class="msg-hora"><?= esc($troca['timestamp']) ?></div>
            </div>
            <!-- Resposta da IA -->
            <div class="msg msg-ia">
                <div class="msg-avatar-ia"><i class="bi bi-stars"></i></div>
                <div class="msg-conteudo-ia">
                    <div class="msg-bubble msg-bubble-ia markdown-content">
                        <?= nl2br(esc($troca['resposta'])) ?>
                    </div>
                    <?php if (! empty($troca['fontes'])): ?>
                    <div class="fontes-container">
                        <span class="fontes-label"><i class="bi bi-bookmark2"></i> Fontes:</span>
                        <?php foreach ($troca['fontes'] as $fonte): ?>
                        <?php if ($fonte['url']): ?>
                        <a href="<?= esc($fonte['url']) ?>" target="_blank" class="fonte-chip">
                            <i class="bi bi-journal-text"></i>
                            <?= esc(mb_substr($fonte['label'], 0, 50)) ?>
                        </a>
                        <?php else: ?>
                        <span class="fonte-chip fonte-chip-regra">
                            <i class="bi bi-shield-check"></i>
                            <?= esc(mb_substr($fonte['label'], 0, 50)) ?>
                        </span>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Indicador de digitação (oculto por padrão) -->
        <div class="msg msg-ia" id="msgTyping" style="display:none;">
            <div class="msg-avatar-ia"><i class="bi bi-stars"></i></div>
            <div class="msg-bubble msg-bubble-ia typing-indicator">
                <span></span><span></span><span></span>
            </div>
        </div>

    </div><!-- /chat-area -->

    <!-- ═══ ÁREA DE INPUT ════════════════════════════════════════════ -->
    <div class="chat-input-wrapper">
        <div class="chat-input-container">
            <textarea
                id="inputPergunta"
                class="chat-input"
                placeholder="Digite sua pergunta sobre o MANCAT..."
                rows="1"
                maxlength="500"
                autocomplete="off"
            ></textarea>
            <button id="btnEnviar" class="btn-enviar" title="Enviar (Enter)">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
        <div class="input-footer">
            <span id="charCount" class="char-count">0 / 500</span>
            <span class="input-hint">Enter para enviar · Shift+Enter para nova linha</span>
        </div>
    </div>

</div><!-- /assistente-wrapper -->

<?php $this->endSection(); ?>

<?php $this->section('scripts'); ?>
<style>
/* ─────────────────────────────────────────────────────────────────────
   ASSISTENTE IA — Estilos
───────────────────────────────────────────────────────────────────── */
:root {
    --ia-azul:       #1a56db;
    --ia-azul-light: #e8f0fe;
    --ia-gold:       #F5A800;
    --ia-gold-light: rgba(245,168,0,.1);
    --ia-surface:    #ffffff;
    --ia-border:     #e5e7eb;
    --ia-text:       #1e293b;
    --ia-muted:      #64748b;
    --ia-radius:     16px;
}

.assistente-wrapper {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 60px);
    max-width: 900px;
    margin: 0 auto;
    padding: 0 1rem;
}

/* Header */
.assistente-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 0 1rem;
    border-bottom: 1px solid var(--ia-border);
    flex-shrink: 0;
}
.assistente-avatar {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, var(--ia-gold), #e67e00);
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem; color: white;
    box-shadow: 0 4px 12px rgba(245,168,0,.35);
}
.assistente-titulo {
    font-size: 1.25rem; font-weight: 700;
    color: var(--ia-text); margin: 0;
}
.assistente-subtitulo {
    font-size: .8rem; color: var(--ia-muted); margin: .1rem 0 0;
}
.btn-limpar {
    font-size: .78rem; color: var(--ia-muted);
    text-decoration: none;
    padding: .35rem .7rem;
    border: 1px solid var(--ia-border);
    border-radius: 8px;
    transition: all .2s;
}
.btn-limpar:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }

/* Chat area */
.chat-area {
    flex: 1;
    overflow-y: auto;
    padding: 1.5rem 0;
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    scroll-behavior: smooth;
}
.chat-area::-webkit-scrollbar { width: 4px; }
.chat-area::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* Boas-vindas */
.msg-boas-vindas {
    text-align: center;
    padding: 2rem;
    background: linear-gradient(135deg, #fefce8, #fff7ed);
    border: 1px solid #fed7aa;
    border-radius: var(--ia-radius);
}
.bv-icon { font-size: 2.5rem; color: var(--ia-gold); margin-bottom: 1rem; }
.bv-texto { color: var(--ia-text); font-size: .95rem; line-height: 1.6; }
.sugestao-titulo {
    font-size: .78rem; font-weight: 700;
    color: var(--ia-muted); text-transform: uppercase;
    letter-spacing: .07em; margin: 1.5rem 0 .75rem;
}
.sugestao-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: .6rem;
}
.sugestao-btn {
    background: white;
    border: 1px solid var(--ia-border);
    border-radius: 10px;
    padding: .6rem .75rem;
    font-size: .78rem;
    color: var(--ia-text);
    cursor: pointer;
    text-align: left;
    display: flex; align-items: flex-start; gap: .5rem;
    transition: all .2s;
    line-height: 1.4;
}
.sugestao-btn i { color: var(--ia-gold); flex-shrink: 0; margin-top: .1rem; }
.sugestao-btn:hover {
    background: var(--ia-gold-light);
    border-color: var(--ia-gold);
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,.08);
}

/* Mensagens */
.msg-par { display: flex; flex-direction: column; gap: .75rem; }
.msg { display: flex; gap: .6rem; }

/* Usuário (direita) */
.msg-usuario { flex-direction: row-reverse; }
.msg-bubble-usuario {
    background: linear-gradient(135deg, var(--ia-azul), #1e40af);
    color: white;
    border-radius: var(--ia-radius) var(--ia-radius) 4px var(--ia-radius);
    padding: .75rem 1rem;
    max-width: 75%;
    font-size: .9rem;
    line-height: 1.5;
    box-shadow: 0 2px 8px rgba(26,86,219,.25);
}
.msg-hora {
    font-size: .68rem;
    color: var(--ia-muted);
    margin-top: .25rem;
    text-align: right;
}

/* IA (esquerda) */
.msg-avatar-ia {
    width: 32px; height: 32px;
    background: linear-gradient(135deg, var(--ia-gold), #e67e00);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; color: white;
    flex-shrink: 0;
    margin-top: .1rem;
    box-shadow: 0 2px 6px rgba(245,168,0,.3);
}
.msg-conteudo-ia { display: flex; flex-direction: column; gap: .5rem; max-width: 85%; }
.msg-bubble-ia {
    background: var(--ia-surface);
    border: 1px solid var(--ia-border);
    border-radius: 4px var(--ia-radius) var(--ia-radius) var(--ia-radius);
    padding: .85rem 1rem;
    font-size: .88rem;
    line-height: 1.6;
    color: var(--ia-text);
    box-shadow: 0 2px 6px rgba(0,0,0,.06);
}

/* Markdown dentro do bubble */
.markdown-content strong { color: #1e3a8a; }
.markdown-content ul, .markdown-content ol { padding-left: 1.2rem; margin: .4rem 0; }
.markdown-content li { margin: .2rem 0; }
.markdown-content p { margin: .35rem 0; }
.markdown-content p:first-child { margin-top: 0; }
.markdown-content p:last-child { margin-bottom: 0; }

/* Fontes */
.fontes-container {
    display: flex; flex-wrap: wrap;
    align-items: center; gap: .4rem;
    font-size: .72rem;
}
.fontes-label {
    color: var(--ia-muted);
    font-weight: 600;
    font-size: .7rem;
    text-transform: uppercase;
    letter-spacing: .05em;
}
.fonte-chip {
    background: var(--ia-azul-light);
    color: var(--ia-azul);
    border-radius: 20px;
    padding: .15rem .6rem;
    font-size: .7rem;
    font-weight: 600;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: .25rem;
    transition: all .2s;
}
.fonte-chip:hover { background: var(--ia-azul); color: white; }
.fonte-chip-regra {
    background: rgba(245,168,0,.12);
    color: #92400e;
}

/* Typing indicator */
.typing-indicator {
    display: flex; align-items: center; gap: .35rem;
    padding: .75rem 1rem !important;
    width: fit-content;
}
.typing-indicator span {
    width: 7px; height: 7px;
    background: #94a3b8;
    border-radius: 50%;
    display: inline-block;
    animation: typing-bounce 1.2s infinite;
}
.typing-indicator span:nth-child(2) { animation-delay: .2s; }
.typing-indicator span:nth-child(3) { animation-delay: .4s; }
@keyframes typing-bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30%            { transform: translateY(-6px); background: var(--ia-gold); }
}

/* Input */
.chat-input-wrapper {
    flex-shrink: 0;
    padding: 1rem 0 1.25rem;
    border-top: 1px solid var(--ia-border);
}
.chat-input-container {
    display: flex;
    align-items: flex-end;
    gap: .75rem;
    background: white;
    border: 2px solid var(--ia-border);
    border-radius: var(--ia-radius);
    padding: .6rem .75rem;
    transition: border-color .2s, box-shadow .2s;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}
.chat-input-container:focus-within {
    border-color: var(--ia-gold);
    box-shadow: 0 0 0 3px rgba(245,168,0,.15), 0 2px 8px rgba(0,0,0,.08);
}
.chat-input {
    flex: 1;
    border: none; outline: none;
    resize: none;
    font-size: .92rem;
    line-height: 1.5;
    color: var(--ia-text);
    background: transparent;
    max-height: 120px;
    overflow-y: auto;
    font-family: inherit;
}
.chat-input::placeholder { color: #adb5bd; }
.btn-enviar {
    width: 40px; height: 40px;
    background: linear-gradient(135deg, var(--ia-gold), #e67e00);
    color: white; border: none;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem;
    cursor: pointer;
    flex-shrink: 0;
    transition: all .2s;
    box-shadow: 0 2px 6px rgba(245,168,0,.35);
}
.btn-enviar:hover { transform: scale(1.05); box-shadow: 0 4px 10px rgba(245,168,0,.4); }
.btn-enviar:disabled { opacity: .5; transform: none; cursor: not-allowed; }
.input-footer {
    display: flex;
    justify-content: space-between;
    font-size: .7rem;
    color: var(--ia-muted);
    padding: .35rem .1rem 0;
}
.char-count.warn { color: #dc2626; }

/* Erro inline */
.msg-erro .msg-bubble-ia {
    background: #fef2f2;
    border-color: #fca5a5;
    color: #991b1b;
}

/* Animação de entrada das mensagens */
@keyframes slideIn {
    from { opacity: 0; transform: translateY(8px); }
    to   { opacity: 1; transform: translateY(0); }
}
.msg { animation: slideIn .25s ease; }
</style>

<script>
(function () {
    'use strict';

    const chatArea    = document.getElementById('chatArea');
    const input       = document.getElementById('inputPergunta');
    const btnEnviar   = document.getElementById('btnEnviar');
    const msgTyping   = document.getElementById('msgTyping');
    const charCount   = document.getElementById('charCount');
    const boasVindas  = document.getElementById('boasVindas');
    const CHAT_URL    = '<?= base_url('assistente/chat') ?>';
    const CSRF_NAME   = '<?= csrf_token() ?>';
    let   CSRF_HASH   = '<?= csrf_hash() ?>';

    // ── Auto-resize textarea ────────────────────────────────────────
    input.addEventListener('input', () => {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 120) + 'px';

        const len = input.value.length;
        charCount.textContent = `${len} / 500`;
        charCount.classList.toggle('warn', len > 450);
    });

    // ── Enter para enviar ───────────────────────────────────────────
    input.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && ! e.shiftKey) {
            e.preventDefault();
            enviar();
        }
    });

    // ── Botão enviar ────────────────────────────────────────────────
    btnEnviar.addEventListener('click', enviar);

    // ── Sugestões ───────────────────────────────────────────────────
    document.querySelectorAll('.sugestao-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            input.value = btn.dataset.q;
            input.dispatchEvent(new Event('input'));
            enviar();
        });
    });

    // ── Scroll ao fundo ─────────────────────────────────────────────
    function scrollFundo() {
        chatArea.scrollTo({ top: chatArea.scrollHeight, behavior: 'smooth' });
    }

    // ── Formata markdown simples para HTML ──────────────────────────
    function markdown(text) {
        return text
            // Negrito **texto**
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            // Itálico *texto*
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            // Código `codigo`
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            // Listas com -
            .replace(/^[-•] (.+)$/gm, '<li>$1</li>')
            // Listas com número
            .replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
            // Agrupa <li> em <ul>
            .replace(/(<li>.*?<\/li>(\n|$))+/g, m => `<ul>${m}</ul>`)
            // Quebras de linha → <p> (blocos separados por linha vazia)
            .split(/\n\n+/)
            .map(p => p.trim())
            .filter(p => p && !p.startsWith('<ul>') && !p.startsWith('<li>'))
            .reduce((acc, p) => {
                if (p.startsWith('<ul>')) return acc + p;
                return acc + `<p>${p.replace(/\n/g, '<br>')}</p>`;
            }, text.replace(/\n\n+/g, '\n\n')
                   .replace(/^[-•] (.+)$/gm, '<li>$1</li>')
                   .replace(/^\d+\. (.+)$/gm, '<li>$1</li>')
                   .replace(/(<li>.*?<\/li>)/g, m => m)
            );
    }

    // ── Formata markdown (versão simples e confiável) ───────────────
    function formatarMd(text) {
        let html = text
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g,     '<em>$1</em>')
            .replace(/`([^`]+)`/g,     '<code>$1</code>');

        // Listas
        const linhas = html.split('\n');
        let resultado = [];
        let emLista = false;
        for (const linha of linhas) {
            const listMatch = linha.match(/^[-•*]\s+(.+)/) || linha.match(/^\d+\.\s+(.+)/);
            if (listMatch) {
                if (!emLista) { resultado.push('<ul>'); emLista = true; }
                resultado.push(`<li>${listMatch[1]}</li>`);
            } else {
                if (emLista) { resultado.push('</ul>'); emLista = false; }
                resultado.push(linha);
            }
        }
        if (emLista) resultado.push('</ul>');

        // Parágrafos
        html = resultado.join('\n')
            .split(/\n\n+/)
            .map(b => b.trim())
            .filter(b => b)
            .map(b => b.startsWith('<ul>') ? b : `<p>${b.replace(/\n/g, '<br>')}</p>`)
            .join('');

        return html;
    }

    // ── Adiciona mensagem do usuário na tela ─────────────────────────
    function adicionarMsgUsuario(texto) {
        const hora = new Date().toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        const div = document.createElement('div');
        div.className = 'msg-par';
        div.innerHTML = `
            <div class="msg msg-usuario">
                <div class="msg-bubble msg-bubble-usuario">${texto.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</div>
                <div class="msg-hora">${hora}</div>
            </div>`;
        chatArea.appendChild(div);
        return div;
    }

    // ── Adiciona resposta da IA na tela ──────────────────────────────
    function adicionarMsgIA(msgPar, resposta, fontes) {
        let fontesHtml = '';
        if (fontes && fontes.length > 0) {
            fontesHtml = `<div class="fontes-container">
                <span class="fontes-label"><i class="bi bi-bookmark2"></i> Fontes:</span>
                ${fontes.map(f => f.url
                    ? `<a href="${f.url}" target="_blank" class="fonte-chip"><i class="bi bi-journal-text"></i>${f.label.substring(0,50)}</a>`
                    : `<span class="fonte-chip fonte-chip-regra"><i class="bi bi-shield-check"></i>${f.label.substring(0,50)}</span>`
                ).join('')}
            </div>`;
        }
        const iaDiv = document.createElement('div');
        iaDiv.className = 'msg msg-ia';
        iaDiv.innerHTML = `
            <div class="msg-avatar-ia"><i class="bi bi-stars"></i></div>
            <div class="msg-conteudo-ia">
                <div class="msg-bubble msg-bubble-ia markdown-content">${formatarMd(resposta)}</div>
                ${fontesHtml}
            </div>`;
        msgPar.appendChild(iaDiv);
        scrollFundo();
    }

    // ── Adiciona mensagem de erro ────────────────────────────────────
    function adicionarErro(msgPar, erro) {
        const iaDiv = document.createElement('div');
        iaDiv.className = 'msg msg-ia msg-erro';
        iaDiv.innerHTML = `
            <div class="msg-avatar-ia"><i class="bi bi-stars"></i></div>
            <div class="msg-conteudo-ia">
                <div class="msg-bubble msg-bubble-ia">
                    <i class="bi bi-exclamation-triangle me-1"></i>${erro}
                </div>
            </div>`;
        msgPar.appendChild(iaDiv);
        scrollFundo();
    }

    // ── Função principal de envio ────────────────────────────────────
    async function enviar() {
        const texto = input.value.trim();
        if (! texto || btnEnviar.disabled) return;

        // Esconde boas-vindas
        if (boasVindas) boasVindas.style.display = 'none';

        // Adiciona msg do usuário
        const msgPar = adicionarMsgUsuario(texto);
        scrollFundo();

        // Limpa input
        input.value = '';
        input.style.height = 'auto';
        charCount.textContent = '0 / 500';

        // Desabilita enquanto processa
        btnEnviar.disabled = true;
        input.disabled = true;
        msgTyping.style.display = 'flex';
        scrollFundo();

        try {
            const form = new FormData();
            form.append('pergunta', texto);
            form.append(CSRF_NAME, CSRF_HASH);

            const resp = await fetch(CHAT_URL, { method: 'POST', body: form });
            const data = await resp.json();

            msgTyping.style.display = 'none';

            if (data.erro) {
                adicionarErro(msgPar, data.erro);
            } else {
                adicionarMsgIA(msgPar, data.resposta, data.fontes);
                // Atualiza o CSRF hash para a próxima requisição
                if (data.csrf_hash) {
                    CSRF_HASH = data.csrf_hash;
                }
            }
        } catch (err) {
            msgTyping.style.display = 'none';
            adicionarErro(msgPar, 'Erro de conexão. Verifique sua internet e tente novamente.');
        }

        btnEnviar.disabled = false;
        input.disabled = false;
        input.focus();
    }

    // Scroll inicial ao fundo (se tiver histórico)
    scrollFundo();
    input.focus();
})();
</script>
<?php $this->endSection(); ?>

