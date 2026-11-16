/**
 * PostYourJobUK — Recruiter Workspace, Job CRUD, ATS Pipeline & 6-Chart Analytics (recruiter.js)
 * Zero External Dependencies | WCAG 2.1 Level AA Compliant
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;

    function statusBadgeHtml(status) {
        const s = String(status || 'new').toLowerCase();
        const map = {
            hired: { cls: 'hired', label: '🎉 Hired' },
            interviewing: { cls: 'interviewing', label: '🟢 Interviewing' },
            reviewed: { cls: 'reviewed', label: '🔍 Reviewed' },
            rejected: { cls: 'rejected', label: '🛑 Rejected' },
            pending: { cls: 'pending', label: '⏳ Pending AI' },
            new: { cls: 'new', label: '✨ New' },
            processing_failed: { cls: 'failed', label: '⚠️ AI Retry' }
        };
        const cfg = map[s] || { cls: 'reviewed', label: escapeHtml(status) };
        return `<span class="status-badge-flag ${cfg.cls}">${cfg.label}</span>`;
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
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.85rem;">${escapeHtml(tenant.company_name || 'Recruiter Workspace')}</h1>
                    <p class="text-muted" style="margin-top: 0.25rem;">
                        👤 ${escapeHtml(tenant.contact_person || 'Hiring Team')} &bull; ✉️ ${escapeHtml(tenant.email || '')}
                        ${tenant.phone_number ? ` &bull; 📞 ${escapeHtml(tenant.phone_number)}` : ''}
                    </p>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    <a href="/dashboard/analytics" class="btn btn-ghost">📊 View Analytics</a>
                    <a href="/jobs/create" class="btn btn-primary">+ Post New Job</a>
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

            <!-- Job Postings Roster -->
            <section class="card" aria-labelledby="jobs-table-heading">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                    <h2 id="jobs-table-heading" style="font-size: 1.25rem; margin: 0;">Your Job Postings (${jobs.length})</h2>
                </div>
                ${jobs.length === 0 ? `
                    <p class="text-muted text-center" style="padding: 2rem 0;">You have not posted any jobs yet. Click <strong>+ Post New Job</strong> above to get started.</p>
                ` : `
                    <div class="table-wrapper">
                        <table id="recruiter-jobs-table" class="app-table stack-table">
                            <thead>
                                <tr>
                                    <th class="sortable-th" data-sort-key="title">Role Title</th>
                                    <th class="sortable-th" data-sort-key="status">Status</th>
                                    <th class="sortable-th" data-sort-key="applicants" data-sort-type="number">Applicants</th>
                                    <th class="sortable-th" data-sort-key="topscore" data-sort-type="number">Top AI Match</th>
                                    <th style="text-align: right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${jobs.map(j => `
                                    <tr data-title="${escapeHtml(j.title)}" data-status="${escapeHtml(j.status)}" data-applicants="${Number(j.applicant_count || 0)}" data-topscore="${Number(j.top_score || 0)}">
                                        <td data-label="Role Title">
                                            <div style="font-weight: 700; color: #ffffff;">${escapeHtml(j.title)}</div>
                                            <small class="text-muted">📍 ${escapeHtml(j.location || 'Remote')} &bull; ${escapeHtml(j.employment_type || 'full-time')}</small>
                                        </td>
                                        <td data-label="Status">
                                            <span class="status-badge ${j.status === 'active' ? 'active' : 'suspended'}">${escapeHtml(j.status)}</span>
                                        </td>
                                        <td data-label="Applicants">
                                            <a href="/applicants?job_id=${Number(j.job_id)}" class="btn btn-ghost btn-sm">
                                                👥 ${Number(j.applicant_count || 0)} Candidates
                                            </a>
                                        </td>
                                        <td data-label="Top AI Match">
                                            <strong style="color: ${Number(j.top_score) >= 80 ? '#10b981' : '#38bdf8'};">${Number(j.top_score || 0)}%</strong>
                                        </td>
                                        <td data-label="Actions" style="text-align: right;">
                                            <div style="display: inline-flex; gap: 0.45rem; flex-wrap: wrap;">
                                                <a href="/applicants?job_id=${Number(j.job_id)}" class="btn btn-primary btn-sm">Review</a>
                                                <a href="/jobs/edit?id=${Number(j.job_id)}" class="btn btn-ghost btn-sm">Edit</a>
                                                <button type="button" class="btn btn-danger btn-sm js-delete-job" data-job-id="${Number(j.job_id)}" data-job-title="${escapeHtml(j.title)}">Delete</button>
                                            </div>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `}
            </section>
        `;

        window.AppA11y.initSortableTable(document.getElementById('recruiter-jobs-table'));

        host.querySelectorAll('.js-delete-job').forEach(btn => {
            btn.addEventListener('click', async () => {
                const jobId = Number(btn.dataset.jobId);
                const title = btn.dataset.jobTitle;
                if (!window.confirm(`Delete job posting "${title}" and all associated applicants?`)) return;
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

            <!-- 5. Row 4: Top 5 Candidates Leaderboard -->
            <div class="card">
                <h2 style="font-size: 1.2rem; margin-bottom: 0.35rem;">🏆 Top 5 Highest-Scoring Candidates Across All Jobs</h2>
                <p class="text-muted" style="font-size: 0.84rem; margin-bottom: 1rem;">Quick-action leaderboard of your strongest AI-verified talent pool.</p>
                ${topCands.length === 0 ? `<p class="text-muted">No evaluated candidates yet.</p>` : `
                    <div class="table-wrapper">
                        <table class="app-table stack-table">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Candidate</th>
                                    <th>Applied Position</th>
                                    <th>AI Match Score</th>
                                    <th>ATS Stage</th>
                                    <th style="text-align: right;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${topCands.map((c, i) => `
                                    <tr>
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

        // Render all 6 charts via native HTML5 <canvas> engine
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
     * 4. Applicants Roster & 1-Click ATS Pipeline (/applicants?job_id=X)
     */
    async function renderApplicantsList(host) {
        const params = new URLSearchParams(window.location.search);
        const jobId = Number(params.get('job_id') || params.get('id') || 0);
        const res = await window.AppApi.get(`/api/applicants?job_id=${jobId}`);
        if (!res.ok || !res.data.success) return;

        const { job, applicants, stats } = res.data;
        document.title = `Applicants: ${job.title} — PostYourJobUK`;

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.75rem;">👥 Candidates for ${escapeHtml(job.title)}</h1>
                    <p class="text-muted" style="margin-top: 0.25rem;">AI-evaluated candidate roster &amp; automated ATS communication pipeline.</p>
                </div>
                <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                    ${applicants.length > 0 ? `<button type="button" id="wipe-all-cands-btn" class="btn btn-danger btn-sm">🗑️ Wipe All Candidates</button>` : ''}
                    <a href="/dashboard" class="btn btn-ghost btn-sm">← Back to Dashboard</a>
                </div>
            </div>

            <!-- 4 Uniform KPI Stage Cards -->
            <section class="app-stats-grid" aria-label="Job Pipeline Counters">
                <div class="card"><span class="form-label">Total Applicants</span><p class="stat-value" id="kpi-total">${Number(stats.total_applicants || 0)}</p></div>
                <div class="card"><span class="form-label">Interviewing</span><p class="stat-value" id="kpi-interviewing">${Number(stats.interviewing_count || 0)}</p></div>
                <div class="card"><span class="form-label">Hired / Offered</span><p class="stat-value" id="kpi-hired">${Number(stats.hired_count || 0)}</p></div>
                <div class="card"><span class="form-label">Rejected</span><p class="stat-value" id="kpi-rejected">${Number(stats.rejected_count || 0)}</p></div>
            </section>

            <!-- Balanced 4-Column Desktop Applicants Table -->
            <section class="card">
                ${applicants.length === 0 ? `<p class="text-muted text-center" style="padding: 2rem 0;">No candidates have applied to this position yet.</p>` : `
                    <div class="table-wrapper">
                        <table id="applicants-roster-table" class="app-table stack-table">
                            <thead>
                                <tr>
                                    <th class="sortable-th" data-sort-key="name">Candidate &amp; Stage</th>
                                    <th class="sortable-th" data-sort-key="score" data-sort-type="number">AI Match &amp; Evaluation</th>
                                    <th>ATS Pipeline Follow-Up</th>
                                    <th style="text-align: right;">Profile &amp; CV</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${applicants.map(a => {
                                    const st = String(a.status || 'new').toLowerCase();
                                    const score = Number(a.ai_score || 0);
                                    const scoreColor = score >= 80 ? '#10b981' : (score >= 50 ? '#f59e0b' : '#f87171');
                                    return `
                                    <tr id="cand-row-${Number(a.applicant_id)}" data-name="${escapeHtml(a.name)}" data-score="${score}">
                                        <td data-label="Candidate & Stage">
                                            <div style="font-weight: 700; color: #ffffff;">${escapeHtml(a.name)}</div>
                                            <small class="text-muted" style="display: block; margin-bottom: 0.35rem;">${escapeHtml(a.email)}</small>
                                            <span class="js-stage-badge">${statusBadgeHtml(st)}</span>
                                        </td>
                                        <td data-label="AI Match & Evaluation">
                                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                                <strong style="font-size: 1.15rem; color: ${scoreColor};">${score}%</strong>
                                                <div style="width: 80px; height: 7px; background: rgba(255,255,255,0.1); border-radius: 999px; overflow: hidden;">
                                                    <div style="width: ${Math.min(100, score)}%; height: 100%; background: ${scoreColor};"></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="ATS Pipeline Follow-Up">
                                            <div style="display: inline-flex; gap: 0.4rem; flex-wrap: wrap;">
                                                <button type="button" class="pipeline-btn pipeline-btn-interview js-ats-btn" data-id="${Number(a.applicant_id)}" data-status="interviewing" ${st === 'interviewing' ? 'disabled' : ''}>🟢 Invite</button>
                                                <button type="button" class="pipeline-btn pipeline-btn-offer js-ats-btn" data-id="${Number(a.applicant_id)}" data-status="hired" ${st === 'hired' ? 'disabled' : ''}>🎉 Offer</button>
                                                <button type="button" class="pipeline-btn pipeline-btn-reject js-ats-btn" data-id="${Number(a.applicant_id)}" data-status="rejected" ${st === 'rejected' ? 'disabled' : ''}>🛑 Reject</button>
                                            </div>
                                        </td>
                                        <td data-label="Profile & CV" style="text-align: right;">
                                            <div style="display: inline-flex; gap: 0.4rem; flex-wrap: wrap;">
                                                <a href="/applicant/view?id=${Number(a.applicant_id)}" class="btn btn-primary btn-sm">👁️ Profile</a>
                                                <a href="/api/applicants/download?id=${Number(a.applicant_id)}" class="btn btn-ghost btn-sm">⬇️ CV</a>
                                                <button type="button" class="btn btn-danger btn-sm js-del-cand" data-id="${Number(a.applicant_id)}" aria-label="Delete ${escapeHtml(a.name)}">🗑️</button>
                                            </div>
                                        </td>
                                    </tr>`;
                                }).join('')}
                            </tbody>
                        </table>
                    </div>
                `}
            </section>
        `;

        window.AppA11y.initSortableTable(document.getElementById('applicants-roster-table'));

        // Wire ATS Pipeline Buttons
        host.querySelectorAll('.js-ats-btn').forEach(btn => {
            btn.addEventListener('click', async () => {
                const applicantId = Number(btn.dataset.id);
                const newStatus = btn.dataset.status;
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
                if (!window.confirm('Permanently delete this candidate and their CV?')) return;
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
                if (!window.confirm('WARNING: Permanently delete ALL candidates and CV blobs for this job?')) return;
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
