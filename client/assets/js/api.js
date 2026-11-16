/**
 * PostYourJobUK — Centralized Fetch & Session API Client (api.js)
 * Zero External Dependencies | Automatic CSRF Header Injection | Accessible Toast Alerts
 */
(function (window) {
    'use strict';

    const state = {
        csrfToken: '',
        authenticated: false,
        role: 'guest',
        user: null,
        initialized: false
    };

    /**
     * Escape HTML entities safely to prevent XSS when building DOM templates
     */
    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Render an accessible toast alert (role="status" / aria-live="polite")
     */
    function showToast(message, type = 'success', durationMs = 5000) {
        let region = document.getElementById('toast-live-region');
        if (!region) {
            region = document.createElement('div');
            region.id = 'toast-live-region';
            region.className = 'toast-region';
            region.setAttribute('role', 'status');
            region.setAttribute('aria-live', 'polite');
            document.body.appendChild(region);
        }

        const alertEl = document.createElement('div');
        const alertClass = type === 'error' ? 'alert-error' : (type === 'warning' ? 'alert-warning' : 'alert-success');
        alertEl.className = `alert ${alertClass}`;
        alertEl.style.boxShadow = '0 10px 30px rgba(0, 0, 0, 0.45)';
        alertEl.innerHTML = `
            <span>${escapeHtml(message)}</span>
            <button type="button" class="btn btn-ghost btn-sm" aria-label="Dismiss notification" style="padding: 0.15rem 0.5rem; margin-left: 0.5rem;">✕</button>
        `;

        alertEl.querySelector('button').addEventListener('click', () => alertEl.remove());
        region.appendChild(alertEl);

        if (durationMs > 0) {
            setTimeout(() => {
                if (alertEl.parentNode) alertEl.remove();
            }, durationMs);
        }
    }

    /**
     * Perform a standardized JSON or FormData HTTP request to /api/*
     */
    async function request(url, options = {}) {
        const method = (options.method || 'GET').toUpperCase();
        const headers = Object.assign({
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }, options.headers || {});

        if (state.csrfToken && ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)) {
            headers['X-CSRF-Token'] = state.csrfToken;
        }

        let body = options.body || undefined;
        if (body && !(body instanceof FormData) && typeof body === 'object') {
            headers['Content-Type'] = 'application/json';
            if (state.csrfToken && !body.csrf_token) {
                body.csrf_token = state.csrfToken;
            }
            body = JSON.stringify(body);
        } else if (body instanceof FormData && state.csrfToken && !body.has('csrf_token')) {
            body.append('csrf_token', state.csrfToken);
        }

        const response = await fetch(url, {
            method,
            headers,
            body,
            credentials: 'same-origin'
        });

        let data = {};
        const contentType = response.headers.get('content-type') || '';
        if (contentType.includes('application/json')) {
            data = await response.json();
        } else {
            const text = await response.text();
            data = { success: response.ok, raw: text };
        }

        if (data && data.csrf_token) {
            state.csrfToken = data.csrf_token;
        }

        if (response.status === 401 && data.redirect && !options.suppressAuthRedirect) {
            window.location.href = data.redirect;
        }

        return {
            ok: response.ok,
            status: response.status,
            data
        };
    }

    /**
     * Synchronize session state with GET /api/auth/session
     */
    async function initSession() {
        try {
            const res = await request('/api/auth/session', { suppressAuthRedirect: true });
            if (res.ok && res.data) {
                state.csrfToken = res.data.csrf_token || '';
                state.authenticated = Boolean(res.data.authenticated);
                state.role = res.data.role || 'guest';
                state.user = res.data.user || null;
                state.initialized = true;

                if (res.data.flash_success) showToast(res.data.flash_success, 'success');
                if (res.data.flash_error) showToast(res.data.flash_error, 'error');
            }
        } catch (err) {
            console.error('Session initialization error:', err);
        }
        return state;
    }

    window.AppApi = {
        state,
        escapeHtml,
        showToast,
        request,
        initSession,
        get: (url, opts = {}) => request(url, Object.assign({}, opts, { method: 'GET' })),
        post: (url, body = {}, opts = {}) => request(url, Object.assign({}, opts, { method: 'POST', body }))
    };
})(window);
