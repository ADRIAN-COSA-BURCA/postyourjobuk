/**
 * PostYourJobUK — WCAG 2.1 Level AA Accessibility & Shared Layout Engine (a11y.js)
 * Provides Modal Focus Trap, Escape Dismissal, Keyboard Table Sorting, and Role-Aware Shell
 */
(function (window) {
    'use strict';

    let activeModal = null;
    let lastFocusedElement = null;

    const FOCUSABLE_SELECTOR = [
        'a[href]',
        'button:not([disabled])',
        'input:not([disabled]):not([type="hidden"])',
        'select:not([disabled])',
        'textarea:not([disabled])',
        '[tabindex]:not([tabindex="-1"])'
    ].join(', ');

    /**
     * Open a modal dialog with WCAG 2.1 AA focus trapping and aria-modal="true"
     */
    function openModal(modalId, triggerElement = null) {
        const modal = typeof modalId === 'string' ? document.getElementById(modalId) : modalId;
        if (!modal) return;

        lastFocusedElement = triggerElement || document.activeElement;
        activeModal = modal;

        modal.classList.add('is-open');
        modal.style.display = 'flex';
        modal.setAttribute('aria-hidden', 'false');

        const focusables = modal.querySelectorAll(FOCUSABLE_SELECTOR);
        if (focusables.length > 0) {
            setTimeout(() => focusables[0].focus(), 20);
        }
    }

    /**
     * Close the active modal and restore focus to the triggering element
     */
    function closeModal(modalId = null) {
        const modal = modalId
            ? (typeof modalId === 'string' ? document.getElementById(modalId) : modalId)
            : activeModal;
        if (!modal) return;

        modal.classList.remove('is-open');
        modal.style.display = 'none';
        modal.setAttribute('aria-hidden', 'true');

        if (activeModal === modal) {
            activeModal = null;
        }
        if (lastFocusedElement && typeof lastFocusedElement.focus === 'function') {
            lastFocusedElement.focus();
            lastFocusedElement = null;
        }
    }

    // Global Keyboard Listener for Escape & Tab Focus Trap inside Modals
    document.addEventListener('keydown', function (e) {
        if (!activeModal) return;

        if (e.key === 'Escape') {
            e.preventDefault();
            closeModal(activeModal);
            return;
        }

        if (e.key === 'Tab') {
            const focusables = Array.from(activeModal.querySelectorAll(FOCUSABLE_SELECTOR))
                .filter(el => el.offsetParent !== null);
            if (focusables.length === 0) {
                e.preventDefault();
                return;
            }
            const first = focusables[0];
            const last = focusables[focusables.length - 1];

            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    });

    /**
     * Attach keyboard-accessible sorting to table headers (.sortable-th)
     */
    function initSortableTable(tableEl) {
        if (!tableEl) return;
        const headers = tableEl.querySelectorAll('th.sortable-th');
        const tbody = tableEl.querySelector('tbody');
        if (!tbody) return;

        headers.forEach((th, colIdx) => {
            th.setAttribute('tabindex', '0');
            th.setAttribute('role', 'columnheader');
            if (!th.hasAttribute('aria-sort')) {
                th.setAttribute('aria-sort', 'none');
            }

            let indicator = th.querySelector('.sort-indicator');
            if (!indicator) {
                indicator = document.createElement('span');
                indicator.className = 'sort-indicator';
                indicator.setAttribute('aria-hidden', 'true');
                const initSort = th.getAttribute('aria-sort');
                indicator.textContent = initSort === 'ascending' ? '▲' : (initSort === 'descending' ? '▼' : '⇅');
                th.appendChild(indicator);
            }

            const sortHandler = () => {
                const currentSort = th.getAttribute('aria-sort');
                const nextDir = currentSort === 'ascending' ? 'descending' : 'ascending';

                headers.forEach(h => {
                    h.setAttribute('aria-sort', 'none');
                    const ind = h.querySelector('.sort-indicator');
                    if (ind) ind.textContent = '⇅';
                });
                th.setAttribute('aria-sort', nextDir);
                indicator.textContent = nextDir === 'ascending' ? '▲' : '▼';

                const rows = Array.from(tbody.querySelectorAll('tr')).filter(r => !r.id || !r.id.toLowerCase().includes('noresults'));
                const sortKey = th.dataset.sortKey || '';
                const sortType = th.dataset.sortType || 'string';

                rows.sort((rowA, rowB) => {
                    let valA = sortKey && rowA.dataset[sortKey] !== undefined
                        ? rowA.dataset[sortKey]
                        : (rowA.children[colIdx] ? rowA.children[colIdx].textContent.trim() : '');
                    let valB = sortKey && rowB.dataset[sortKey] !== undefined
                        ? rowB.dataset[sortKey]
                        : (rowB.children[colIdx] ? rowB.children[colIdx].textContent.trim() : '');

                    if (sortType === 'number') {
                        valA = parseFloat(String(valA).replace(/[^0-9.-]/g, '')) || 0;
                        valB = parseFloat(String(valB).replace(/[^0-9.-]/g, '')) || 0;
                        return nextDir === 'ascending' ? valA - valB : valB - valA;
                    }
                    return nextDir === 'ascending'
                        ? String(valA).localeCompare(String(valB))
                        : String(valB).localeCompare(String(valA));
                });

                rows.forEach(r => tbody.appendChild(r));
            };

            th.addEventListener('click', sortHandler);
            th.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    sortHandler();
                }
            });
        });
    }

    /**
     * Render global Skip Link, Role-Aware Header, and Footer into #app-header and #app-footer
     */
    function renderSharedShell(sessionState) {
        const role = (sessionState && sessionState.role) || 'guest';
        const headerHost = document.getElementById('app-header');
        const footerHost = document.getElementById('app-footer');

        if (!document.querySelector('.skip-link')) {
            const skip = document.createElement('a');
            skip.href = '#main-content';
            skip.className = 'skip-link';
            skip.textContent = 'Skip to main content';
            document.body.insertBefore(skip, document.body.firstChild);
        }

        if (headerHost) {
            let navHtml = '';
            if (role === 'admin') {
                navHtml = `
                    <a href="/admin">Admin Panel</a>
                    <a href="/admin/analytics">Platform Analytics</a>
                    <a href="/admin/fairness">Fairness Audit</a>
                    <button type="button" id="shell-logout-btn" class="btn btn-danger btn-sm">Logout</button>
                `;
            } else if (role === 'recruiter') {
                navHtml = `
                    <a href="/">Browse Jobs</a>
                    <a href="/dashboard">Dashboard</a>
                    <a href="/dashboard/analytics">Analytics</a>
                    <button type="button" id="shell-logout-btn" class="btn btn-ghost btn-sm">Logout</button>
                `;
            } else {
                navHtml = `
                    <a href="/">Home</a>
                    <a href="/resume-builder">CV Builder</a>
                    <a href="/about">About</a>
                    <a href="/contact">Contact</a>
                    <a href="/login">Recruiter Login</a>
                    <a href="/admin/login" class="btn btn-primary btn-sm">Admin</a>
                `;
            }

            headerHost.innerHTML = `
                <header class="header" role="banner">
                    <div class="container header-inner">
                        <a href="/" class="brand-logo" aria-label="PostYourJobUK Home">PostYourJob<span>UK</span></a>
                        <nav class="nav" aria-label="Primary Navigation">
                            ${navHtml}
                        </nav>
                    </div>
                </header>
            `;

            const logoutBtn = document.getElementById('shell-logout-btn');
            if (logoutBtn) {
                logoutBtn.addEventListener('click', async () => {
                    const endpoint = role === 'admin' ? '/api/admin/logout' : '/api/auth/logout';
                    const res = await window.AppApi.post(endpoint);
                    window.location.href = (res.data && res.data.redirect) ? res.data.redirect : '/login';
                });
            }
        }

        if (footerHost) {
            const year = new Date().getFullYear();
            footerHost.innerHTML = `
                <footer class="site-footer" role="contentinfo" style="background: #070a12; border-top: 1px solid var(--glass-border); padding: 1.35rem 0; margin-top: 3rem; width: 100%;">
                    <div class="container footer-inner" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                        <p style="font-size: 0.78rem; color: var(--text-subtle); margin: 0;">&copy; ${year} PostYourJobUK. All rights reserved.</p>
                        <div class="footer-links" style="display: flex; align-items: center; gap: 1.25rem;">
                            <a href="/vision" style="font-size: 0.82rem; color: var(--text-muted); text-decoration: none;">Company vision</a>
                            <a href="/terms" style="font-size: 0.82rem; color: var(--text-muted); text-decoration: none;">Terms of service</a>
                            <span style="display: inline-block; width: 1px; height: 14px; background: var(--glass-border);" aria-hidden="true"></span>
                            <a href="https://wa.me/442079460958?text=Hello" target="_blank" rel="noopener noreferrer" aria-label="Chat via WhatsApp" title="Chat via WhatsApp" style="display: inline-flex; align-items: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#9ca3af" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/><path d="M12 0C5.373 0 0 5.373 0 12c0 2.123.554 4.17 1.535 5.961L.057 23.428a.75.75 0 0 0 .916.916l5.453-1.478A11.952 11.952 0 0 0 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.75a9.712 9.712 0 0 1-4.953-1.354l-.355-.211-3.671.995.944-3.58-.232-.368A9.712 9.712 0 0 1 2.25 12C2.25 6.615 6.615 2.25 12 2.25S21.75 6.615 21.75 12 17.385 21.75 12 21.75z"/></svg>
                            </a>
                            <a href="https://linkedin.com/company/PostyourjobUK" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" title="LinkedIn" style="display: inline-flex; align-items: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="#9ca3af" aria-hidden="true"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>
                            </a>
                        </div>
                    </div>
                </footer>
            `;
        }
    }

    window.AppA11y = {
        openModal,
        closeModal,
        initSortableTable,
        renderSharedShell
    };
})(window);
