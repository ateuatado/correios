/* ================================================================
   CorreiosComercial — app.js
   ================================================================ */

document.addEventListener('DOMContentLoaded', function () {

    // -----------------------------------------------------------------
    // Toggle Sidebar (mobile)
    // -----------------------------------------------------------------
    const sidebarToggler = document.getElementById('sidebarToggler');
    const sidebar        = document.getElementById('mainSidebar');
    const overlay        = document.getElementById('sidebarOverlay');

    if (sidebarToggler && sidebar) {
        sidebarToggler.addEventListener('click', () => {
            sidebar.classList.toggle('show');
            if (overlay) overlay.classList.toggle('d-none');
        });
        if (overlay) {
            overlay.addEventListener('click', () => {
                sidebar.classList.remove('show');
                overlay.classList.add('d-none');
            });
        }
    }

    // -----------------------------------------------------------------
    // Árvore principal (view arvore): colapso/expansão de módulos
    // -----------------------------------------------------------------
    document.querySelectorAll('.tree-modulo-header').forEach(header => {
        header.addEventListener('click', function () {
            const targetId = this.dataset.target;
            const target   = document.getElementById(targetId);
            if (!target) return;

            const isOpen = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!isOpen));
            target.classList.toggle('d-none', isOpen);
        });
    });

    // -----------------------------------------------------------------
    // Árvore principal: colapso/expansão de capítulos com anexos
    // -----------------------------------------------------------------
    document.querySelectorAll('.tree-capitulo-header[data-target]').forEach(header => {
        header.addEventListener('click', function (e) {
            e.preventDefault();
            const targetId = this.dataset.target;
            const target   = document.getElementById(targetId);
            if (!target) return;

            const isOpen = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', String(!isOpen));
            target.classList.toggle('d-none', isOpen);
        });
    });

    // -----------------------------------------------------------------
    // Sidebar: ativar item corrente
    // -----------------------------------------------------------------
    const currentPath = window.location.pathname;
    document.querySelectorAll('.sidebar .tree-link').forEach(link => {
        if (link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });

    // -----------------------------------------------------------------
    // Expandir automaticamente o módulo ativo na sidebar
    // -----------------------------------------------------------------
    document.querySelectorAll('.sidebar .tree-link.active').forEach(link => {
        let parent = link.closest('.tree-children');
        while (parent) {
            parent.classList.remove('d-none');
            const trigger = document.querySelector(`[data-target="${parent.id}"]`);
            if (trigger) trigger.setAttribute('aria-expanded', 'true');
            parent = parent.parentElement?.closest('.tree-children');
        }
    });

});
