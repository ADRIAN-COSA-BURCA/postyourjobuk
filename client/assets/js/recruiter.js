/**
 * PostYourJobUK — Recruiter Workspace, Job CRUD, ATS Pipeline & 6-Chart Analytics (recruiter.js)
 * Zero External Dependencies | Full Filter/Sort Toolbars + Clickable Column Sorting | WCAG 2.1 Level AA
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;

    const VIBRANT_TITLE_COLORS = [
        '#38bdf8', // Sky Cyan
        '#34d399', // Mint Emerald
        '#a78bfa', // Lavender Violet
        '#fbbf24', // Amber Gold
        '#f472b6', // Neon Rose
        '#fb923c', // Tangerine Orange
        '#2dd4bf'  // Electric Teal
    ];

    function statusBadgeHtml(status) {
        const s = String(status || 'reviewed').toLowerCase();
        const map = {
            hired: { cls: 'hired', label: '🎉 Hired / Offered' },
            offered: { cls: 'hired', label: '🎉 Hired / Offered' },
            interviewing: { cls: 'interviewing', label: '🟢 Interviewing' },
            reviewed: { cls: 'reviewed', label: '🔍 Reviewed' },
            rejected: { cls: 'rejected', label: '🛑 Rejected' },
            pending: { cls: 'pending', label: '⏳ Pending AI' },
            new: { cls: 'reviewed', label: '🔍 Reviewed' },
            processing_failed: { cls: 'failed', label: '⚠️ Needs Review' }
        };
        const cfg = map[s] || { cls: 'reviewed', label: '🔍 Reviewed' };
        return `<span class="status-badge-flag ${cfg.cls}">${cfg.label}</span>`;
    }

    function normalizeFilterStage(status) {
        const s = String(status || 'reviewed').toLowerCase();
        if (s === 'interviewing') return 'interviewing';
        if (s === 'hired' || s === 'offered') return 'hired';
        if (s === 'rejected') return 'rejected';
        return 'reviewed';
    }

    function timeAgoLabel(dateStr) {
        if (!dateStr) return 'Recently';
        const ts = new Date(dateStr).getTime();
        if (isNaN(ts)) return dateStr;
        const diff = Math.floor((Date.now() - ts) / 1000);
        if (diff < 60) return 'just now';
        if (diff < 3600) return Math.floor(diff / 60) + ' mins ago';
        if (diff < 86400) return Math.floor(diff / 3600) + ' hours ago';
        return Math.floor(diff / 86400) + ' days ago';
    }

    /**
     * 1. Main Recruiter Dashboard (/dashboard)
     */
    async function renderDashboardHome(host) {
        const res = await window.AppApi.get('/api/dashboard');
        if (!res.ok || !res.data.success) return;

        const { tenant, stats, jobs } = res.data;
        document.title = `${tenant.company_name || 'Recruiter'} Dashboard — PostYourJobUK`;

        host.innerHTML = `
            <!-- Corporate Profile Business Card -->
            <div class="card" style="margin-bottom: 2rem; border-top: 4px solid #4f46e5; background: #111827; border: 1px solid #374151;">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                    <div>
                        <h1 style="margin: 0 0 0.35rem 0; font-size: 1.85rem; color: #ffffff;">Welcome, ${escapeHtml(tenant.company_name || 'Recruiter')}</h1>
                        <p class="text-muted" style="margin: 0; color: #94a3b8;">Your Corporate Profile Dashboard</p>
                    </div>
                    <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                        <button type="button" id="open-edit-profile-btn" class="btn btn-ghost" style="border-color: #6366f1; color: #a5b4fc;">⚙️ Update Details</button>
                        <a href="/dashboard/analytics" class="btn btn-primary">📊 View Analytics</a>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(175px, 1fr)); gap: 1rem; background: #1f2937; padding: 1rem; border-radius: 8px; border: 1px solid #374151;">
                    <div>
                        <span style="display: block; font-size: 0.82rem; color: #94a3b8; margin-bottom: 0.2rem;">Industry</span>
                        <strong style="color: #ffffff;">${escapeHtml(tenant.industry || 'Not Specified')}</strong>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.82rem; color: #94a3b8; margin-bottom: 0.2rem;">Contact Person</span>
                        <strong style="color: #ffffff;">${escapeHtml(tenant.contact_person || 'Not Specified')}</strong>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.82rem; color: #94a3b8; margin-bottom: 0.2rem;">Email Address</span>
                        <strong style="color: #ffffff; word-break: break-all;">${escapeHtml(tenant.email || 'Not Specified')}</strong>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.82rem; color: #94a3b8; margin-bottom: 0.2rem;">Phone Number</span>
                        <strong style="color: #ffffff;">${escapeHtml(tenant.phone_number || 'Not Specified')}</strong>
                    </div>
                    <div>
                        <span style="display: block; font-size: 0.82rem; color: #94a3b8; margin-bottom: 0.2rem;">Website</span>
                        ${tenant.website_url ? `<a href="${escapeHtml(tenant.website_url)}" target="_blank" rel="noopener" style="color: #818cf8; font-weight: 700;">Visit Site ↗</a>` : `<strong style="color: #ffffff;">Not Specified</strong>`}
                    </div>
                </div>

                ${tenant.company_address ? `
                    <div style="margin-top: 1.15rem; padding: 0 0.25rem;">
                        <span style="display: block; font-size: 0.82rem; color: #94a3b8; margin-bottom: 0.2rem;">Headquarters / Address</span>
                        <p style="margin: 0; font-size: 0.92rem; color: #ffffff;">${escapeHtml(tenant.company_address)}</p>
                    </div>
                ` : ''}
            </div>

            <!-- Recruiter Update Corporate Profile & Credentials Modal -->
            <div id="edit-recruiter-profile-modal" class="modal-backdrop" role="dialog" aria-modal="true" aria-labelledby="edit-profile-modal-title" aria-hidden="true">
                <div class="modal-dialog" style="max-width: 680px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem;">
                        <div>
                            <h2 id="edit-profile-modal-title" style="font-size: 1.3rem; margin: 0;">⚙️ Update Corporate Details &amp; Account Credentials</h2>
                            <p class="text-muted" style="font-size: 0.82rem; margin: 0.2rem 0 0 0;">Changes immediately reflect on your dashboard and across all Employer Business Cards on your job postings.</p>
                        </div>
                        <button type="button" class="btn btn-ghost btn-sm" onclick="window.AppA11y.closeModal('edit-recruiter-profile-modal')" aria-label="Close modal">✕</button>
                    </div>
                    <form id="recruiter-update-profile-form">
                        <div class="grid-2" style="gap: 0.75rem; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="rp-company">Company Name *</label>
                                <input type="text" id="rp-company" name="company_name" value="${escapeHtml(tenant.company_name || '')}" required>
                            </div>
                            <div class="form-group">
                                <label for="rp-email">Corporate Login &amp; Contact Email *</label>
                                <input type="email" id="rp-email" name="email" value="${escapeHtml(tenant.email || '')}" required>
                            </div>
                        </div>
                        <div class="grid-2" style="gap: 0.75rem; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="rp-contact">Contact Person</label>
                                <input type="text" id="rp-contact" name="contact_person" value="${escapeHtml(tenant.contact_person || '')}" placeholder="e.g., Sarah Jenkins (Hiring Manager)">
                            </div>
                            <div class="form-group">
                                <label for="rp-phone">Phone Number</label>
                                <input type="text" id="rp-phone" name="phone_number" value="${escapeHtml(tenant.phone_number || '')}" placeholder="e.g., +44 20 7946 0921">
                            </div>
                        </div>
                        <div class="grid-2" style="gap: 0.75rem; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="rp-industry">Industry Sector</label>
                                <input type="text" id="rp-industry" name="industry" value="${escapeHtml(tenant.industry || '')}" placeholder="e.g., Technology / Healthcare">
                            </div>
                            <div class="form-group">
                                <label for="rp-website">Website URL</label>
                                <input type="url" id="rp-website" name="website_url" value="${escapeHtml(tenant.website_url || '')}" placeholder="https://example.co.uk">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="rp-address">Headquarters / Company Address</label>
                            <textarea id="rp-address" name="company_address" rows="2" placeholder="Street, City, Postcode">${escapeHtml(tenant.company_address || '')}</textarea>
                        </div>

                        <hr style="border: none; border-top: 1px solid rgba(255,255,255,0.1); margin: 1rem 0;">
                        <p style="font-size: 0.82rem; font-weight: 700; color: #a78bfa; text-transform: uppercase; letter-spacing: 0.05em; margin: 0 0 0.65rem 0;">🔐 Security &amp; Password Recovery Settings</p>

                        <div class="grid-2" style="gap: 0.75rem; margin-bottom: 0;">
                            <div class="form-group">
                                <label for="rp-password">New Password (leave blank to keep current)</label>
                                <input type="password" id="rp-password" name="new_password" minlength="8" placeholder="Min. 8 characters" autocomplete="new-password">
                            </div>
                            <div class="form-group">
                                <label for="rp-recovery">Account Recovery Code (for Forgot Password)</label>
                                <input type="text" id="rp-recovery" name="recovery_code" value="${escapeHtml(tenant.recovery_code || '')}" placeholder="e.g., RECOV-2026">
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 0.85rem;">
                            <button type="button" class="btn btn-ghost" onclick="window.AppA11y.closeModal('edit-recruiter-profile-modal')">Cancel</button>
                            <button type="submit" id="save-recruiter-profile-btn" class="btn btn-primary">💾 Save Details &amp; Update Jobs</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- 3 Uniform KPI Stat Cards -->
            <section class="stats-grid" aria-label="Workspace Summary Metrics">
                <div class="card">
                    <h3>Total Jobs</h3>
                    <p class="stat-value">${Number(stats.total_jobs || 0)}</p>
                </div>
                <div class="card">
                    <h3>Active Jobs</h3>
                    <p class="stat-value">${Number(stats.active_jobs || 0)}</p>
                </div>
                <div class="card">
                    <h3>Total Applicants</h3>
                    <p class="stat-value">${Number(stats.total_applicants || 0)}</p>
                </div>
            </section>

            <!-- Job Postings Roster with Filter & Sort Toolbar -->
            <section class="card" aria-labelledby="jobs-table-heading">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.25rem;">
                    <h2 id="jobs-table-heading" style="font-size: 1.35rem; margin: 0;">Your Current Postings</h2>
                    <a href="/jobs/create" class="btn btn-primary">+ Create New Job</a>
                </div>

                <!-- JOB POSTINGS FILTER & SORT TOOLBAR -->
                <div class="job-filter-toolbar">
                    <div style="flex: 1 1 240px; min-width: 200px;">
                        <label for="jobSearchInput" class="sr-only">Search by job title</label>
                        <input type="text" id="jobSearchInput" placeholder="🔍 Search by job title..." class="job-filter-input">
                    </div>
                    <div class="job-filter-group">
                        <label for="jobStatusFilter" class="sr-only">Filter by status</label>
                        <select id="jobStatusFilter" class="job-filter-select">
                            <option value="all">All Statuses</option>
                            <option value="active">Active Only</option>
                            <option value="inactive">Inactive Only</option>
                        </select>

                        <label for="jobSortSelect" class="sr-only">Sort job postings</label>
                        <select id="jobSortSelect" class="job-filter-select">
                            <option value="date_desc">Posted (Newest First)</option>
                            <option value="date_asc">Posted (Oldest First)</option>
                            <option value="title_asc">Title (A - Z)</option>
                            <option value="title_desc">Title (Z - A)</option>
                            <option value="applicants_desc">Applicants (Most First)</option>
                            <option value="applicants_asc">Applicants (Least First)</option>
                        </select>

                        <span id="jobCountBadge" style="font-size: 0.8rem; color: #94a3b8; white-space: nowrap; padding-left: 0.25rem;">
                            Showing ${jobs.length} postings
                        </span>
                    </div>
                </div>

                ${jobs.length === 0 ? `
                    <p class="text-muted text-center" style="padding: 2rem 0;">No job postings detected inside this corporate tenant workspace.</p>
                ` : `
                    <div class="table-wrapper">
                        <table id="recruiter-jobs-table" class="app-table stack-table">
                            <thead>
                                <tr>
                                    <th class="sortable-th" data-sort-key="title">Title</th>
                                    <th class="sortable-th" data-sort-key="status">Status</th>
                                    <th class="sortable-th" data-sort-key="applicants" data-sort-type="number">Applicants</th>
                                    <th class="sortable-th" data-sort-key="date" data-sort-type="number">Posted</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="jobTableBody">
                                ${jobs.map((j, idx) => {
                                    const st = String(j.status || 'active').toLowerCase();
                                    const titleColor = VIBRANT_TITLE_COLORS[idx % VIBRANT_TITLE_COLORS.length];
                                    const ts = new Date(j.created_at || Date.now()).getTime() || 0;
                                    return `
                                    <tr class="job-posting-row"
                                        data-title="${escapeHtml(String(j.title || '').toLowerCase())}"
                                        data-status="${escapeHtml(st)}"
                                        data-applicants="${Number(j.applicant_count || 0)}"
                                        data-date="${ts}">
                                        <td data-label="Title">
                                            <div style="font-weight: 700; color: ${titleColor}; font-size: 1rem;">${escapeHtml(j.title)}</div>
                                            <small class="text-muted">📍 ${escapeHtml(j.location || 'Remote')} &bull; ${escapeHtml(j.employment_type || 'full-time')}</small>
                                        </td>
                                        <td data-label="Status">
                                            <span class="status-badge ${st === 'active' ? 'active' : 'suspended'}">
                                                ${st === 'active' ? '✅ Active' : '⏸️ Inactive'}
                                            </span>
                                        </td>
                                        <td data-label="Applicants">
                                            <strong style="color: #818cf8;">${Number(j.applicant_count || 0)}</strong> applicants
                                        </td>
                                        <td data-label="Posted" style="color: #9ca3af; font-size: 0.875rem;">
                                            ${escapeHtml(timeAgoLabel(j.created_at))}
                                        </td>
                                        <td data-label="Actions" style="text-align: right;">
                                            <div style="display: inline-flex; gap: 0.45rem; flex-wrap: wrap;">
                                                <a href="/applicants?job_id=${Number(j.job_id)}" class="btn btn-sm btn-ghost" style="color: #38bdf8; border-color: #38bdf8;">👥 Applicants</a>
                                                <a href="/jobs/edit?id=${Number(j.job_id)}" class="btn btn-sm btn-ghost" style="color: #10b981; border-color: #10b981;">✏️ Edit</a>
                                                <button type="button" class="btn btn-sm btn-ghost js-delete-job" style="color: #ef4444; border-color: #ef4444;" data-job-id="${Number(j.job_id)}" data-job-title="${escapeHtml(j.title)}">🗑️ Delete</button>
                                            </div>
                                        </td>
                                    </tr>`;
                                }).join('')}
                                <tr id="jobNoResultsRow" style="display: none;">
                                    <td colspan="5" style="text-align: center; color: #94a3b8; padding: 2rem;">No job postings match your search or filter criteria.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `}
            </section>
        `;

        window.AppA11y.initSortableTable(document.getElementById('recruiter-jobs-table'));

        // Wire Recruiter Update Details Modal
        const openProfileBtn = document.getElementById('open-edit-profile-btn');
        if (openProfileBtn) {
            openProfileBtn.addEventListener('click', () => {
                window.AppA11y.openModal('edit-recruiter-profile-modal', openProfileBtn);
            });
        }

        const profileForm = document.getElementById('recruiter-update-profile-form');
        if (profileForm) {
            profileForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const saveBtn = document.getElementById('save-recruiter-profile-btn');
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Saving…';
                }
                const f = e.target;
                const r = await window.AppApi.post('/api/dashboard/profile', {
                    company_name: f.company_name.value.trim(),
                    email: f.email.value.trim(),
                    contact_person: f.contact_person.value.trim(),
                    phone_number: f.phone_number.value.trim(),
                    industry: f.industry.value.trim(),
                    website_url: f.website_url.value.trim(),
                    company_address: f.company_address.value.trim(),
                    recovery_code: f.recovery_code.value.trim(),
                    new_password: f.new_password.value
                });
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = '💾 Save Details & Update Jobs';
                }
                if (r.ok && r.data.success) {
                    window.AppA11y.closeModal('edit-recruiter-profile-modal');
                    showToast(r.data.message, 'success');
                    renderDashboardHome(host);
                } else {
                    showToast((r.data && r.data.error) || 'Could not update profile.', 'error');
                }
            });
        }

        // Wire Job Filter & Sort Toolbar
        const searchInput = document.getElementById('jobSearchInput');
        const statusFilter = document.getElementById('jobStatusFilter');
        const sortSelect = document.getElementById('jobSortSelect');
        const countBadge = document.getElementById('jobCountBadge');
        const tableBody = document.getElementById('jobTableBody');
        const noResultsRow = document.getElementById('jobNoResultsRow');

        if (tableBody) {
            const allRows = Array.from(tableBody.querySelectorAll('tr.job-posting-row'));
            const applyJobFilters = () => {
                const query = (searchInput?.value || '').trim().toLowerCase();
                const statusVal = statusFilter?.value || 'all';
                const sortVal = sortSelect?.value || 'date_desc';

                const visibleRows = allRows.filter(row => {
                    const title = row.getAttribute('data-title') || '';
                    const status = row.getAttribute('data-status') || '';
                    if (query && !title.includes(query)) return false;
                    if (statusVal !== 'all' && status !== statusVal) return false;
                    return true;
                });

                visibleRows.sort((a, b) => {
                    const dateA = parseInt(a.getAttribute('data-date') || '0', 10);
                    const dateB = parseInt(b.getAttribute('data-date') || '0', 10);
                    const appA = parseInt(a.getAttribute('data-applicants') || '0', 10);
                    const appB = parseInt(b.getAttribute('data-applicants') || '0', 10);
                    const titleA = a.getAttribute('data-title') || '';
                    const titleB = b.getAttribute('data-title') || '';

                    switch (sortVal) {
                        case 'date_desc': return dateB - dateA;
                        case 'date_asc': return dateA - dateB;
                        case 'title_asc': return titleA.localeCompare(titleB);
                        case 'title_desc': return titleB.localeCompare(titleA);
                        case 'applicants_desc': return appB - appA;
                        case 'applicants_asc': return appA - appB;
                        default: return dateB - dateA;
                    }
                });

                allRows.forEach(r => { r.style.display = 'none'; });
                visibleRows.forEach(r => {
                    r.style.display = '';
                    tableBody.appendChild(r);
                });

                if (noResultsRow) {
                    tableBody.appendChild(noResultsRow);
                    noResultsRow.style.display = visibleRows.length === 0 ? '' : 'none';
                }
                if (countBadge) {
                    countBadge.textContent = `Showing ${visibleRows.length} of ${allRows.length} postings`;
                }
            };

            searchInput?.addEventListener('input', applyJobFilters);
            statusFilter?.addEventListener('change', applyJobFilters);
            sortSelect?.addEventListener('change', applyJobFilters);
        }

        host.querySelectorAll('.js-delete-job').forEach(btn => {
            btn.addEventListener('click', async () => {
                const jobId = Number(btn.dataset.jobId);
                const title = btn.dataset.jobTitle;
                if (!window.confirm(`Are you sure you want to delete job posting "${title}"?`)) return;
                const r = await window.AppApi.post('/api/jobs/delete', { id: jobId });
                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    renderDashboardHome(host);
                } else {
                    showToast((r.data && r.data.error) || 'Could not delete job.', 'error');
                }
            });
        });
    }

    /**
     * 2. Recruiter 6-Chart Analytics Suite (/dashboard/analytics)
     */
    async function renderRecruiterAnalytics(host) {
        const res = await window.AppApi.get('/api/dashboard/analytics');
        if (!res.ok || !res.data.success) return;

        const d = res.data;
        const stats = d.stats || {};
        const funnel = d.funnel_data || {};
        const trend = d.trend_data || { labels: [], data: [] };
        const topJobs = d.top_jobs || [];
        const scoreDist = d.score_distribution || {};
        const empBreak = d.employment_breakdown || { labels: [], data: [] };
        const topCands = d.top_candidates || [];

        document.title = 'Recruiter Analytics Dashboard — PostYourJobUK';

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.85rem;">Analytics Dashboard</h1>
                    <p class="text-muted" style="margin-top: 0.25rem;">Executive recruitment &amp; AI talent intelligence for <strong>${escapeHtml(d.tenant?.company_name || 'Recruiter')}</strong></p>
                </div>
                <a href="/dashboard" class="btn btn-ghost">← Back to Dashboard</a>
            </div>

            <!-- 1. Top 3 Uniform KPI Cards -->
            <section class="stats-grid">
                <div class="card"><h3>Total Jobs</h3><p class="stat-value">${Number(stats.total_jobs || 0)}</p></div>
                <div class="card"><h3>Active Jobs</h3><p class="stat-value">${Number(stats.active_jobs || 0)}</p></div>
                <div class="card"><h3>Total Applicants</h3><p class="stat-value">${Number(stats.total_applicants || 0)}</p></div>
            </section>

            <!-- 2. Row 1: Top Jobs Bar + AI Match Quality Line -->
            <div class="analytics-grid-2">
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">📊 Top Jobs by Applicant Volume</h2>
                    <p class="analytics-card-subtitle">Each job posting is color-coded by candidate application volume.</p>
                    <div style="height: 270px;"><canvas id="rec-top-jobs-canvas"></canvas></div>
                </div>
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">📈 AI Match Quality by Job Posting</h2>
                    <p class="analytics-card-subtitle">Compares Average Candidate Match (%) vs. Top Candidate Score (%) per role.</p>
                    <div style="height: 270px;"><canvas id="rec-quality-line-canvas"></canvas></div>
                </div>
            </div>

            <!-- 3. Row 2: ATS Funnel Doughnut + Submission Date Pie -->
            <div class="analytics-grid-2">
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🎯 ATS Recruitment Pipeline</h2>
                    <p class="analytics-card-subtitle">Live candidate distribution across your 4 hiring stages.</p>
                    <div style="height: 230px;"><canvas id="rec-funnel-canvas"></canvas></div>
                    <div class="pipeline-mini-pills">
                        <div class="pipeline-mini-pill"><span class="pill-label">🔍 Reviewed</span><span class="pill-val" style="color: #818cf8;">${Number(funnel.reviewed || 0)}</span></div>
                        <div class="pipeline-mini-pill"><span class="pill-label">🟢 Interviewing</span><span class="pill-val" style="color: #38bdf8;">${Number(funnel.interviewing || 0)}</span></div>
                        <div class="pipeline-mini-pill"><span class="pill-label">🎉 Hired</span><span class="pill-val" style="color: #10b981;">${Number(funnel.hired || 0)}</span></div>
                        <div class="pipeline-mini-pill"><span class="pill-label">🛑 Rejected</span><span class="pill-val" style="color: #f87171;">${Number(funnel.rejected || 0)}</span></div>
                    </div>
                </div>
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🥧 Applications by Submission Date</h2>
                    <p class="analytics-card-subtitle">Proportional breakdown of candidate applications across active dates.</p>
                    <div style="height: 280px;"><canvas id="rec-trend-pie-canvas"></canvas></div>
                </div>
            </div>

            <!-- 4. Row 3: AI Compatibility Tiers Pie + Employment Type Doughnut -->
            <div class="analytics-grid-2">
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🧠 Candidate AI Compatibility Tiers</h2>
                    <p class="analytics-card-subtitle">Segments applicants into Strong (≥80%), Qualified (50%–79%), and Low (&lt;50%) tiers.</p>
                    <div style="height: 260px;"><canvas id="rec-tiers-pie-canvas"></canvas></div>
                </div>
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">💼 Activity by Employment Type</h2>
                    <p class="analytics-card-subtitle">Distribution across Full-time, Contract, Part-time, and Internship roles.</p>
                    <div style="height: 260px;"><canvas id="rec-emp-doughnut-canvas"></canvas></div>
                </div>
            </div>

            <!-- 5. Row 4: Top 5 Candidates Leaderboard (with Clickable Column Sorting) -->
            <div class="card">
                <h2 style="font-size: 1.2rem; margin-bottom: 0.35rem;">🏆 Top 5 Highest-Scoring Candidates Across All Jobs</h2>
                <p class="text-muted" style="font-size: 0.84rem; margin-bottom: 1rem;">Quick-action leaderboard of your strongest AI-verified talent pool. Click any column header to sort.</p>
                ${topCands.length === 0 ? `<p class="text-muted">No evaluated candidates yet.</p>` : `
                    <div class="table-wrapper">
                        <table id="rec-top-cands-table" class="app-table stack-table">
                            <thead>
                                <tr>
                                    <th class="sortable-th" data-sort-key="rank" data-sort-type="number">Rank</th>
                                    <th class="sortable-th" data-sort-key="name">Candidate</th>
                                    <th class="sortable-th" data-sort-key="role">Applied Position</th>
                                    <th class="sortable-th" data-sort-key="score" data-sort-type="number">AI Match Score</th>
                                    <th class="sortable-th" data-sort-key="stage">ATS Stage</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${topCands.map((c, i) => `
                                    <tr data-rank="${i + 1}" data-name="${escapeHtml(c.name)}" data-role="${escapeHtml(c.job_title)}" data-score="${Number(c.ai_score || 0)}" data-stage="${escapeHtml(c.status)}">
                                        <td data-label="Rank"><strong style="color: #f59e0b;">#${i + 1}</strong></td>
                                        <td data-label="Candidate">
                                            <div style="font-weight: 700;">${escapeHtml(c.name)}</div>
                                            <small class="text-muted">${escapeHtml(c.email)}</small>
                                        </td>
                                        <td data-label="Applied Position">${escapeHtml(c.job_title)}</td>
                                        <td data-label="AI Match Score"><strong style="color: #10b981;">${Number(c.ai_score || 0)}%</strong></td>
                                        <td data-label="ATS Stage">${statusBadgeHtml(c.status)}</td>
                                        <td data-label="Action" style="text-align: right;">
                                            <a href="/applicant/view?id=${Number(c.applicant_id)}" class="btn btn-ghost btn-sm">👁️ View Profile</a>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `}
            </div>
        `;

        window.AppA11y.initSortableTable(document.getElementById('rec-top-cands-table'));

        const jobLabels = topJobs.length ? topJobs.map(j => j.title) : ['No Jobs'];
        window.AppCharts.renderHorizontalBar('rec-top-jobs-canvas', {
            title: 'Top Jobs by Applicant Volume',
            labels: jobLabels,
            datasets: [{
                label: 'Applicants',
                data: topJobs.length ? topJobs.map(j => Number(j.applicant_count || 0)) : [0],
                colors: window.AppCharts.PALETTE
            }]
        });

        window.AppCharts.renderLineChart('rec-quality-line-canvas', {
            title: 'AI Match Quality by Job Posting',
            labels: jobLabels,
            yMax: 100,
            ySuffix: '%',
            datasets: [
                { label: 'Top Candidate (%)', data: topJobs.length ? topJobs.map(j => Number(j.max_score || 0)) : [0], color: '#10b981' },
                { label: 'Average Match (%)', data: topJobs.length ? topJobs.map(j => Number(j.avg_score || 0)) : [0], color: '#38bdf8' }
            ]
        });

        window.AppCharts.renderDoughnut('rec-funnel-canvas', {
            title: 'ATS Recruitment Pipeline',
            labels: ['Reviewed', 'Interviewing', 'Hired', 'Rejected'],
            values: [funnel.reviewed || 0, funnel.interviewing || 0, funnel.hired || 0, funnel.rejected || 0],
            colors: ['#818cf8', '#38bdf8', '#10b981', '#ef4444']
        });

        window.AppCharts.renderPie('rec-trend-pie-canvas', {
            title: 'Applications by Submission Date',
            labels: trend.labels && trend.labels.length ? trend.labels : ['No Activity'],
            values: trend.data && trend.data.length ? trend.data : [0],
            colors: window.AppCharts.PALETTE
        });

        window.AppCharts.renderPie('rec-tiers-pie-canvas', {
            title: 'Candidate AI Compatibility Tiers',
            labels: Object.keys(scoreDist),
            values: Object.values(scoreDist),
            colors: ['#10b981', '#f59e0b', '#ef4444']
        });

        window.AppCharts.renderDoughnut('rec-emp-doughnut-canvas', {
            title: 'Activity by Employment Type',
            labels: empBreak.labels && empBreak.labels.length ? empBreak.labels : ['Full-time'],
            values: empBreak.data && empBreak.data.length ? empBreak.data : [0],
            colors: ['#38bdf8', '#a855f7', '#f59e0b', '#10b981']
        });
    }

    /**
     * 3. Job Create & Edit Views (/jobs/create & /jobs/edit?id=X)
     */
    async function renderJobForm(host, isEdit = false) {
        const params = new URLSearchParams(window.location.search);
        const jobId = Number(params.get('id') || 0);
        let job = { title: '', location: '', salary: '', employment_type: 'full-time', status: 'active', description: '', requirements: '' };

        if (isEdit) {
            const res = await window.AppApi.get(`/api/jobs/detail?id=${jobId}&mode=edit`);
            if (!res.ok || !res.data.success) {
                showToast((res.data && res.data.error) || 'Job not found.', 'error');
                window.location.href = '/dashboard';
                return;
            }
            job = res.data.job;
        }

        document.title = (isEdit ? `Edit ${job.title}` : 'Create Job Posting') + ' — PostYourJobUK';

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h1 style="font-size: 1.75rem; margin: 0;">${isEdit ? '✏️ Edit Job Specification' : '✨ Create New Job Posting'}</h1>
                <a href="/dashboard" class="btn btn-ghost">← Back to Dashboard</a>
            </div>
            <div class="card">
                <form id="recruiter-job-form">
                    <input type="hidden" name="job_id" value="${Number(job.job_id || 0)}">
                    <div class="grid-2" style="margin-bottom: 0;">
                        <div class="form-group">
                            <label for="jf-title">Position Title *</label>
                            <input type="text" id="jf-title" name="title" required minlength="5" value="${escapeHtml(job.title)}">
                        </div>
                        <div class="form-group">
                            <label for="jf-location">Location (Leave blank for Remote)</label>
                            <input type="text" id="jf-location" name="location" value="${escapeHtml(job.location || '')}">
                        </div>
                    </div>
                    <div class="grid-3" style="margin-bottom: 0;">
                        <div class="form-group">
                            <label for="jf-salary">Compensation / Salary Range</label>
                            <input type="text" id="jf-salary" name="salary" value="${escapeHtml(job.salary || '')}" placeholder="e.g. £55,000 - £70,000">
                        </div>
                        <div class="form-group">
                            <label for="jf-type">Employment Type</label>
                            <select id="jf-type" name="employment_type">
                                ${['full-time', 'part-time', 'contract', 'internship'].map(t => `
                                    <option value="${t}" ${job.employment_type === t ? 'selected' : ''}>${t.replace('-', ' ').toUpperCase()}</option>
                                `).join('')}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="jf-status">Listing Status</label>
                            <select id="jf-status" name="status">
                                ${['active', 'inactive', 'closed'].map(s => `
                                    <option value="${s}" ${job.status === s ? 'selected' : ''}>${s.toUpperCase()}</option>
                                `).join('')}
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="jf-desc">Full Role Description (min 50 characters) *</label>
                        <textarea id="jf-desc" name="description" rows="6" required minlength="50">${escapeHtml(job.description)}</textarea>
                    </div>
                    <div class="form-group">
                        <label for="jf-req">Required Technical Skills &amp; Experience (Used by AI Grounding Gate) *</label>
                        <textarea id="jf-req" name="requirements" rows="5" required>${escapeHtml(job.requirements)}</textarea>
                    </div>
                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <a href="/dashboard" class="btn btn-ghost">Cancel</a>
                        <button type="submit" class="btn btn-primary">${isEdit ? 'Save Changes' : 'Publish Job Posting'}</button>
                    </div>
                </form>
            </div>
        `;

        document.getElementById('recruiter-job-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const f = e.target;
            const payload = {
                job_id: Number(f.job_id.value || 0),
                title: f.title.value.trim(),
                location: f.location.value.trim(),
                salary: f.salary.value.trim(),
                employment_type: f.employment_type.value,
                status: f.status.value,
                description: f.description.value.trim(),
                requirements: f.requirements.value.trim()
            };
            const endpoint = isEdit ? '/api/jobs/update' : '/api/jobs/store';
            const r = await window.AppApi.post(endpoint, payload);
            if (r.ok && r.data.success) {
                showToast(r.data.message, 'success');
                setTimeout(() => { window.location.href = '/dashboard'; }, 700);
            } else {
                showToast((r.data && r.data.error) || 'Validation error.', 'error');
            }
        });
    }

    /**
     * 4. Applicants Roster with Full Filter/Sort Toolbar & Clickable Column Headers (/applicants?job_id=X)
     */
    async function renderApplicantsList(host) {
        const params = new URLSearchParams(window.location.search);
        const jobId = Number(params.get('job_id') || params.get('id') || 0);
        const res = await window.AppApi.get(`/api/applicants?job_id=${jobId}`);
        if (!res.ok || !res.data.success) return;

        const { job, applicants, stats } = res.data;
        document.title = `Applicants: ${job.title} — PostYourJobUK`;

        host.innerHTML = `
            <div style="margin-bottom: 1rem;">
                <a href="/dashboard" class="btn btn-ghost btn-sm">← Back to Dashboard</a>
            </div>

            <div class="card" style="margin-bottom: 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.75rem;">👥 Applications for: ${escapeHtml(job.title)}</h1>
                    <div style="margin-top: 0.35rem;">
                        Status:
                        <span class="status-badge ${job.status === 'active' ? 'active' : 'suspended'}">
                            ${job.status === 'active' ? '✅ Active' : '⏸️ Inactive'}
                        </span>
                    </div>
                </div>
            </div>

            <!-- 4 Uniform KPI Stage Cards (with ⚠️ Wipe All button inside Card 1) -->
            <section class="app-stats-grid" aria-label="Job Pipeline Counters">
                <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                        <span class="form-label">Total Applicants</span>
                        <p class="stat-value" id="kpi-total">${Number(stats.total_applicants || 0)}</p>
                    </div>
                    ${applicants.length > 0 ? `
                        <button type="button" id="wipe-all-cands-btn" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.14); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); border-radius: 6px; padding: 0.35rem 0.65rem; font-weight: 700; font-size: 0.74rem;">
                            ⚠️ Wipe All
                        </button>
                    ` : ''}
                </div>
                <div class="card"><span class="form-label">Interviewing</span><p class="stat-value" id="kpi-interviewing">${Number(stats.interviewing_count || 0)}</p></div>
                <div class="card"><span class="form-label">Hired / Offered</span><p class="stat-value" id="kpi-hired">${Number(stats.hired_count || 0)}</p></div>
                <div class="card"><span class="form-label">Rejected</span><p class="stat-value" id="kpi-rejected">${Number(stats.rejected_count || 0)}</p></div>
            </section>

            <!-- Balanced 4-Column Desktop Applicants Card with Filter & Sort Toolbar -->
            <section class="card">
                <!-- APPLICANTS FILTER & SORT TOOLBAR -->
                <div class="filter-toolbar">
                    <div style="flex: 1 1 200px; min-width: 180px;">
                        <label for="candidateSearchInput" class="sr-only">Search candidate name or email</label>
                        <input type="text" id="candidateSearchInput" placeholder="🔍 Search candidate name or email..." class="filter-input">
                    </div>
                    <div class="filter-toolbar-group">
                        <label for="statusFilterSelect" class="sr-only">Filter by pipeline stage</label>
                        <select id="statusFilterSelect" class="filter-select">
                            <option value="all">All Pipeline Stages</option>
                            <option value="reviewed">🔍 Reviewed / Pending</option>
                            <option value="interviewing">🟢 Interviewing</option>
                            <option value="hired">🎉 Hired / Offered</option>
                            <option value="rejected">🛑 Rejected</option>
                        </select>

                        <label for="scoreFilterSelect" class="sr-only">Filter by AI match score</label>
                        <select id="scoreFilterSelect" class="filter-select">
                            <option value="all">All Match Scores</option>
                            <option value="80">Score ≥ 80% (High Match)</option>
                            <option value="50">Score ≥ 50% (Qualified)</option>
                            <option value="low">Score &lt; 50% (Low / Non-Fit)</option>
                        </select>

                        <label for="limitFilterSelect" class="sr-only">Limit displayed candidates</label>
                        <select id="limitFilterSelect" class="filter-select">
                            <option value="all">Show All</option>
                            <option value="5">Top 5 Candidates</option>
                            <option value="10">Top 10 Candidates</option>
                        </select>

                        <label for="candidateSortSelect" class="sr-only">Sort candidates</label>
                        <select id="candidateSortSelect" class="filter-select">
                            <option value="score_desc">Score (Highest First)</option>
                            <option value="score_asc">Score (Lowest First)</option>
                            <option value="date_desc">Newest Submission</option>
                            <option value="date_asc">Oldest Submission</option>
                            <option value="name_asc">Name (A - Z)</option>
                            <option value="name_desc">Name (Z - A)</option>
                        </select>

                        <span id="candidateCountBadge" style="font-size: 0.78rem; color: #94a3b8; white-space: nowrap; padding-left: 0.25rem;">
                            Showing ${applicants.length} candidates
                        </span>
                    </div>
                </div>

                ${applicants.length === 0 ? `<p class="text-muted text-center" style="padding: 2rem 0;">No applications received for this job position yet.</p>` : `
                    <div class="table-wrapper">
                        <table id="applicants-roster-table" class="app-table stack-table">
                            <thead>
                                <tr>
                                    <th class="sortable-th" data-sort-key="score" data-sort-type="number" style="width: 110px;">Match</th>
                                    <th class="sortable-th" data-sort-key="name" style="width: 34%;">Candidate Profile</th>
                                    <th class="sortable-th" data-sort-key="status" style="width: 32%;">ATS Pipeline &amp; Follow-Up</th>
                                    <th style="width: 24%; text-align: right;">Profile &amp; CV Actions</th>
                                </tr>
                            </thead>
                            <tbody id="applicantTableBody">
                                ${applicants.map(a => {
                                    const rawSt = String(a.status || 'reviewed').toLowerCase();
                                    const filterStage = normalizeFilterStage(rawSt);
                                    const score = Number(a.ai_score || 0);
                                    const scoreColor = score >= 80 ? '#10b981' : (score >= 50 ? '#f59e0b' : '#ef4444');
                                    const ts = new Date(a.applied_at || a.created_at || Date.now()).getTime() || 0;
                                    const dateFormatted = new Date(ts).toLocaleDateString('en-GB', { month: 'short', day: '2-digit', year: 'numeric' });
                                    const cleanSummary = String(a.ai_summary || '').replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();

                                    return `
                                    <tr class="applicant-row"
                                        data-score="${score}"
                                        data-status="${escapeHtml(filterStage)}"
                                        data-name="${escapeHtml(String(a.name || '').toLowerCase())}"
                                        data-email="${escapeHtml(String(a.email || '').toLowerCase())}"
                                        data-date="${ts}">
                                        
                                        <!-- Column 1: Match Score & Date -->
                                        <td data-label="Match Index">
                                            <div>
                                                <span style="font-weight: 800; font-size: 1.2rem; color: ${scoreColor}; display: block; line-height: 1.2;">${score}%</span>
                                                <span style="font-size: 0.74rem; color: #9ca3af; display: block; margin-top: 0.2rem;">${escapeHtml(dateFormatted)}</span>
                                            </div>
                                        </td>

                                        <!-- Column 2: Candidate Profile & Compact Summary Preview -->
                                        <td data-label="Candidate Profile">
                                            <div>
                                                <strong style="font-size: 1.02rem; color: #ffffff; display: block; line-height: 1.3;">${escapeHtml(a.name)}</strong>
                                                <span style="font-size: 0.82rem; color: #a5b4fc; display: block; margin: 0.15rem 0 0.35rem 0; word-break: break-all;">${escapeHtml(a.email)}</span>
                                                ${cleanSummary ? `
                                                    <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0; line-height: 1.38; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; max-width: 320px;" title="${escapeHtml(cleanSummary)}">
                                                        ${escapeHtml(cleanSummary.slice(0, 140))}${cleanSummary.length > 140 ? '…' : ''}
                                                    </p>
                                                ` : ''}
                                            </div>
                                        </td>

                                        <!-- Column 3: Current Stage + Horizontal ATS Follow-Up Buttons -->
                                        <td data-label="ATS Pipeline & Follow-Up">
                                            <div>
                                                <div style="margin-bottom: 0.45rem;">${statusBadgeHtml(rawSt)}</div>
                                                <div style="display: inline-flex; gap: 0.38rem; flex-wrap: wrap;">
                                                    <button type="button" class="pipeline-btn pipeline-btn-interview js-ats-btn" data-id="${Number(a.applicant_id)}" data-name="${escapeHtml(a.name)}" data-status="interviewing" ${rawSt === 'interviewing' ? 'disabled' : ''}>🟢 Interview</button>
                                                    <button type="button" class="pipeline-btn pipeline-btn-offer js-ats-btn" data-id="${Number(a.applicant_id)}" data-name="${escapeHtml(a.name)}" data-status="hired" ${rawSt === 'hired' ? 'disabled' : ''}>🎉 Offer</button>
                                                    <button type="button" class="pipeline-btn pipeline-btn-reject js-ats-btn" data-id="${Number(a.applicant_id)}" data-name="${escapeHtml(a.name)}" data-status="rejected" ${rawSt === 'rejected' ? 'disabled' : ''}>🛑 Reject</button>
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Column 4: Profile & CV Actions -->
                                        <td data-label="Profile & CV Actions" style="text-align: right;">
                                            <div style="display: inline-flex; gap: 0.4rem; flex-wrap: wrap; justify-content: flex-end;">
                                                <a href="/applicant/view?id=${Number(a.applicant_id)}" class="btn btn-sm btn-ghost" style="color: #38bdf8; border-color: #38bdf8;">👁️ Overview</a>
                                                <a href="/api/applicants/download?id=${Number(a.applicant_id)}" class="btn btn-sm btn-ghost" style="color: #10b981; border-color: #10b981;">📥 CV</a>
                                                <button type="button" class="btn btn-sm btn-ghost js-del-cand" style="color: #ef4444; border-color: #ef4444;" data-id="${Number(a.applicant_id)}" aria-label="Delete ${escapeHtml(a.name)}">🗑️ Delete</button>
                                            </div>
                                        </td>
                                    </tr>`;
                                }).join('')}
                                <tr id="noResultsFilterRow" style="display: none;">
                                    <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">No candidates match the selected filter criteria.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                `}
            </section>
        `;

        window.AppA11y.initSortableTable(document.getElementById('applicants-roster-table'));

        // Wire Applicants Filter & Sort Toolbar
        const searchInput = document.getElementById('candidateSearchInput');
        const statusFilter = document.getElementById('statusFilterSelect');
        const scoreFilter = document.getElementById('scoreFilterSelect');
        const limitFilter = document.getElementById('limitFilterSelect');
        const sortSelect = document.getElementById('candidateSortSelect');
        const countBadge = document.getElementById('candidateCountBadge');
        const tableBody = document.getElementById('applicantTableBody');
        const noResultsRow = document.getElementById('noResultsFilterRow');

        if (tableBody) {
            const allRows = Array.from(tableBody.querySelectorAll('tr.applicant-row'));
            const applyApplicantFilters = () => {
                const query = (searchInput?.value || '').trim().toLowerCase();
                const statusVal = statusFilter?.value || 'all';
                const scoreVal = scoreFilter?.value || 'all';
                const limitVal = limitFilter?.value || 'all';
                const sortVal = sortSelect?.value || 'score_desc';

                const visibleRows = allRows.filter(row => {
                    const name = row.getAttribute('data-name') || '';
                    const email = row.getAttribute('data-email') || '';
                    const status = row.getAttribute('data-status') || 'reviewed';
                    const score = parseInt(row.getAttribute('data-score') || '0', 10);

                    if (query && !name.includes(query) && !email.includes(query)) return false;
                    if (statusVal !== 'all' && status !== statusVal) return false;
                    if (scoreVal === '80' && score < 80) return false;
                    if (scoreVal === '50' && score < 50) return false;
                    if (scoreVal === 'low' && score >= 50) return false;
                    return true;
                });

                visibleRows.sort((a, b) => {
                    const scoreA = parseInt(a.getAttribute('data-score') || '0', 10);
                    const scoreB = parseInt(b.getAttribute('data-score') || '0', 10);
                    const dateA = parseInt(a.getAttribute('data-date') || '0', 10);
                    const dateB = parseInt(b.getAttribute('data-date') || '0', 10);
                    const nameA = a.getAttribute('data-name') || '';
                    const nameB = b.getAttribute('data-name') || '';

                    switch (sortVal) {
                        case 'score_desc': return scoreB - scoreA;
                        case 'score_asc':  return scoreA - scoreB;
                        case 'date_desc':  return dateB - dateA;
                        case 'date_asc':   return dateA - dateB;
                        case 'name_asc':   return nameA.localeCompare(nameB);
                        case 'name_desc':  return nameB.localeCompare(nameA);
                        default:           return scoreB - scoreA;
                    }
                });

                allRows.forEach(r => { r.style.display = 'none'; });

                let displayLimit = visibleRows.length;
                if (limitVal === '5') displayLimit = Math.min(5, visibleRows.length);
                if (limitVal === '10') displayLimit = Math.min(10, visibleRows.length);

                const rowsToDisplay = visibleRows.slice(0, displayLimit);
                rowsToDisplay.forEach(r => {
                    r.style.display = '';
                    tableBody.appendChild(r);
                });

                if (noResultsRow) {
                    tableBody.appendChild(noResultsRow);
                    noResultsRow.style.display = rowsToDisplay.length === 0 ? '' : 'none';
                }
                if (countBadge) {
                    countBadge.textContent = `Showing ${rowsToDisplay.length} of ${allRows.length} candidates`;
                }
            };

            searchInput?.addEventListener('input', applyApplicantFilters);
            statusFilter?.addEventListener('change', applyApplicantFilters);
            scoreFilter?.addEventListener('change', applyApplicantFilters);
            limitFilter?.addEventListener('change', applyApplicantFilters);
            sortSelect?.addEventListener('change', applyApplicantFilters);
        }

        // Wire ATS Pipeline Buttons
        host.querySelectorAll('.js-ats-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const applicantId = Number(btn.dataset.id);
                const candName = btn.dataset.name || 'Candidate';
                const newStatus = btn.dataset.status;

                const confirmMsg = newStatus === 'interviewing'
                    ? `Invite ${candName} to Interview? This will automatically send an official Interview Invitation email with your recruiter contact details.`
                    : (newStatus === 'hired'
                        ? `Extend Job Offer to ${candName}? This will automatically send an official Congratulatory Job Offer email.`
                        : `Mark ${candName} as Rejected? (Silent status update — no email will be sent)`);

                if (!window.confirm(confirmMsg)) return;

                btn.disabled = true;
                const r = await window.AppApi.post('/api/applicants/status', {
                    applicant_id: applicantId,
                    job_id: jobId,
                    status: newStatus
                });

                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    renderApplicantsList(host);
                } else {
                    btn.disabled = false;
                    showToast((r.data && r.data.error) || 'Failed to update status.', 'error');
                }
            });
        });

        // Wire Single Delete
        host.querySelectorAll('.js-del-cand').forEach(btn => {
            btn.addEventListener('click', async () => {
                const applicantId = Number(btn.dataset.id);
                if (!window.confirm('Are you sure you want to permanently delete this candidate and their CV? This cannot be undone.')) return;
                const r = await window.AppApi.post('/api/applicants/delete', { applicant_id: applicantId, job_id: jobId });
                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    renderApplicantsList(host);
                }
            });
        });

        // Wire Wipe All
        const wipeBtn = document.getElementById('wipe-all-cands-btn');
        if (wipeBtn) {
            wipeBtn.addEventListener('click', async () => {
                if (!window.confirm('CRITICAL WARNING: Are you absolutely sure you want to delete ALL candidates and their CVs for this job? This will wipe cloud storage and CANNOT be undone.')) return;
                const r = await window.AppApi.post('/api/applicants/delete-all', { job_id: jobId });
                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    renderApplicantsList(host);
                }
            });
        }
    }

    /**
     * 5. Single Candidate Profile & Grounded AI Evaluation View (/applicant/view?id=X)
     */
    async function renderApplicantDetail(host) {
        const params = new URLSearchParams(window.location.search);
        const id = Number(params.get('id') || 0);
        const res = await window.AppApi.get(`/api/applicants/detail?id=${id}`);
        if (!res.ok || !res.data.success) return;

        const { applicant, job, summary_sections } = res.data;
        const st = String(applicant.status || 'new').toLowerCase();
        document.title = `Candidate Profile: ${applicant.name} — PostYourJobUK`;

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.8rem;">${escapeHtml(applicant.name)}</h1>
                    <p class="text-muted" style="margin-top: 0.25rem;">
                        Applied for <strong>${escapeHtml(job?.title || 'Role')}</strong> &bull; ✉️ ${escapeHtml(applicant.email)}
                        ${applicant.phone ? ` &bull; 📞 ${escapeHtml(applicant.phone)}` : ''}
                    </p>
                </div>
                <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                    <a href="/api/applicants/download?id=${Number(applicant.applicant_id)}" class="btn btn-primary btn-sm">⬇️ Download Original CV</a>
                    <a href="/applicants?job_id=${Number(applicant.job_id)}" class="btn btn-ghost btn-sm">← Back to Roster</a>
                </div>
            </div>

            <!-- ATS Pipeline Control Bar -->
            <section class="card" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div style="display: flex; align-items: center; gap: 1rem;">
                    <div>
                        <small class="text-muted" style="display: block;">Current ATS Stage</small>
                        ${statusBadgeHtml(st)}
                    </div>
                    <div style="padding-left: 1rem; border-left: 1px solid rgba(255,255,255,0.1);">
                        <small class="text-muted" style="display: block;">AI Grounded Match Score</small>
                        <strong style="font-size: 1.4rem; color: #10b981;">${Number(applicant.ai_score || 0)}%</strong>
                    </div>
                </div>
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <button type="button" class="pipeline-btn pipeline-btn-interview js-view-ats" data-status="interviewing" ${st === 'interviewing' ? 'disabled' : ''}>🟢 Invite to Interview</button>
                    <button type="button" class="pipeline-btn pipeline-btn-offer js-view-ats" data-status="hired" ${st === 'hired' ? 'disabled' : ''}>🎉 Extend Job Offer</button>
                    <button type="button" class="pipeline-btn pipeline-btn-reject js-view-ats" data-status="rejected" ${st === 'rejected' ? 'disabled' : ''}>🛑 Reject Candidate</button>
                </div>
            </section>

            <!-- Grounded AI Evaluation Sections -->
            <div class="grid-2">
                ${(summary_sections && summary_sections.length)
                    ? summary_sections.map(sec => `<article class="card">${sec.html}</article>`).join('')
                    : `<article class="card" style="grid-column: 1 / -1;">${applicant.ai_summary || '<p class="text-muted">AI evaluation is currently queued or processing.</p>'}</article>`
                }
            </div>
        `;

        host.querySelectorAll('.js-view-ats').forEach(btn => {
            btn.addEventListener('click', async () => {
                btn.disabled = true;
                const r = await window.AppApi.post('/api/applicants/status', {
                    applicant_id: Number(applicant.applicant_id),
                    job_id: Number(applicant.job_id),
                    status: btn.dataset.status
                });
                if (r.ok && r.data.success) {
                    showToast(r.data.message, 'success');
                    renderApplicantDetail(host);
                } else {
                    btn.disabled = false;
                    showToast((r.data && r.data.error) || 'Could not update stage.', 'error');
                }
            });
        });
    }

    function initRecruiterPortal() {
        const host = document.getElementById('recruiter-view-host');
        if (!host) return;
        const path = window.location.pathname.replace(/\/+$/, '');

        if (path === '/dashboard/analytics') renderRecruiterAnalytics(host);
        else if (path === '/jobs/create') renderJobForm(host, false);
        else if (path === '/jobs/edit') renderJobForm(host, true);
        else if (path === '/applicants') renderApplicantsList(host);
        else if (path === '/applicant/view') renderApplicantDetail(host);
        else renderDashboardHome(host);
    }

    window.AppRecruiter = {
        initRecruiterPortal
    };
})(window);
