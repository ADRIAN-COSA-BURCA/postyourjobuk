<?php
$topTenants   = $analytics['top_tenants_chart'] ?? [];
$velocity     = $analytics['velocity_chart'] ?? ['labels' => [], 'jobs' => [], 'apps' => []];
$globalFunnel = $analytics['global_funnel'] ?? ['reviewed' => 0, 'interviewing' => 0, 'hired' => 0, 'rejected' => 0];
$aiEngine     = $analytics['ai_engine'] ?? ['scored_complete' => 0, 'queued_pending' => 0, 'failed_count' => 0, 'platform_avg_score' => 0, 'success_rate' => 100];
$industryData = $analytics['industry_chart'] ?? ['labels' => [], 'data' => []];
$secTelemetry = $analytics['security_telemetry'] ?? ['INFO' => 0, 'WARNING' => 0, 'CRITICAL' => 0];
?>
<style>
    .admin-analytics-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .admin-card-subtitle {
        font-size: 0.82rem;
        color: #94a3b8;
        margin: -0.9rem 0 1.25rem 0;
    }
    .admin-mini-pills {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .admin-mini-pills-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .admin-mini-pill {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 0.45rem 0.5rem;
        text-align: center;
    }
    .admin-mini-pill .pill-label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        margin-bottom: 0.15rem;
    }
    .admin-mini-pill .pill-val {
        font-size: 1.05rem;
        font-weight: 800;
    }
    @media (max-width: 768px) {
        .admin-analytics-grid-2 {
            grid-template-columns: 1fr;
        }
        .admin-mini-pills {
            grid-template-columns: repeat(2, 1fr);
        }
        .admin-mini-pills-3 {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="admin-wrapper-view">
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="margin-bottom: 0.35rem; color: #ffffff;">Platform Analytics &amp; Multi-Tenant Intelligence</h2>
            <p class="text-muted" style="margin: 0;">Executive oversight of multi-tenant activity, AI CV worker health, global ATS conversions, and security telemetry.</p>
        </div>
        <div style="display: flex; gap: 0.65rem; flex-wrap: wrap;">
            <a href="/admin/fairness" class="btn btn-ghost">⚖️ Fairness Audit</a>
            <a href="/admin/logs" class="btn btn-ghost">🛡️ System Logs</a>
            <a href="/admin" class="btn btn-primary">← Back to System Overview</a>
        </div>
    </div>

    <!-- 1. Top 3 KPI Metric Counters (Uniform 4-color system preserved) -->
    <div class="admin-counters-grid" style="margin-bottom: 1.75rem;">
        <div class="admin-metric-card">
            <h3>Total Applications</h3>
            <p class="count-total"><?php echo (int)($analytics['total_applications'] ?? 0); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Jobs (Last 30 Days)</h3>
            <p class="count-listings"><?php echo (int)($analytics['recent_jobs'] ?? 0); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Active Tenants</h3>
            <p class="count-active"><?php echo (int)($analytics['total_tenants'] ?? 0); ?></p>
        </div>
    </div>

    <!-- 2. Row 1: Top Tenants by Platform Activity (Multi-Color Bar) + Dual-Curve Platform Growth (Line Chart) -->
    <div class="admin-analytics-grid-2">
        <div class="card">
            <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🏢 Top Tenants by Platform Activity</h2>
            <p class="admin-card-subtitle">Each registered company is color-coded by candidate application volume &amp; job postings.</p>
            <div style="height: 295px;"><canvas id="tenantActivityChart"></canvas></div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">📈 Platform Growth &amp; Activity Velocity</h2>
            <p class="admin-card-subtitle">Dual-line comparison of New Job Postings vs. Candidate Applications over time.</p>
            <div style="height: 295px;"><canvas id="platformVelocityChart"></canvas></div>
        </div>
    </div>

    <!-- 3. Row 2: Global ATS Conversion Funnel (Doughnut) + AI CV Worker & Queue Health (Pie Chart) -->
    <div class="admin-analytics-grid-2">
        <div class="card">
            <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🎯 Global ATS Conversion Funnel (All Tenants)</h2>
            <p class="admin-card-subtitle">Aggregates candidate progression across all recruiter pipelines platform-wide.</p>
            <div style="height: 240px;"><canvas id="globalFunnelChart"></canvas></div>
            <div class="admin-mini-pills">
                <div class="admin-mini-pill">
                    <span class="pill-label">🔍 Reviewed</span>
                    <span class="pill-val" style="color: #818cf8;"><?php echo (int)($globalFunnel['reviewed'] ?? 0); ?></span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">🟢 Interviewing</span>
                    <span class="pill-val" style="color: #38bdf8;"><?php echo (int)($globalFunnel['interviewing'] ?? 0); ?></span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">🎉 Hired</span>
                    <span class="pill-val" style="color: #10b981;"><?php echo (int)($globalFunnel['hired'] ?? 0); ?></span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">🛑 Rejected</span>
                    <span class="pill-val" style="color: #f87171;"><?php echo (int)($globalFunnel['rejected'] ?? 0); ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🤖 AI CV Processing Engine &amp; Queue Health</h2>
            <p class="admin-card-subtitle">Azure Function worker throughput, queue completion status, and mean AI match score.</p>
            <div style="height: 240px;"><canvas id="aiEnginePieChart"></canvas></div>
            <div class="admin-mini-pills-3">
                <div class="admin-mini-pill">
                    <span class="pill-label">✅ Queue SLA</span>
                    <span class="pill-val" style="color: #10b981;"><?php echo number_format((float)($aiEngine['success_rate'] ?? 100), 1); ?>%</span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">🧠 Mean AI Score</span>
                    <span class="pill-val" style="color: #f59e0b;"><?php echo number_format((float)($aiEngine['platform_avg_score'] ?? 0), 1); ?>%</span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">⚠️ Failed Jobs</span>
                    <span class="pill-val" style="color: #f87171;"><?php echo (int)($aiEngine['failed_count'] ?? 0); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 4. Row 3: Tenant Industry Breakdown (Pie) + System Security & Audit Telemetry (Doughnut) -->
    <div class="admin-analytics-grid-2">
        <div class="card">
            <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🏭 Tenant Distribution by Industry Sector</h2>
            <p class="admin-card-subtitle">Breakdown of registered recruiter organizations across market sectors.</p>
            <div style="height: 280px;"><canvas id="industryPieChart"></canvas></div>
        </div>

        <div class="card">
            <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🛡️ Security &amp; Audit Log Telemetry</h2>
            <p class="admin-card-subtitle">Distribution of platform audit events (`system_logs`) by severity level.</p>
            <div style="height: 225px;"><canvas id="securityDoughnutChart"></canvas></div>
            <div class="admin-mini-pills-3">
                <div class="admin-mini-pill">
                    <span class="pill-label">ℹ️ Info Events</span>
                    <span class="pill-val" style="color: #38bdf8;"><?php echo (int)($secTelemetry['INFO'] ?? 0); ?></span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">⚠️ Warnings</span>
                    <span class="pill-val" style="color: #f59e0b;"><?php echo (int)($secTelemetry['WARNING'] ?? 0); ?></span>
                </div>
                <div class="admin-mini-pill">
                    <span class="pill-label">🚨 Critical</span>
                    <span class="pill-val" style="color: #ef4444;"><?php echo (int)($secTelemetry['CRITICAL'] ?? 0); ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Row 4: Full-Width Multi-Tenant Utilisation & Performance Leaderboard -->
    <div class="card" style="margin-bottom: 2rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1rem;">
            <div>
                <h2 style="margin: 0; font-size: 1.2rem;">🏆 Multi-Tenant Utilisation &amp; Performance Leaderboard</h2>
                <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #94a3b8;">Ranks active recruiter workspaces by candidate volume, active job postings, average AI score, and ATS hiring velocity.</p>
            </div>
        </div>

        <?php if (empty($topTenants)): ?>
            <p style="color: #94a3b8; text-align: center; padding: 1.5rem 0;">No tenant organizations registered yet.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">
                            <th style="padding: 0.75rem;">Rank</th>
                            <th style="padding: 0.75rem;">Tenant Organization</th>
                            <th style="padding: 0.75rem;">Status</th>
                            <th style="padding: 0.75rem;">Jobs (Open / Total)</th>
                            <th style="padding: 0.75rem;">Total Applicants</th>
                            <th style="padding: 0.75rem;">Mean AI Score</th>
                            <th style="padding: 0.75rem;">ATS Outcomes</th>
                            <th style="padding: 0.75rem; text-align: right;">Workspace Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topTenants as $idx => $t):
                            $avgScore = $t['avg_ai_score'] !== null ? (float)$t['avg_ai_score'] : 0;
                            $scoreColor = $avgScore >= 80 ? '#10b981' : ($avgScore >= 50 ? '#f59e0b' : '#94a3b8');
                            $isActive = strtolower($t['status'] ?? 'active') === 'active';
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                            <td style="padding: 0.85rem 0.75rem; font-weight: 800; color: #f59e0b;">#<?php echo $idx + 1; ?></td>
                            <td style="padding: 0.85rem 0.75rem;">
                                <div style="font-weight: 700; color: #ffffff;"><?php echo htmlspecialchars($t['company_name']); ?></div>
                                <div style="font-size: 0.78rem; color: #94a3b8;"><?php echo htmlspecialchars($t['industry']); ?></div>
                            </td>
                            <td style="padding: 0.85rem 0.75rem;">
                                <span style="padding: 0.25rem 0.65rem; border-radius: 999px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; <?php echo $isActive ? 'background: rgba(16,185,129,0.15); color: #34d399; border: 1px solid rgba(16,185,129,0.35);' : 'background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.35);'; ?>">
                                    <?php echo $isActive ? '● Active' : '● Suspended'; ?>
                                </span>
                            </td>
                            <td style="padding: 0.85rem 0.75rem; color: #e2e8f0; font-weight: 600;">
                                <span style="color: #10b981; font-weight: 800;"><?php echo (int)$t['active_jobs']; ?></span> / <?php echo (int)$t['job_count']; ?>
                            </td>
                            <td style="padding: 0.85rem 0.75rem;">
                                <span style="font-weight: 800; font-size: 1rem; color: #38bdf8;"><?php echo (int)$t['applicant_count']; ?></span>
                            </td>
                            <td style="padding: 0.85rem 0.75rem;">
                                <?php if ($avgScore > 0): ?>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-weight: 800; color: <?php echo $scoreColor; ?>;"><?php echo number_format($avgScore, 1); ?>%</span>
                                        <div style="width: 60px; height: 6px; background: rgba(255,255,255,0.1); border-radius: 999px; overflow: hidden;">
                                            <div style="width: <?php echo min(100, $avgScore); ?>%; height: 100%; background: <?php echo $scoreColor; ?>;"></div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span style="color: #64748b; font-size: 0.8rem;">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.85rem 0.75rem; font-size: 0.82rem;">
                                <span style="color: #38bdf8; font-weight: 700; margin-right: 0.6rem;">🟢 <?php echo (int)$t['interviewing_count']; ?> Interview</span>
                                <span style="color: #10b981; font-weight: 700;">🎉 <?php echo (int)$t['hired_count']; ?> Hired</span>
                            </td>
                            <td style="padding: 0.85rem 0.75rem; text-align: right;">
                                <a href="/admin/view-tenant?id=<?php echo (int)$t['tenant_id']; ?>" class="btn btn-ghost btn-sm" style="padding: 0.35rem 0.75rem; font-size: 0.78rem;">
                                    🏢 Inspect Tenant
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Chart.js Setup -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    Chart.defaults.color = '#94a3b8';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.08)';
    Chart.defaults.font.family = "'Inter', -apple-system, BlinkMacSystemFont, sans-serif";

    const multiPalette = [
        'rgba(245, 158, 11, 0.85)',  // Amber
        'rgba(16, 185, 129, 0.85)',  // Emerald
        'rgba(56, 189, 248, 0.85)',  // Sky Blue
        'rgba(168, 85, 247, 0.85)',  // Purple
        'rgba(244, 63, 94, 0.85)',   // Rose
        'rgba(6, 182, 212, 0.85)',   // Cyan
        'rgba(234, 179, 8, 0.85)',   // Yellow
        'rgba(236, 72, 153, 0.85)'   // Pink
    ];
    const multiBorders = [
        '#f59e0b',
        '#10b981',
        '#38bdf8',
        '#a855f7',
        '#f43f5e',
        '#06b6d4',
        '#eab308',
        '#ec4899'
    ];

    const topTenantsData = <?php echo json_encode($topTenants); ?>;
    const tenantLabels = topTenantsData.length ? topTenantsData.map(t => t.company_name) : ['No Tenants'];
    const tenantApps   = topTenantsData.length ? topTenantsData.map(t => parseInt(t.applicant_count, 10) || 0) : [0];
    const tenantJobs   = topTenantsData.length ? topTenantsData.map(t => parseInt(t.job_count, 10) || 0) : [0];

    // 1. Top Tenants by Platform Activity (Multi-Color Horizontal Bar Chart)
    new Chart(document.getElementById('tenantActivityChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: tenantLabels,
            datasets: [
                {
                    label: 'Total Applicants',
                    data: tenantApps,
                    backgroundColor: tenantLabels.map((_, i) => multiPalette[i % multiPalette.length]),
                    borderColor: tenantLabels.map((_, i) => multiBorders[i % multiBorders.length]),
                    borderWidth: 1.5,
                    borderRadius: 6
                },
                {
                    label: 'Job Postings',
                    data: tenantJobs,
                    backgroundColor: 'rgba(255, 255, 255, 0.22)',
                    borderColor: '#cbd5e1',
                    borderWidth: 1,
                    borderRadius: 6
                }
            ]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#e2e8f0', boxWidth: 12, padding: 12 } }
            },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // 2. Platform Growth & Activity Velocity (Dual-Curve Line Chart)
    const velLabels = <?php echo json_encode(!empty($velocity['labels']) ? $velocity['labels'] : ['No Activity']); ?>;
    const velJobs   = <?php echo json_encode(!empty($velocity['jobs']) ? $velocity['jobs'] : [0]); ?>;
    const velApps   = <?php echo json_encode(!empty($velocity['apps']) ? $velocity['apps'] : [0]); ?>;

    new Chart(document.getElementById('platformVelocityChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: velLabels,
            datasets: [
                {
                    label: 'Candidate Applications',
                    data: velApps,
                    borderColor: '#38bdf8',
                    backgroundColor: 'rgba(56, 189, 248, 0.14)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#38bdf8',
                    pointRadius: 4,
                    tension: 0.35,
                    fill: true
                },
                {
                    label: 'New Job Postings',
                    data: velJobs,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointRadius: 4,
                    tension: 0.35,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { color: '#e2e8f0', boxWidth: 12, padding: 12 } }
            },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 } }
            }
        }
    });

    // 3. Global ATS Conversion Funnel (Doughnut Chart)
    const funnelRaw = <?php echo json_encode($globalFunnel); ?>;
    const funnelCounts = [
        parseInt(funnelRaw.reviewed || 0, 10),
        parseInt(funnelRaw.interviewing || 0, 10),
        parseInt(funnelRaw.hired || 0, 10),
        parseInt(funnelRaw.rejected || 0, 10)
    ];

    new Chart(document.getElementById('globalFunnelChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Reviewed', 'Interviewing', 'Hired / Offered', 'Rejected'],
            datasets: [{
                data: funnelCounts,
                backgroundColor: [
                    'rgba(56, 189, 248, 0.85)',  // Sky Blue
                    'rgba(245, 158, 11, 0.85)',  // Amber
                    'rgba(16, 185, 129, 0.85)',  // Emerald
                    'rgba(239, 68, 68, 0.85)'    // Coral Red
                ],
                borderColor: ['#38bdf8', '#f59e0b', '#10b981', '#ef4444'],
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'right', labels: { color: '#e2e8f0', padding: 14, boxWidth: 14 } }
            }
        }
    });

    // 4. AI CV Processing Engine & Queue Health (Pie Chart)
    const aiRaw = <?php echo json_encode($aiEngine); ?>;
    new Chart(document.getElementById('aiEnginePieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: ['AI Scored & Complete', 'Queued / Processing', 'Processing Failed'],
            datasets: [{
                data: [
                    parseInt(aiRaw.scored_complete || 0, 10),
                    parseInt(aiRaw.queued_pending || 0, 10),
                    parseInt(aiRaw.failed_count || 0, 10)
                ],
                backgroundColor: [
                    'rgba(16, 185, 129, 0.85)',  // Emerald
                    'rgba(245, 158, 11, 0.85)',  // Amber
                    'rgba(239, 68, 68, 0.85)'    // Coral Red
                ],
                borderColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: '#e2e8f0', padding: 14, boxWidth: 14 } }
            }
        }
    });

    // 5. Tenant Distribution by Industry Sector (Multi-Color Pie Chart)
    const indLabels = <?php echo json_encode(!empty($industryData['labels']) ? $industryData['labels'] : ['General Recruitment']); ?>;
    const indCounts = <?php echo json_encode(!empty($industryData['data']) ? $industryData['data'] : [1]); ?>;

    new Chart(document.getElementById('industryPieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: indLabels,
            datasets: [{
                data: indCounts,
                backgroundColor: indLabels.map((_, i) => multiPalette[i % multiPalette.length]),
                borderColor: indLabels.map((_, i) => multiBorders[i % multiBorders.length]),
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { color: '#e2e8f0', padding: 14, boxWidth: 14 } }
            }
        }
    });

    // 6. Security & Audit Log Telemetry (Doughnut Chart)
    const secRaw = <?php echo json_encode($secTelemetry); ?>;
    new Chart(document.getElementById('securityDoughnutChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['INFO (Normal Operations)', 'WARNING (Auth / Validation)', 'CRITICAL (Security / State)'],
            datasets: [{
                data: [
                    parseInt(secRaw.INFO || 0, 10),
                    parseInt(secRaw.WARNING || 0, 10),
                    parseInt(secRaw.CRITICAL || 0, 10)
                ],
                backgroundColor: [
                    'rgba(56, 189, 248, 0.85)',  // Sky Blue
                    'rgba(245, 158, 11, 0.85)',  // Amber
                    'rgba(239, 68, 68, 0.85)'    // Coral Red
                ],
                borderColor: ['#38bdf8', '#f59e0b', '#ef4444'],
                borderWidth: 2,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '58%',
            plugins: {
                legend: { position: 'right', labels: { color: '#e2e8f0', padding: 14, boxWidth: 14 } }
            }
        }
    });
});
</script>
