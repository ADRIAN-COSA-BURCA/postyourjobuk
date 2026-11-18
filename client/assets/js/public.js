/**
 * PostYourJobUK — Public Job Board, Contact Form, Job Details & Candidate Application (public.js)
 * Zero External Dependencies | WCAG 2.1 Level AA Compliant
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;
    let allJobs = [];

    function initContactView() {
        const form = document.getElementById('public-contact-form');
        if (!form) return;
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const payload = {
                name: form.name.value.trim(),
                email: form.email.value.trim(),
                subject: form.subject.value.trim(),
                message: form.message.value.trim()
            };
            const res = await window.AppApi.post('/api/public/contact', payload);
            if (res.ok && res.data.success) {
                showToast(res.data.message, 'success');
                form.reset();
            } else {
                showToast((res.data && res.data.error) || 'Could not send message.', 'error');
            }
        });
    }

    function renderJobsList(jobs, totalCount = null) {
        const container = document.getElementById('jobs-list-container');
        const counterEl = document.getElementById('jobs-count-label');
        if (!container) return;

        const total = totalCount !== null ? totalCount : allJobs.length;
        if (counterEl) {
            counterEl.textContent = jobs.length === total
                ? `Showing ${jobs.length} opportunities`
                : `Showing ${jobs.length} of ${total} opportunities`;
        }

        if (!jobs.length) {
            container.style.display = 'block';
            container.innerHTML = `
                <div class="card text-center" style="padding: 2.5rem; background: #1f2937; border: 1px solid #374151;">
                    <p style="font-size: 1.1rem; color: #94a3b8; margin-bottom: 1rem;">🔍 No job opportunities match your selected filter criteria.</p>
                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('filter-reset-btn')?.click()">Clear Filters</button>
                </div>
            `;
            return;
        }

        container.style.display = 'grid';
        container.innerHTML = jobs.map(job => {
            const loc = job.location ? escapeHtml(job.location) : 'Remote';
            const salary = job.salary ? escapeHtml(job.salary) : 'Competitive';
            const rawEmp = (job.employment_type || 'full-time').replace('-', ' ');
            const empType = escapeHtml(rawEmp.charAt(0).toUpperCase() + rawEmp.slice(1));
            const industry = job.industry ? escapeHtml(job.industry) : '';

            return `
                <div class="job-card-wrapper">
                    <a href="/job?id=${Number(job.job_id)}" class="card job-card-link">
                        <div>
                            <!-- Header: FULL Company Name + Employment Badge -->
                            <div class="flex-between" style="margin-bottom: 1rem; align-items: center; gap: 0.75rem; display: flex; justify-content: space-between;">
                                <div style="font-weight: 700; color: #f3f4f6; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; flex: 1;" title="${escapeHtml(job.company_name || 'Company')}">
                                    ${escapeHtml(job.company_name || 'Hiring Company')}
                                </div>
                                <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 0.75rem; white-space: nowrap; flex-shrink: 0;">
                                    ${empType}
                                </span>
                            </div>

                            <!-- Title -->
                            <h3 style="margin-bottom: 0.5rem; font-size: 1.15rem; color: #ffffff;">${escapeHtml(job.title)}</h3>

                            <!-- Meta details (Location, Salary, Industry) -->
                            <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                <span>📍 ${loc}</span>
                                <span style="color: var(--border);">•</span>
                                <span>💷 ${salary}</span>
                                ${industry ? `<span style="color: var(--border);">•</span><span style="color: #38bdf8;">🏷️ ${industry}</span>` : ''}
                            </p>
                        </div>

                        <!-- Card Footer: Time Ago (Left) + Unique Job ID (Right) -->
                        <div class="text-muted" style="font-size: 0.8rem; border-top: 1px solid var(--border); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                            <span>🕒 Posted ${escapeHtml(job.time_ago || 'recently')}</span>
                            <span style="font-family: monospace; font-size: 0.78rem; font-weight: 600; color: #94a3b8; background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                Job ID: ${Number(job.job_id)}
                            </span>
                        </div>
                    </a>
                </div>
            `;
        }).join('');
    }

    function applyClientJobFilters() {
        const typeVal = (document.getElementById('filter-type')?.value || 'all').toLowerCase();
        const locVal = (document.getElementById('filter-location')?.value || 'all').toLowerCase();
        const indVal = (document.getElementById('filter-industry')?.value || 'all').toLowerCase();
        const sortVal = document.getElementById('filter-sort')?.value || 'newest';
        const resetBtn = document.getElementById('filter-reset-btn');

        const isFiltered = (typeVal !== 'all' || locVal !== 'all' || indVal !== 'all' || sortVal !== 'newest');
        if (resetBtn) {
            resetBtn.style.display = isFiltered ? 'inline-block' : 'none';
        }

        let filtered = allJobs.slice();

        if (typeVal !== 'all') {
            filtered = filtered.filter(j => {
                const t = String(j.employment_type || '').toLowerCase();
                const l = String(j.location || 'remote').toLowerCase();
                if (typeVal === 'remote') return t.includes('remote') || l.includes('remote');
                return t === typeVal;
            });
        }
        if (locVal !== 'all') {
            filtered = filtered.filter(j => String(j.location || 'Remote').toLowerCase() === locVal);
        }
        if (indVal !== 'all') {
            filtered = filtered.filter(j => String(j.industry || 'General').toLowerCase() === indVal);
        }

        filtered.sort((a, b) => {
            if (sortVal === 'title_asc') return String(a.title || '').localeCompare(String(b.title || ''));
            if (sortVal === 'company_asc') return String(a.company_name || '').localeCompare(String(b.company_name || ''));
            const da = new Date(a.created_at || 0).getTime();
            const db = new Date(b.created_at || 0).getTime();
            return sortVal === 'oldest' ? da - db : db - da;
        });

        renderJobsList(filtered, allJobs.length);
    }

    async function initHomeView() {
        const mainEl = document.getElementById('main-content');
        if (!mainEl) return;

        const params = new URLSearchParams(window.location.search);
        const searchQ = (params.get('search') || '').trim();
        const searchInput = document.getElementById('hero-search-input');
        if (searchInput) {
            searchInput.value = searchQ;
        }
        const searchBanner = document.getElementById('active-search-banner');
        if (searchBanner) {
            if (searchQ) {
                searchBanner.style.display = 'flex';
                searchBanner.style.alignItems = 'center';
                searchBanner.style.gap = '0.65rem';
                searchBanner.innerHTML = `
                    <span>🔍 Showing search results for <strong style="color: #ffffff;">"${escapeHtml(searchQ)}"</strong></span>
                    <a href="/" class="btn btn-ghost btn-sm" style="padding: 0.2rem 0.6rem; font-size: 0.75rem; color: #f87171; border-color: rgba(248, 113, 113, 0.35);">✕ Clear Search</a>
                `;
            } else {
                searchBanner.style.display = 'none';
                searchBanner.innerHTML = '';
            }
        }

        const res = await window.AppApi.get('/api/public/home' + (searchQ ? `?search=${encodeURIComponent(searchQ)}` : ''));
        if (!res.ok || !res.data.success) {
            showToast('Failed to load job listings.', 'error');
            return;
        }

        allJobs = res.data.jobs || [];
        const news = res.data.news || [];
        const locations = (res.data.filters && res.data.filters.locations) || [];
        const industries = (res.data.filters && res.data.filters.industries) || [];

        const locSelect = document.getElementById('filter-location');
        if (locSelect) {
            locSelect.innerHTML = '<option value="all">📍 All Locations</option>' +
                locations.map(l => `<option value="${escapeHtml(l.toLowerCase())}">${escapeHtml(l)}</option>`).join('');
        }

        const indSelect = document.getElementById('filter-industry');
        if (indSelect) {
            indSelect.innerHTML = '<option value="all">🏢 All Industries</option>' +
                industries.map(i => `<option value="${escapeHtml(i.toLowerCase())}">${escapeHtml(i)}</option>`).join('');
        }

        const newsContainer = document.getElementById('news-carousel-container');
        if (newsContainer) {
            newsContainer.innerHTML = news.length
                ? news.map(n => `
                    <article class="card news-card-item">
                        <div>
                            <div style="display: flex; justify-content: space-between; font-size: 0.78rem; color: #94a3b8; margin-bottom: 0.45rem;">
                                <span style="color: #a78bfa; font-weight: 700;">${escapeHtml(n.source_name)}</span>
                                <span>${escapeHtml(n.time_ago || '')}</span>
                            </div>
                            <h3 style="font-size: 1rem; margin-bottom: 0.5rem;">
                                <a href="${escapeHtml(n.link)}" target="_blank" rel="noopener noreferrer" style="color: #ffffff;">${escapeHtml(n.title)}</a>
                            </h3>
                            <p class="text-muted" style="font-size: 0.85rem;">${escapeHtml(n.description || '')}</p>
                        </div>
                        <div style="margin-top: 0.85rem;">
                            <a href="${escapeHtml(n.link)}" target="_blank" rel="noopener noreferrer" style="font-size: 0.82rem; font-weight: 700;">Read Full Article →</a>
                        </div>
                    </article>
                `).join('')
                : '<p class="text-muted">No news articles available right now.</p>';
        }

        ['filter-type', 'filter-location', 'filter-industry', 'filter-sort'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', applyClientJobFilters);
        });

        const resetBtn = document.getElementById('filter-reset-btn');
        if (resetBtn) {
            resetBtn.addEventListener('click', () => {
                ['filter-type', 'filter-location', 'filter-industry'].forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.value = 'all';
                });
                const sEl = document.getElementById('filter-sort');
                if (sEl) sEl.value = 'newest';
                applyClientJobFilters();
            });
        }

        const refreshNewsBtn = document.getElementById('refresh-news-btn');
        if (refreshNewsBtn) {
            refreshNewsBtn.addEventListener('click', async () => {
                refreshNewsBtn.disabled = true;
                refreshNewsBtn.textContent = 'Refreshing…';
                const r = await window.AppApi.post('/api/news/refresh');
                refreshNewsBtn.disabled = false;
                refreshNewsBtn.textContent = '↻ Refresh News';
                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    initHomeView();
                }
            });
        }

        renderJobsList(allJobs);
    }

    /**
     * Initialize Single Job Details & Candidate Application View (job.html)
     */
    async function initJobDetailView() {
        const params = new URLSearchParams(window.location.search);
        const jobId = Number(params.get('id') || 0);
        if (!jobId) {
            window.location.href = '/';
            return;
        }

        const res = await window.AppApi.get(`/api/jobs/detail?id=${jobId}`);
        if (!res.ok || !res.data.success) {
            showToast((res.data && res.data.error) || 'Job not found.', 'error');
            setTimeout(() => { window.location.href = '/'; }, 1200);
            return;
        }

        const job = res.data.job;
        document.title = `${job.title} — PostYourJobUK`;

        const host = document.getElementById('job-detail-host');
        if (!host) return;

        host.innerHTML = `
            <div style="margin-bottom: 1.5rem;">
                <a href="/" class="btn btn-ghost btn-sm">← Back to Job Listings</a>
            </div>
            <div class="grid-2" style="grid-template-columns: 1.25fr 0.95fr; align-items: start;">
                <div>
                    <article class="card" style="margin-bottom: 1.5rem;">
                        <span class="status-badge active" style="margin-bottom: 0.75rem;">${escapeHtml(job.employment_type || 'Full-time')}</span>
                        <h1 style="font-size: 1.85rem; margin-bottom: 0.5rem;">${escapeHtml(job.title)}</h1>
                        <p style="color: #a78bfa; font-weight: 700; margin-bottom: 1rem;">
                            🏢 ${escapeHtml(job.company_name || 'Verified Employer')} &bull; 📍 ${escapeHtml(job.location || 'Remote')} &bull; 💷 ${escapeHtml(job.salary || 'Competitive')}
                        </p>
                        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 1.25rem 0;">
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.65rem;">Role Description</h2>
                        <div class="text-muted" style="white-space: pre-line; margin-bottom: 1.5rem;">${escapeHtml(job.description)}</div>
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.65rem;">Key Requirements &amp; Qualifications</h2>
                        <div class="text-muted" style="white-space: pre-line;">${escapeHtml(job.requirements)}</div>
                    </article>

                    <article class="card">
                        <h2 style="font-size: 1.1rem; margin-bottom: 0.75rem;">🏢 Employer &amp; Recruiter Business Card</h2>
                        <p><strong>Organisation:</strong> ${escapeHtml(job.company_name || 'N/A')}</p>
                        <p><strong>Hiring Contact:</strong> ${escapeHtml(job.contact_person || 'Recruitment Team')}</p>
                        ${job.company_email ? `<p><strong>Contact Email:</strong> <a href="mailto:${escapeHtml(job.company_email)}" style="color: #818cf8;">${escapeHtml(job.company_email)}</a></p>` : ''}
                        ${job.phone_number ? `<p><strong>Phone Number:</strong> ${escapeHtml(job.phone_number)}</p>` : ''}
                        <p><strong>Industry:</strong> ${escapeHtml(job.industry || 'General')}</p>
                        ${job.website_url ? `<p><strong>Website:</strong> <a href="${escapeHtml(job.website_url)}" target="_blank" rel="noopener">${escapeHtml(job.website_url)}</a></p>` : ''}
                        ${job.company_address ? `<p><strong>Headquarters:</strong> ${escapeHtml(job.company_address)}</p>` : ''}
                    </article>
                </div>

                <aside class="card" aria-labelledby="apply-heading">
                    <h2 id="apply-heading" style="font-size: 1.3rem; margin-bottom: 0.35rem;">Apply for This Position</h2>
                    <p class="text-muted" style="font-size: 0.88rem; margin-bottom: 1.25rem;">Upload your CV in PDF or DOCX format (max 5 MB). You will receive an instant email confirmation.</p>

                    <form id="candidate-apply-form" enctype="multipart/form-data">
                        <input type="hidden" name="job_id" value="${Number(job.job_id)}">
                        <input type="hidden" name="form_rendered_at" value="${Number(res.data.form_rendered_at || Math.floor(Date.now() / 1000))}">
                        <div class="sr-only" aria-hidden="true">
                            <label for="candidate_website_url">Leave this field blank</label>
                            <input type="text" id="candidate_website_url" name="candidate_website_url" tabindex="-1" autocomplete="off">
                        </div>

                        <div class="form-group">
                            <label for="app-name">Full Name *</label>
                            <input type="text" id="app-name" name="name" required minlength="2" maxlength="100" autocomplete="name">
                        </div>
                        <div class="form-group">
                            <label for="app-email">Email Address *</label>
                            <input type="email" id="app-email" name="email" required maxlength="150" autocomplete="email">
                        </div>
                        <div class="form-group">
                            <label for="app-phone">Phone Number (Optional)</label>
                            <input type="tel" id="app-phone" name="phone" maxlength="30" autocomplete="tel">
                        </div>
                        <div class="form-group">
                            <label for="app-cv">CV Document (PDF or DOCX, max 5 MB) *</label>
                            <input type="file" id="app-cv" name="cv_document" accept=".pdf,.docx" required>
                        </div>

                        <button type="submit" id="apply-submit-btn" class="btn btn-primary w-100" style="width: 100%;">Submit Application</button>
                    </form>
                </aside>
            </div>
        `;

        const form = document.getElementById('candidate-apply-form');
        if (form) {
            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                const btn = document.getElementById('apply-submit-btn');
                btn.disabled = true;
                btn.textContent = 'Uploading & Verifying CV…';

                const fd = new FormData(form);
                const applyRes = await window.AppApi.post('/api/apply', fd);

                btn.disabled = false;
                btn.textContent = 'Submit Application';

                if (applyRes.status === 409 && applyRes.data && applyRes.data.duplicate) {
                    const d = applyRes.data.duplicate_details || {};
                    const dupBody = document.getElementById('duplicate-modal-body');
                    if (dupBody) {
                        dupBody.innerHTML = `
                            We already have an active application on file for <strong>${escapeHtml(d.email || '')}</strong>
                            for the <strong>${escapeHtml(d.job_title || job.title)}</strong> role (submitted on ${escapeHtml(d.applied_at || 'recently')}).
                        `;
                    }
                    window.AppA11y.openModal('duplicate-app-modal', btn);
                    return;
                }

                if (applyRes.ok && applyRes.data.success) {
                    showToast(applyRes.data.message, 'success', 8000);
                    form.reset();
                } else {
                    showToast((applyRes.data && applyRes.data.error) || 'Application submission failed.', 'error');
                }
            });
        }
    }

    window.AppPublic = {
        initHomeView,
        initContactView,
        initJobDetailView
    };
})(window);
