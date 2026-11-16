/**
 * PostYourJobUK — Public Job Board, Static Views, Job Details & Candidate Application (public.js)
 * Zero External Dependencies | WCAG 2.1 Level AA Compliant
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;
    let allJobs = [];

    const STATIC_VIEWS = {
        '/about': `
            <section class="container section">
                <p class="about-eyebrow">Platform Architecture &amp; Mission</p>
                <h1 class="about-title">About PostYourJobUK</h1>
                <p class="about-lead">PostYourJobUK is a cloud-native, multi-tenant AI recruitment and Applicant Tracking System (ATS) engineered to eliminate demographic bias, prevent LLM hallucinations, and automate candidate communications.</p>
                <div class="stats-grid">
                    <div class="card"><h3>AI Grounding Gate</h3><p class="stat-value">100%</p></div>
                    <div class="card"><h3>Demographic Disparity</h3><p class="stat-value">0.0 pt</p></div>
                    <div class="card"><h3>Cloud Architecture</h3><p class="stat-value">4-Tier Azure</p></div>
                </div>
                <div class="grid-2">
                    <article class="card">
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Two-Stage Decoupled AI Evaluation</h2>
                        <p class="text-muted">Candidate CVs undergo regex PII redaction before factual claim extraction at temperature 0.0, verified by a deterministic Python Grounding Gate and scored via Base-100 subtractive math.</p>
                    </article>
                    <article class="card">
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Automated ATS Follow-Up Pipeline</h2>
                        <p class="text-muted">Recruiters advance candidates across Reviewed, Interviewing, Hired, and Rejected stages with 1-click automated Brevo SMTP emails embedding live employer business cards.</p>
                    </article>
                </div>
            </section>
        `,
        '/vision': `
            <section class="container section">
                <p class="vision-eyebrow">Algorithmic Fairness &amp; Transparency</p>
                <h1 class="vision-title">Our Vision for Ethical AI Recruitment</h1>
                <p class="vision-lead">We believe every candidate deserves to be evaluated strictly on verified competencies and domain experience—never on demographic proxies, name origin, or formatting style.</p>
                <div class="grid-2">
                    <article class="card">
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Zero Hallucination Guarantee</h2>
                        <p class="text-muted">Every extracted skill claim is cross-checked character-for-character against the raw CV text and guarded against job description copy-pasting.</p>
                    </article>
                    <article class="card">
                        <h2 style="font-size: 1.15rem; margin-bottom: 0.5rem;">Live Counterfactual Auditing</h2>
                        <p class="text-muted">Our Super-Admin Fairness Workspace (Tenant 9999) continuously verifies parity across 9 protected social dimensions aligned with the EU AI Act and UK Equality Act 2010.</p>
                    </article>
                </div>
            </section>
        `,
        '/terms': `
            <section class="container section">
                <p class="terms-eyebrow">Governance &amp; Data Protection</p>
                <h1 class="terms-title">Terms of Service &amp; UK GDPR Privacy Policy</h1>
                <p class="text-muted" style="margin-bottom: 1.5rem;">Aligned with UK Data Protection Act 2018, UK GDPR, EU AI Act (Annex III), and ISO/IEC 42001:2023.</p>
                <div class="card" style="margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.1rem; margin-bottom: 0.5rem;">1. Data Minimization &amp; Stage 0 PII Redaction</h2>
                    <p class="text-muted">Uploaded CVs are stored in encrypted Azure Blob Storage scoped to the hiring tenant. Prior to AI evaluation, personal identifiers (names, emails, phones, dates of birth, graduation years) are redacted.</p>
                </div>
                <div class="card" style="margin-bottom: 1.25rem;">
                    <h2 style="font-size: 1.1rem; margin-bottom: 0.5rem;">2. Multi-Tenant Isolation &amp; Erasure Rights</h2>
                    <p class="text-muted">Each recruiter organization operates within a strict BOLA-protected tenant boundary. Recruiters can permanently wipe individual or bulk candidate records and CV blobs at any time.</p>
                </div>
            </section>
        `
    };

    function renderContactView(host) {
        host.innerHTML = `
            <section class="container section">
                <p class="contact-eyebrow">Support &amp; Enterprise Inquiries</p>
                <h1 class="contact-title">Get in Touch</h1>
                <p class="contact-sub">Have questions about our AI recruitment platform, tenant onboarding, or compliance certification? Send our team a message below.</p>
                <div class="grid-2">
                    <div class="card">
                        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Send an Inquiry</h2>
                        <form id="public-contact-form">
                            <div class="form-group">
                                <label for="contact-name">Full Name *</label>
                                <input type="text" id="contact-name" name="name" required autocomplete="name">
                            </div>
                            <div class="form-group">
                                <label for="contact-email">Email Address *</label>
                                <input type="email" id="contact-email" name="email" required autocomplete="email">
                            </div>
                            <div class="form-group">
                                <label for="contact-subject">Subject</label>
                                <input type="text" id="contact-subject" name="subject" placeholder="Tenant Onboarding / General Question">
                            </div>
                            <div class="form-group">
                                <label for="contact-message">Message *</label>
                                <textarea id="contact-message" name="message" rows="5" required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Send Message</button>
                        </form>
                    </div>
                    <div class="card">
                        <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">Platform Operations</h2>
                        <p class="text-muted" style="margin-bottom: 1rem;"><strong>Email:</strong> careers@postyourjobuk.co.uk</p>
                        <p class="text-muted" style="margin-bottom: 1rem;"><strong>Headquarters:</strong> London, United Kingdom</p>
                        <p class="text-muted"><strong>Operating Hours:</strong> Monday – Friday, 09:00 – 18:00 GMT</p>
                    </div>
                </div>
            </section>
        `;

        const form = document.getElementById('public-contact-form');
        if (form) {
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
    }

    function renderJobsList(jobs) {
        const container = document.getElementById('jobs-list-container');
        const counterEl = document.getElementById('jobs-count-label');
        if (!container) return;

        if (counterEl) {
            counterEl.textContent = `Showing ${jobs.length} active opportunity${jobs.length === 1 ? '' : 's'}`;
        }

        if (!jobs.length) {
            container.innerHTML = `
                <div class="card text-center" style="padding: 2.5rem;">
                    <h3>No Matching Job Postings Found</h3>
                    <p class="text-muted" style="margin-top: 0.5rem;">Try clearing your search filters or browsing all active positions.</p>
                </div>
            `;
            return;
        }

        container.innerHTML = jobs.map(job => {
            const loc = job.location ? escapeHtml(job.location) : 'Remote';
            const salary = job.salary ? escapeHtml(job.salary) : 'Competitive';
            const empType = escapeHtml((job.employment_type || 'full-time').replace('-', ' '));
            const industry = escapeHtml(job.industry || 'General');
            const desc = escapeHtml((job.description || '').slice(0, 180)) + ((job.description || '').length > 180 ? '…' : '');

            return `
                <article class="card job-card-item" style="margin-bottom: 1.15rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
                        <div style="flex: 1; min-width: 240px;">
                            <div style="display: flex; align-items: center; gap: 0.6rem; flex-wrap: wrap; margin-bottom: 0.35rem;">
                                <h3 style="margin: 0;"><a href="/job?id=${Number(job.job_id)}" style="color: #ffffff;">${escapeHtml(job.title)}</a></h3>
                                <span class="status-badge active">${empType}</span>
                            </div>
                            <p style="color: #a78bfa; font-weight: 700; font-size: 0.92rem; margin-bottom: 0.5rem;">
                                🏢 ${escapeHtml(job.company_name || 'Verified Employer')} &bull; <span class="text-muted">${industry}</span>
                            </p>
                            <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.85rem;">${desc}</p>
                            <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.84rem; color: #cbd5e1;">
                                <span>📍 ${loc}</span>
                                <span>💷 ${salary}</span>
                                <span>🕒 ${escapeHtml(job.time_ago || 'Recently')}</span>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: flex-end; justify-content: center;">
                            <a href="/job?id=${Number(job.job_id)}" class="btn btn-primary btn-sm">View &amp; Apply →</a>
                        </div>
                    </div>
                </article>
            `;
        }).join('');
    }

    function applyClientJobFilters() {
        const typeVal = (document.getElementById('filter-type')?.value || 'all').toLowerCase();
        const locVal = (document.getElementById('filter-location')?.value || 'all').toLowerCase();
        const indVal = (document.getElementById('filter-industry')?.value || 'all').toLowerCase();
        const sortVal = document.getElementById('filter-sort')?.value || 'newest';

        let filtered = allJobs.slice();

        if (typeVal !== 'all') {
            filtered = filtered.filter(j => {
                const t = String(j.employment_type || '').toLowerCase();
                const l = String(j.location || 'remote').toLowerCase();
                if (typeVal === 'remote') return l.includes('remote');
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

        renderJobsList(filtered);
    }

    async function initHomeView() {
        const path = window.location.pathname.replace(/\/+$/, '') || '/';
        const mainEl = document.getElementById('main-content');
        if (!mainEl) return;

        if (STATIC_VIEWS[path]) {
            mainEl.innerHTML = STATIC_VIEWS[path];
            return;
        }
        if (path === '/contact') {
            renderContactView(mainEl);
            return;
        }

        const params = new URLSearchParams(window.location.search);
        const searchQ = params.get('search') || '';
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
                        <p><strong>Industry:</strong> ${escapeHtml(job.industry || 'General')}</p>
                        ${job.website_url ? `<p><strong>Website:</strong> <a href="${escapeHtml(job.website_url)}" target="_blank" rel="noopener">${escapeHtml(job.website_url)}</a></p>` : ''}
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
        initJobDetailView
    };
})(window);
