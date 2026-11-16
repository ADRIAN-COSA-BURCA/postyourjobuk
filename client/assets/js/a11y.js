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

            const sortHandler = () => {
                const currentSort = th.getAttribute('aria-sort');
                const nextDir = currentSort === 'ascending' ? 'descending' : 'ascending';

                headers.forEach(h => h.setAttribute('aria-sort', 'none'));
                th.setAttribute('aria-sort', nextDir);

                const rows = Array.from(tbody.querySelectorAll('tr'));
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
                        valA = parseFloat(valA) || 0;
                        valB = parseFloat(valB) || 0;
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
                <footer class="site-footer" role="contentinfo">
                    <div class="container footer-inner">
                        <small>&copy; ${year} PostYourJobUK. Cloud-Native AI Recruitment &amp; ATS Platform.</small>
                        <div class="footer-links">
                            <a href="/vision"><small>Our Vision</small></a>
                            <a href="/terms"><small>Terms of Service</small></a>
                            <a href="/about"><small>About</small></a>
                            <a href="/contact"><small>Contact</small></a>
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
