/**
 * PostYourJobUK — Super-Admin Governance, 6-Chart Analytics, Audit Logs & Tenant 9999 Fairness Suite (admin.js)
 * Zero External Dependencies | WCAG 2.1 Level AA Compliant
 */
(function (window) {
    'use strict';

    const { escapeHtml, showToast } = window.AppApi;
    let fairnessPollTimer = null;

    /**
     * 1. Admin System Overview & Tenants Roster (/admin)
     */
    async function renderAdminOverview(host) {
        const params = new URLSearchParams(window.location.search);
        const filter = params.get('filter') || 'all';
        const res = await window.AppApi.get(`/api/admin/dashboard?filter=${encodeURIComponent(filter)}`);
        if (!res.ok || !res.data.success) return;

        const { stats, tenants } = res.data;
        document.title = 'System Overview — Super-Admin Portal';

        host.innerHTML = `
            <div class="card" style="margin-bottom: 1.75rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="font-size: 1.7rem; margin: 0;">🛡️ System Overview</h1>
                    <p class="text-muted" style="margin-top: 0.2rem;">Multi-tenant governance, platform telemetry, and regulatory compliance controls.</p>
                </div>
                <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                    <a href="/admin/logs" class="btn btn-ghost btn-sm">📋 System Logs</a>
                    <a href="/admin/analytics" class="btn btn-ghost btn-sm">📊 View Analytics</a>
                    <a href="/admin/fairness" class="btn btn-ghost btn-sm">⚖️ Fairness Audit</a>
                    <button type="button" id="open-create-tenant-btn" class="btn btn-primary btn-sm">+ Create New Recruiter</button>
                </div>
            </div>

            <!-- 3 Uniform KPI Metric Cards -->
            <div class="admin-counters-grid">
                <div class="admin-metric-card"><h3>Total Tenants</h3><p class="count-total">${Number(stats.total_tenants || 0)}</p></div>
                <div class="admin-metric-card"><h3>Active Tenants</h3><p class="count-active">${Number(stats.active_tenants || 0)}</p></div>
                <div class="admin-metric-card"><h3>Total Job Postings</h3><p class="count-listings">${Number(stats.total_jobs || 0)}</p></div>
            </div>

            <!-- Registered Tenants Table -->
            <section class="card" aria-labelledby="tenants-heading">
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1rem;">
                    <h2 id="tenants-heading" style="font-size: 1.2rem; margin: 0;">Registered Recruiter Organizations (${tenants.length})</h2>
                    <div style="display: flex; gap: 0.5rem;">
                        <a href="/admin?filter=all" class="btn ${filter === 'all' ? 'btn-primary' : 'btn-ghost'} btn-sm">All</a>
                        <a href="/admin?filter=active" class="btn ${filter === 'active' ? 'btn-primary' : 'btn-ghost'} btn-sm">Active</a>
                        <a href="/admin?filter=suspended" class="btn ${filter === 'suspended' ? 'btn-primary' : 'btn-ghost'} btn-sm">Suspended</a>
                    </div>
                </div>
                <div class="table-wrapper">
                    <table id="admin-tenants-table" class="admin-table-view stack-table">
                        <thead>
                            <tr>
                                <th class="sortable-th" data-sort-key="company">Company Name</th>
                                <th class="sortable-th" data-sort-key="status">Status</th>
                                <th class="sortable-th" data-sort-key="created">Registered Date</th>
                                <th style="text-align: right;">Workspace Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tenants.map(t => `
                                <tr data-company="${escapeHtml(t.company_name)}" data-status="${escapeHtml(t.status)}" data-created="${escapeHtml(t.created_at)}">
                                    <td data-label="Company Name">
                                        <div style="font-weight: 700; color: #ffffff;">${escapeHtml(t.company_name)}</div>
                                        <small class="text-muted">${escapeHtml(t.email || '')}</small>
                                    </td>
                                    <td data-label="Status">
                                        <span class="status-badge-flag ${t.status === 'active' ? 'active' : 'suspended'}">${escapeHtml(t.status)}</span>
                                    </td>
                                    <td data-label="Registered Date">${escapeHtml((t.created_at || '').slice(0, 10))}</td>
                                    <td data-label="Workspace Action" style="text-align: right;">
                                        <a href="/admin/view-tenant?id=${Number(t.tenant_id)}" class="btn btn-primary btn-sm">Manage</a>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </section>
        `;

        window.AppA11y.initSortableTable(document.getElementById('admin-tenants-table'));

        const createBtn = document.getElementById('open-create-tenant-btn');
        if (createBtn) {
            createBtn.addEventListener('click', () => window.AppA11y.openModal('create-tenant-modal', createBtn));
        }
    }

    /**
     * 2. Admin 6-Chart Platform Analytics Suite (/admin/analytics)
     */
    async function renderAdminAnalytics(host) {
        const res = await window.AppApi.get('/api/admin/analytics');
        if (!res.ok || !res.data.success) return;

        const a = res.data.analytics || {};
        const topTenants = a.top_tenants_chart || [];
        const velocity = a.velocity_chart || { labels: [], jobs: [], apps: [] };
        const globalFunnel = a.global_funnel || {};
        const aiEngine = a.ai_engine || {};
        const industry = a.industry_chart || { labels: [], data: [] };
        const sec = a.security_telemetry || {};

        document.title = 'Platform Analytics — Super-Admin Portal';

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.75rem;">
                <div>
                    <h1 style="font-size: 1.8rem; margin: 0;">Platform Analytics &amp; Multi-Tenant Intelligence</h1>
                    <p class="text-muted" style="margin-top: 0.25rem;">Executive oversight of multi-tenant activity, AI CV worker health, global ATS conversions, and security telemetry.</p>
                </div>
                <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                    <a href="/admin/fairness" class="btn btn-ghost btn-sm">⚖️ Fairness Audit</a>
                    <a href="/admin/logs" class="btn btn-ghost btn-sm">🛡️ System Logs</a>
                    <a href="/admin" class="btn btn-primary btn-sm">← Back to System Overview</a>
                </div>
            </div>

            <!-- 1. Top 3 Uniform KPI Cards -->
            <div class="admin-counters-grid">
                <div class="admin-metric-card"><h3>Total Applications</h3><p class="count-total">${Number(a.total_applications || 0)}</p></div>
                <div class="admin-metric-card"><h3>Jobs (Last 30 Days)</h3><p class="count-listings">${Number(a.recent_jobs || 0)}</p></div>
                <div class="admin-metric-card"><h3>Active Tenants</h3><p class="count-active">${Number(a.total_tenants || 0)}</p></div>
            </div>

            <!-- 2. Row 1: Top Tenants Bar + Platform Velocity Dual Line -->
            <div class="admin-analytics-grid-2">
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🏢 Top Tenants by Platform Activity</h2>
                    <p class="admin-card-subtitle">Each registered company is color-coded by candidate application volume &amp; job postings.</p>
                    <div style="height: 275px;"><canvas id="adm-tenants-bar-canvas"></canvas></div>
                </div>
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">📈 Platform Growth &amp; Activity Velocity</h2>
                    <p class="admin-card-subtitle">Dual-line comparison of New Job Postings vs. Candidate Applications over time.</p>
                    <div style="height: 275px;"><canvas id="adm-velocity-line-canvas"></canvas></div>
                </div>
            </div>

            <!-- 3. Row 2: Global ATS Funnel Doughnut + AI CV Engine Pie -->
            <div class="admin-analytics-grid-2">
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🎯 Global ATS Conversion Funnel (All Tenants)</h2>
                    <p class="admin-card-subtitle">Aggregates candidate progression across all recruiter pipelines platform-wide.</p>
                    <div style="height: 230px;"><canvas id="adm-funnel-canvas"></canvas></div>
                    <div class="admin-mini-pills">
                        <div class="admin-mini-pill"><span class="pill-label">🔍 Reviewed</span><span class="pill-val" style="color: #818cf8;">${Number(globalFunnel.reviewed || 0)}</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">🟢 Interviewing</span><span class="pill-val" style="color: #38bdf8;">${Number(globalFunnel.interviewing || 0)}</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">🎉 Hired</span><span class="pill-val" style="color: #10b981;">${Number(globalFunnel.hired || 0)}</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">🛑 Rejected</span><span class="pill-val" style="color: #f87171;">${Number(globalFunnel.rejected || 0)}</span></div>
                    </div>
                </div>
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🤖 AI CV Processing Engine &amp; Queue Health</h2>
                    <p class="admin-card-subtitle">Azure Function worker throughput, queue completion status, and mean AI match score.</p>
                    <div style="height: 230px;"><canvas id="adm-ai-pie-canvas"></canvas></div>
                    <div class="admin-mini-pills-3">
                        <div class="admin-mini-pill"><span class="pill-label">✅ Queue SLA</span><span class="pill-val" style="color: #10b981;">${Number(aiEngine.success_rate || 100).toFixed(1)}%</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">🧠 Mean AI Score</span><span class="pill-val" style="color: #f59e0b;">${Number(aiEngine.platform_avg_score || 0).toFixed(1)}%</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">⚠️ Failed Jobs</span><span class="pill-val" style="color: #f87171;">${Number(aiEngine.failed_count || 0)}</span></div>
                    </div>
                </div>
            </div>

            <!-- 4. Row 3: Industry Sector Pie + Security Telemetry Doughnut -->
            <div class="admin-analytics-grid-2">
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🏭 Tenant Distribution by Industry Sector</h2>
                    <p class="admin-card-subtitle">Breakdown of registered recruiter organizations across market sectors.</p>
                    <div style="height: 265px;"><canvas id="adm-industry-pie-canvas"></canvas></div>
                </div>
                <div class="card">
                    <h2 style="font-size: 1.18rem; margin-bottom: 0.85rem;">🛡️ Security &amp; Audit Log Telemetry</h2>
                    <p class="admin-card-subtitle">Distribution of platform audit events (system_logs) by severity level.</p>
                    <div style="height: 220px;"><canvas id="adm-sec-doughnut-canvas"></canvas></div>
                    <div class="admin-mini-pills-3">
                        <div class="admin-mini-pill"><span class="pill-label">ℹ️ Info Events</span><span class="pill-val" style="color: #38bdf8;">${Number(sec.INFO || 0)}</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">⚠️ Warnings</span><span class="pill-val" style="color: #f59e0b;">${Number(sec.WARNING || 0)}</span></div>
                        <div class="admin-mini-pill"><span class="pill-label">🚨 Critical</span><span class="pill-val" style="color: #ef4444;">${Number(sec.CRITICAL || 0)}</span></div>
                    </div>
                </div>
            </div>

            <!-- 5. Row 4: Multi-Tenant Utilisation Leaderboard -->
            <div class="card">
                <h2 style="font-size: 1.2rem; margin-bottom: 0.35rem;">🏆 Multi-Tenant Utilisation &amp; Performance Leaderboard</h2>
                <p class="text-muted" style="font-size: 0.84rem; margin-bottom: 1rem;">Ranks active recruiter workspaces by candidate volume, active job postings, average AI score, and ATS hiring velocity.</p>
                <div class="table-wrapper">
                    <table class="admin-table-view stack-table">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Tenant Organization</th>
                                <th>Status</th>
                                <th>Jobs (Active / Total)</th>
                                <th>Applicants</th>
                                <th>Mean AI Score</th>
                                <th>ATS Outcomes</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${topTenants.map((t, idx) => `
                                <tr>
                                    <td data-label="Rank"><strong style="color: #f59e0b;">#${idx + 1}</strong></td>
                                    <td data-label="Tenant Organization">
                                        <div style="font-weight: 700;">${escapeHtml(t.company_name)}</div>
                                        <small class="text-muted">${escapeHtml(t.industry)}</small>
                                    </td>
                                    <td data-label="Status"><span class="status-badge-flag ${t.status === 'active' ? 'active' : 'suspended'}">${escapeHtml(t.status)}</span></td>
                                    <td data-label="Jobs (Active / Total)"><strong style="color: #10b981;">${Number(t.active_jobs || 0)}</strong> / ${Number(t.job_count || 0)}</td>
                                    <td data-label="Applicants"><strong style="color: #38bdf8;">${Number(t.applicant_count || 0)}</strong></td>
                                    <td data-label="Mean AI Score"><strong>${t.avg_ai_score ? Number(t.avg_ai_score).toFixed(1) + '%' : 'N/A'}</strong></td>
                                    <td data-label="ATS Outcomes">
                                        <span style="color: #38bdf8; margin-right: 0.5rem;">🟢 ${Number(t.interviewing_count || 0)}</span>
                                        <span style="color: #10b981;">🎉 ${Number(t.hired_count || 0)}</span>
                                    </td>
                                    <td data-label="Action" style="text-align: right;">
                                        <a href="/admin/view-tenant?id=${Number(t.tenant_id)}" class="btn btn-ghost btn-sm">🏢 Inspect</a>
                                    </td>
                                </tr>
                            `).join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;

        const tLabels = topTenants.length ? topTenants.map(t => t.company_name) : ['No Tenants'];
        window.AppCharts.renderHorizontalBar('adm-tenants-bar-canvas', {
            title: 'Top Tenants by Platform Activity',
            labels: tLabels,
            datasets: [
                { label: 'Total Applicants', data: topTenants.length ? topTenants.map(t => Number(t.applicant_count || 0)) : [0], colors: window.AppCharts.PALETTE },
                { label: 'Job Postings', data: topTenants.length ? topTenants.map(t => Number(t.job_count || 0)) : [0], color: '#94a3b8' }
            ]
        });

        window.AppCharts.renderLineChart('adm-velocity-line-canvas', {
            title: 'Platform Growth & Activity Velocity',
            labels: velocity.labels && velocity.labels.length ? velocity.labels : ['No Activity'],
            datasets: [
                { label: 'Candidate Applications', data: velocity.apps || [0], color: '#38bdf8' },
                { label: 'New Job Postings', data: velocity.jobs || [0], color: '#10b981' }
            ]
        });

        window.AppCharts.renderDoughnut('adm-funnel-canvas', {
            title: 'Global ATS Conversion Funnel',
            labels: ['Reviewed', 'Interviewing', 'Hired', 'Rejected'],
            values: [globalFunnel.reviewed || 0, globalFunnel.interviewing || 0, globalFunnel.hired || 0, globalFunnel.rejected || 0],
            colors: ['#818cf8', '#38bdf8', '#10b981', '#ef4444']
        });

        window.AppCharts.renderPie('adm-ai-pie-canvas', {
            title: 'AI CV Processing Engine Health',
            labels: ['AI Scored & Complete', 'Queued / Processing', 'Failed'],
            values: [aiEngine.scored_complete || 0, aiEngine.queued_pending || 0, aiEngine.failed_count || 0],
            colors: ['#10b981', '#f59e0b', '#ef4444']
        });

        window.AppCharts.renderPie('adm-industry-pie-canvas', {
            title: 'Tenant Distribution by Industry Sector',
            labels: industry.labels && industry.labels.length ? industry.labels : ['General'],
            values: industry.data && industry.data.length ? industry.data : [1],
            colors: window.AppCharts.PALETTE
        });

        window.AppCharts.renderDoughnut('adm-sec-doughnut-canvas', {
            title: 'Security & Audit Log Telemetry',
            labels: ['INFO', 'WARNING', 'CRITICAL'],
            values: [sec.INFO || 0, sec.WARNING || 0, sec.CRITICAL || 0],
            colors: ['#38bdf8', '#f59e0b', '#ef4444']
        });
    }

    /**
     * 3. System Security & Audit Logs (/admin/logs)
     */
    async function renderAdminLogs(host) {
        const params = new URLSearchParams(window.location.search);
        const action = params.get('action') || 'all';
        const res = await window.AppApi.get(`/api/admin/logs?action=${encodeURIComponent(action)}`);
        if (!res.ok || !res.data.success) return;

        const logs = res.data.logs || [];
        document.title = 'System Security & Audit Logs — Super-Admin Portal';

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="font-size: 1.75rem; margin: 0;">📋 System Security &amp; Audit Logs</h1>
                    <p class="text-muted">Immutable operational and security event trail (system_logs).</p>
                </div>
                <a href="/admin" class="btn btn-ghost btn-sm">← Back to System Overview</a>
            </div>
            <section class="card">
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                    ${['all', 'ADMIN_', 'TENANT_', 'APPLICANT_', 'FAIRNESS_'].map(f => `
                        <a href="/admin/logs?action=${f}" class="btn ${action === f ? 'btn-primary' : 'btn-ghost'} btn-sm">${f === 'all' ? 'All Events' : f + '*'}</a>
                    `).join('')}
                </div>
                <div class="table-wrapper">
                    <table id="admin-logs-table" class="admin-table-view stack-table">
                        <thead>
                            <tr>
                                <th class="sortable-th" data-sort-key="time">Timestamp</th>
                                <th class="sortable-th" data-sort-key="sev">Severity</th>
                                <th class="sortable-th" data-sort-key="action">Action Type</th>
                                <th class="sortable-th" data-sort-key="actor">Actor</th>
                                <th>IP Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${logs.map(l => {
                                const sev = String(l.severity || 'INFO').toUpperCase();
                                const badgeCls = sev === 'CRITICAL' ? 'badge-critical' : (sev === 'WARNING' ? 'badge-warning' : 'badge-info');
                                return `
                                <tr data-time="${escapeHtml(l.created_at)}" data-sev="${sev}" data-action="${escapeHtml(l.action_type)}" data-actor="${escapeHtml(l.user_label)}">
                                    <td data-label="Timestamp">${escapeHtml(l.created_at || '')}</td>
                                    <td data-label="Severity"><span class="badge ${badgeCls}">${sev}</span></td>
                                    <td data-label="Action Type"><code style="color: #c4b5fd;">${escapeHtml(l.action_type)}</code></td>
                                    <td data-label="Actor">${escapeHtml(l.user_label || 'System')}</td>
                                    <td data-label="IP Address">${escapeHtml(l.ip_address || '0.0.0.0')}</td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            </section>
        `;

        window.AppA11y.initSortableTable(document.getElementById('admin-logs-table'));
    }

    /**
     * 4. Inspect & Edit Tenant Workspace (/admin/view-tenant & /admin/tenant/edit)
     */
    async function renderTenantInspect(host) {
        const params = new URLSearchParams(window.location.search);
        const id = Number(params.get('id') || 0);
        const res = await window.AppApi.get(`/api/admin/tenants/detail?id=${id}`);
        if (!res.ok || !res.data.success) return;

        const { tenant, jobs } = res.data;
        const isActive = tenant.status === 'active';
        document.title = `Tenant: ${tenant.company_name} — Super-Admin Portal`;

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.75rem;">🏢 ${escapeHtml(tenant.company_name)}</h1>
                    <p class="text-muted">Workspace ID #${Number(tenant.tenant_id)} &bull; ${escapeHtml(tenant.email)}</p>
                </div>
                <div style="display: flex; gap: 0.65rem;">
                    <button type="button" id="toggle-tenant-status-btn" class="btn ${isActive ? 'btn-danger' : 'btn-success'} btn-sm">
                        ${isActive ? '🛑 Suspend Workspace' : '✅ Activate Workspace'}
                    </button>
                    <a href="/admin" class="btn btn-ghost btn-sm">← Back to Overview</a>
                </div>
            </div>

            <div class="grid-2">
                <section class="card">
                    <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Edit Organization Details</h2>
                    <form id="admin-edit-tenant-form">
                        <input type="hidden" name="tenant_id" value="${Number(tenant.tenant_id)}">
                        <div class="form-group"><label for="et-comp">Company Name</label><input type="text" id="et-comp" name="company_name" value="${escapeHtml(tenant.company_name)}" required></div>
                        <div class="form-group"><label for="et-contact">Contact Person</label><input type="text" id="et-contact" name="contact_person" value="${escapeHtml(tenant.contact_person || '')}"></div>
                        <div class="form-group"><label for="et-phone">Phone Number</label><input type="text" id="et-phone" name="phone_number" value="${escapeHtml(tenant.phone_number || '')}"></div>
                        <div class="form-group"><label for="et-ind">Industry</label><input type="text" id="et-ind" name="industry" value="${escapeHtml(tenant.industry || '')}"></div>
                        <div class="form-group"><label for="et-web">Website URL</label><input type="url" id="et-web" name="website_url" value="${escapeHtml(tenant.website_url || '')}"></div>
                        <div class="form-group"><label for="et-addr">Address</label><textarea id="et-addr" name="company_address" rows="2">${escapeHtml(tenant.company_address || '')}</textarea></div>
                        <button type="submit" class="btn btn-primary btn-sm">Save Tenant Changes</button>
                    </form>
                </section>

                <section class="card">
                    <h2 style="font-size: 1.15rem; margin-bottom: 1rem;">Posted Jobs (${jobs.length})</h2>
                    ${jobs.length === 0 ? `<p class="text-muted">No jobs posted by this tenant.</p>` : `
                        <div class="table-wrapper">
                            <table class="admin-table-view stack-table">
                                <thead><tr><th>Job Title</th><th>Status</th><th>Applicants</th></tr></thead>
                                <tbody>
                                    ${jobs.map(j => `
                                        <tr>
                                            <td data-label="Job Title">${escapeHtml(j.title)}</td>
                                            <td data-label="Status"><span class="status-badge-flag ${j.status === 'active' ? 'active' : 'suspended'}">${escapeHtml(j.status)}</span></td>
                                            <td data-label="Applicants">${Number(j.applicant_count || 0)}</td>
                                        </tr>
                                    `).join('')}
                                </tbody>
                            </table>
                        </div>
                    `}
                </section>
            </div>
        `;

        document.getElementById('toggle-tenant-status-btn').addEventListener('click', async () => {
            const endpoint = isActive ? '/api/admin/tenants/suspend' : '/api/admin/tenants/activate';
            const r = await window.AppApi.post(endpoint, { id: id });
            if (r.ok && r.data.success) {
                showToast(r.data.message, 'success');
                renderTenantInspect(host);
            }
        });

        document.getElementById('admin-edit-tenant-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const f = e.target;
            const r = await window.AppApi.post('/api/admin/tenants/edit', {
                tenant_id: id,
                company_name: f.company_name.value.trim(),
                contact_person: f.contact_person.value.trim(),
                phone_number: f.phone_number.value.trim(),
                industry: f.industry.value.trim(),
                website_url: f.website_url.value.trim(),
                company_address: f.company_address.value.trim()
            });
            if (r.ok && r.data.success) {
                showToast(r.data.message, 'success');
                renderTenantInspect(host);
            }
        });
    }

    /**
     * 5. Tenant 9999 Fairness & Algorithmic Parity Audit Suite (/admin/fairness)
     */
    async function renderFairnessAudit(host) {
        const res = await window.AppApi.get('/api/admin/fairness');
        if (!res.ok || !res.data.success) return;

        const { pairs, dimensions, stats } = res.data;
        document.title = 'Algorithmic Fairness & Bias Audit — Super-Admin Portal';

        host.innerHTML = `
            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
                <div>
                    <h1 style="margin: 0; font-size: 1.8rem;">⚖️ Algorithmic Fairness &amp; Bias Transparency Panel</h1>
                    <p class="text-muted" style="margin-top: 0.25rem;">Counterfactual parity verification on Tenant 9999 (EU AI Act Annex III &amp; UK Equality Act 2010).</p>
                </div>
                <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
                    <button type="button" id="run-live-audit-btn" class="btn btn-primary btn-sm">⚡ Run Live Algorithmic Parity Audit</button>
                    <a href="/admin/fairness/certificate" target="_blank" class="btn btn-ghost btn-sm">📜 Export Compliance Certificate</a>
                    <a href="/admin" class="btn btn-ghost btn-sm">← Back to Overview</a>
                </div>
            </div>

            <div id="fairness-progress-banner" class="card" style="display: none; margin-bottom: 1.5rem; border-color: #38bdf8;">
                <strong id="fairness-progress-text" style="color: #38bdf8;">Dispatching 18 synthetic CVs to Azure Storage Queue…</strong>
            </div>

            <!-- 4 Uniform KPI Stat Cards -->
            <div class="admin-counters-grid">
                <div class="admin-metric-card"><h3>Total Audit Pairs</h3><p>${Number(stats.total_pairs || 0)}</p></div>
                <div class="admin-metric-card"><h3>Mean Score Delta</h3><p>${Number(stats.mean_delta || 0)} pt</p></div>
                <div class="admin-metric-card"><h3>Maximum Observed Delta</h3><p>${Number(stats.max_skew || 0)} pt</p></div>
                <div class="admin-metric-card"><h3>Most Skewed Pair</h3><p>${escapeHtml(stats.max_skew_pair || 'None')}</p></div>
            </div>

            <section class="card">
                <h2 style="font-size: 1.2rem; margin-bottom: 1rem;">9 Counterfactual Demographic Benchmark Pairs</h2>
                <div class="table-wrapper">
                    <table class="admin-table-view stack-table">
                        <thead>
                            <tr>
                                <th>Pair &amp; Protected Dimension</th>
                                <th>Baseline Candidate (A)</th>
                                <th>Counterfactual Variant (B)</th>
                                <th>Score Delta</th>
                                <th>Parity Verdict</th>
                                <th style="text-align: right;">Forensic Evidence</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${pairs.map(p => {
                                const code = p.pair_code;
                                const dimInfo = dimensions[code] || { dim: code, desc: '' };
                                const delta = p.score_delta !== null ? Number(p.score_delta) : 0;
                                const isSafe = Math.abs(delta) <= 5;
                                return `
                                <tr>
                                    <td data-label="Dimension">
                                        <div style="font-weight: 700; color: #ffffff;">${escapeHtml(code)}: ${escapeHtml(dimInfo.dim)}</div>
                                        <small class="text-muted">${escapeHtml(dimInfo.desc)}</small>
                                    </td>
                                    <td data-label="Baseline (A)">
                                        <div>${escapeHtml(p.name_a)}</div>
                                        <strong style="color: #10b981;">${p.score_a !== null ? p.score_a + '%' : 'Pending'}</strong>
                                    </td>
                                    <td data-label="Variant (B)">
                                        <div>${escapeHtml(p.name_b)}</div>
                                        <strong style="color: #38bdf8;">${p.score_b !== null ? p.score_b + '%' : 'Pending'}</strong>
                                    </td>
                                    <td data-label="Score Delta"><strong>${delta > 0 ? '+' + delta : delta} pt</strong></td>
                                    <td data-label="Parity Verdict">
                                        <span class="status-badge-flag ${isSafe ? 'active' : 'rejected'}">${isSafe ? '✅ Parity Safe' : '⚠️ Review'}</span>
                                    </td>
                                    <td data-label="Forensic Evidence" style="text-align: right;">
                                        <button type="button" class="btn btn-ghost btn-sm js-inspect-pair" data-pair="${escapeHtml(code)}">🔍 Inspect Evidence</button>
                                    </td>
                                </tr>`;
                            }).join('')}
                        </tbody>
                    </table>
                </div>
            </section>
        `;

        // Wire Inspect Evidence Modal
        host.querySelectorAll('.js-inspect-pair').forEach(btn => {
            btn.addEventListener('click', async () => {
                const pairCode = btn.dataset.pair;
                const r = await window.AppApi.get(`/api/admin/fairness/detail?pair=${encodeURIComponent(pairCode)}`);
                if (!r.ok || !r.data.success) return;

                const d = r.data;
                const modalBody = document.getElementById('fairness-modal-body');
                document.getElementById('fairness-modal-title').textContent = `${d.pair_code}: ${d.dimension} (Delta: ${d.delta ?? 0} pt)`;

                modalBody.innerHTML = `
                    <div class="grid-2" style="margin-top: 1rem;">
                        <div class="card">
                            <h3 style="font-size: 1rem; color: #10b981; margin-bottom: 0.5rem;">${escapeHtml(d.candidate_a.name)} (${d.candidate_a.score ?? 0}%)</h3>
                            <p class="text-muted" style="font-size: 0.8rem; margin-bottom: 0.5rem;">SHA-256 Clean Hash: <code>${escapeHtml(d.candidate_a.fingerprint?.clean_cv_sha256 || 'Verified')}</code></p>
                            <pre style="white-space: pre-wrap; font-size: 0.78rem; background: #0b0f19; padding: 0.75rem; border-radius: 8px; max-height: 260px; overflow-y: auto;">${escapeHtml(d.candidate_a.raw_cv || 'Raw CV stored in cloud')}</pre>
                        </div>
                        <div class="card">
                            <h3 style="font-size: 1rem; color: #38bdf8; margin-bottom: 0.5rem;">${escapeHtml(d.candidate_b.name)} (${d.candidate_b.score ?? 0}%)</h3>
                            <p class="text-muted" style="font-size: 0.8rem; margin-bottom: 0.5rem;">SHA-256 Clean Hash: <code>${escapeHtml(d.candidate_b.fingerprint?.clean_cv_sha256 || 'Verified')}</code></p>
                            <pre style="white-space: pre-wrap; font-size: 0.78rem; background: #0b0f19; padding: 0.75rem; border-radius: 8px; max-height: 260px; overflow-y: auto;">${escapeHtml(d.candidate_b.raw_cv || 'Raw CV stored in cloud')}</pre>
                        </div>
                    </div>
                `;
                window.AppA11y.openModal('fairness-evidence-modal', btn);
            });
        });

        // Wire Live Audit Execution
        const runBtn = document.getElementById('run-live-audit-btn');
        if (runBtn) {
            runBtn.addEventListener('click', async () => {
                runBtn.disabled = true;
                const banner = document.getElementById('fairness-progress-banner');
                const txt = document.getElementById('fairness-progress-text');
                banner.style.display = 'block';

                const r = await window.AppApi.post('/api/admin/fairness/run');
                if (!r.ok || !r.data.success) {
                    showToast((r.data && r.data.error) || 'Failed to dispatch audit.', 'error');
                    runBtn.disabled = false;
                    return;
                }

                showToast(r.data.message, 'success');
                if (fairnessPollTimer) clearInterval(fairnessPollTimer);
                fairnessPollTimer = setInterval(async () => {
                    const stRes = await window.AppApi.get('/api/admin/fairness/status');
                    if (stRes.ok && stRes.data.success) {
                        txt.textContent = `⚡ Azure Function Progress: ${stRes.data.completed} / ${stRes.data.total} Synthetic CVs Evaluated…`;
                        if (stRes.data.is_complete) {
                            clearInterval(fairnessPollTimer);
                            showToast('Live Algorithmic Parity Audit Complete!', 'success');
                            renderFairnessAudit(host);
                        }
                    }
                }, 3000);
            });
        }
    }

    function initAdminPortal() {
        const host = document.getElementById('admin-view-host');
        if (!host) return;
        const path = window.location.pathname.replace(/\/+$/, '');

        // Wire Create Tenant Modal Form
        const createForm = document.getElementById('create-tenant-form');
        if (createForm) {
            createForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const f = e.target;
                const r = await window.AppApi.post('/api/admin/tenants/create', {
                    company_name: f.company_name.value.trim(),
                    contact_person: f.contact_person.value.trim(),
                    email: f.email.value.trim(),
                    phone_number: f.phone_number.value.trim(),
                    website_url: f.website_url.value.trim(),
                    company_address: f.company_address.value.trim(),
                    industry: f.industry.value.trim(),
                    password: f.password.value,
                    recovery_code: f.recovery_code.value.trim()
                });
                if (r.ok && r.data.success) {
                    window.AppA11y.closeModal('create-tenant-modal');
                    showToast(r.data.message, 'success');
                    f.reset();
                    renderAdminOverview(host);
                } else {
                    showToast((r.data && r.data.error) || 'Failed to create tenant.', 'error');
                }
            });
        }

        if (path === '/admin/analytics') renderAdminAnalytics(host);
        else if (path === '/admin/logs') renderAdminLogs(host);
        else if (path === '/admin/view-tenant' || path === '/admin/tenant/edit') renderTenantInspect(host);
        else if (path === '/admin/fairness') renderFairnessAudit(host);
        else renderAdminOverview(host);
    }

    window.AppAdmin = {
        initAdminPortal
    };
})(window);
