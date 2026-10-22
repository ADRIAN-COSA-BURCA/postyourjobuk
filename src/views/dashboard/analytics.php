<?php
$topJobs             = $topJobs ?? [];
$funnelData          = $funnelData ?? ['reviewed' => 0, 'interviewing' => 0, 'hired' => 0, 'rejected' => 0];
$trendData           = $trendData ?? ['labels' => [], 'data' => []];
$scoreDistribution   = $scoreDistribution ?? ['Strong Match (≥80%)' => 0, 'Qualified (50%–79%)' => 0, 'Low Match (<50%)' => 0];
$employmentBreakdown = $employmentBreakdown ?? ['labels' => [], 'data' => []];
$topCandidates       = $topCandidates ?? [];
?>
<style>
    .analytics-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    .analytics-card-subtitle {
        font-size: 0.82rem;
        color: #94a3b8;
        margin: -0.9rem 0 1.25rem 0;
    }
    .pipeline-mini-pills {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
    }
    .pipeline-mini-pill {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 8px;
        padding: 0.45rem 0.5rem;
        text-align: center;
    }
    .pipeline-mini-pill .pill-label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.04em;
        color: #94a3b8;
        margin-bottom: 0.15rem;
    }
    .pipeline-mini-pill .pill-val {
        font-size: 1.05rem;
        font-weight: 800;
    }
    @media (max-width: 768px) {
        .analytics-grid-2 {
            grid-template-columns: 1fr;
        }
        .pipeline-mini-pills {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
    <div>
        <h1 style="margin: 0; color: #ffffff;">Analytics Dashboard</h1>
        <p style="margin: 0.4rem 0 0 0; color: #94a3b8;">Executive recruitment &amp; AI talent intelligence for <strong><?= htmlspecialchars($tenant['company_name'] ?? 'Recruiter') ?></strong></p>
    </div>
    <a href="/dashboard" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<!-- 1. Top 3 KPI Stat Cards (Unchanged as requested) -->
<section class="stats-grid" style="margin-bottom: 1.75rem !important;">
    <div class="card">
        <h3>Total Jobs</h3>
        <p class="stat-value"><?= (int)$stats['total_jobs'] ?></p>
    </div>
    <div class="card">
        <h3>Active Jobs</h3>
        <p class="stat-value"><?= (int)$stats['active_jobs'] ?></p>
    </div>
    <div class="card">
        <h3>Total Applicants</h3>
        <p class="stat-value"><?= (int)$stats['total_applicants'] ?></p>
    </div>
</section>

<!-- 2. Row 1: Top Jobs by Applicant Volume (Multi-Color Bar) + AI Match Quality by Job (Dual Line Chart) -->
<div class="analytics-grid-2">
    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">📊 Top Jobs by Applicant Volume</h2>
        <p class="analytics-card-subtitle">Each job posting is color-coded by candidate application volume.</p>
        <div style="height: 290px;"><canvas id="topJobsChart"></canvas></div>
    </div>

    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">📈 AI Match Quality by Job Posting</h2>
        <p class="analytics-card-subtitle">Compares Average Candidate Match (%) vs. Top Candidate Score (%) per role.</p>
        <div style="height: 290px;"><canvas id="jobQualityLineChart"></canvas></div>
    </div>
</div>

<!-- 3. Row 2: ATS Recruitment Pipeline (Doughnut) + Application Activity by Date (Multi-Color Pie) -->
<div class="analytics-grid-2">
    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🎯 ATS Recruitment Pipeline</h2>
        <p class="analytics-card-subtitle">Live candidate distribution across your 4 hiring stages.</p>
        <div style="height: 245px;"><canvas id="funnelChart"></canvas></div>
        <div class="pipeline-mini-pills">
            <div class="pipeline-mini-pill">
                <span class="pill-label">🔍 Reviewed</span>
                <span class="pill-val" style="color: #818cf8;"><?= (int)($funnelData['reviewed'] ?? 0) ?></span>
            </div>
            <div class="pipeline-mini-pill">
                <span class="pill-label">🟢 Interviewing</span>
                <span class="pill-val" style="color: #38bdf8;"><?= (int)($funnelData['interviewing'] ?? 0) ?></span>
            </div>
            <div class="pipeline-mini-pill">
                <span class="pill-label">🎉 Hired</span>
                <span class="pill-val" style="color: #10b981;"><?= (int)($funnelData['hired'] ?? 0) ?></span>
            </div>
            <div class="pipeline-mini-pill">
                <span class="pill-label">🛑 Rejected</span>
                <span class="pill-val" style="color: #f87171;"><?= (int)($funnelData['rejected'] ?? 0) ?></span>
            </div>
        </div>
    </div>

    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">🗓️ Applications by Submission Date</h2>
        <p class="analytics-card-subtitle">Proportional breakdown of candidate submissions across recent active dates.</p>
        <div style="height: 300px;"><canvas id="trendChart"></canvas></div>
    </div>
</div>

<!-- 4. Row 3: AI Match Score Tier Distribution (Pie) + Portfolio Volume by Employment Type (Pie/Polar) -->
<div class="analytics-grid-2">
    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">⚡ Candidate AI Compatibility Tiers</h2>
        <p class="analytics-card-subtitle">Quality segmentation of all evaluated CVs across your job postings.</p>
        <div style="height: 280px;"><canvas id="scoreTierPieChart"></canvas></div>
    </div>

    <div class="card">
        <h2 style="margin-bottom: 1rem; font-size: 1.2rem;">💼 Activity by Employment Type</h2>
        <p class="analytics-card-subtitle">Distribution of candidate interest across Full-Time, Contract, and Flexible roles.</p>
        <div style="height: 280px;"><canvas id="employmentPieChart"></canvas></div>
    </div>
</div>

<!-- 5. Row 4: Top 5 Highest-Scoring Candidates Leaderboard Across All Jobs -->
<div class="card" style="margin-bottom: 2rem;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.15rem;">
        <div>
            <h2 style="margin: 0; font-size: 1.2rem;">🏆 Top 5 Highest-Scoring Candidates Across All Jobs</h2>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.82rem; color: #94a3b8;">Instant leaderboard of your strongest AI-verified matches ready for interview or offer.</p>
        </div>
    </div>

    <?php if (empty($topCandidates)): ?>
        <p style="color: #94a3b8; text-align: center; padding: 1.5rem 0; margin: 0;">No candidate applications have been scored yet.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table style="width: 100%; border-collapse: collapse; min-width: 0;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.1); text-align: left;">
                        <th style="padding: 0.75rem 1rem; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase;">AI Match</th>
                        <th style="padding: 0.75rem 1rem; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase;">Candidate</th>
                        <th style="padding: 0.75rem 1rem; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase;">Applied Position</th>
                        <th style="padding: 0.75rem 1rem; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase;">ATS Stage</th>
                        <th style="padding: 0.75rem 1rem; color: #94a3b8; font-size: 0.76rem; text-transform: uppercase; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topCandidates as $tc): ?>
                        <?php
                            $tcScore = (int)($tc['ai_score'] ?? 0);
                            $tcColor = $tcScore >= 80 ? '#10b981' : ($tcScore >= 50 ? '#f59e0b' : '#ef4444');
                            $tcStatus = strtolower(trim((string)($tc['status'] ?? 'reviewed')));
                            if ($tcStatus === 'interviewing') {
                                $tcBadge = '<span style="background:rgba(14,165,233,0.16);color:#38bdf8;border:1px solid rgba(14,165,233,0.4);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;">🟢 Interviewing</span>';
                            } elseif ($tcStatus === 'hired' || $tcStatus === 'offered') {
                                $tcBadge = '<span style="background:rgba(16,185,129,0.16);color:#34d399;border:1px solid rgba(16,185,129,0.4);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;">🎉 Hired</span>';
                            } elseif ($tcStatus === 'rejected') {
                                $tcBadge = '<span style="background:rgba(239,68,68,0.15);color:#f87171;border:1px solid rgba(239,68,68,0.35);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;">🛑 Rejected</span>';
                            } else {
                                $tcBadge = '<span style="background:rgba(99,102,241,0.15);color:#a5b4fc;border:1px solid rgba(99,102,241,0.35);padding:3px 10px;border-radius:999px;font-size:0.72rem;font-weight:700;">🔍 Reviewed</span>';
                            }
                        ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <td style="padding: 0.75rem 1rem; font-weight: 800; font-size: 1.05rem; color: <?= $tcColor ?>;">
                                <?= $tcScore ?>%
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <strong style="color: #f8fafc; display: block;"><?= htmlspecialchars($tc['name']) ?></strong>
                                <span style="font-size: 0.78rem; color: #94a3b8;"><?= htmlspecialchars($tc['email']) ?></span>
                            </td>
                            <td style="padding: 0.75rem 1rem; color: #cbd5e1; font-weight: 600;">
                                <?= htmlspecialchars($tc['job_title']) ?>
                            </td>
                            <td style="padding: 0.75rem 1rem;">
                                <?= $tcBadge ?>
                            </td>
                            <td style="padding: 0.75rem 1rem; text-align: right;">
                                <a href="/applicant/view?id=<?= (int)$tc['applicant_id'] ?>" class="btn btn-sm btn-ghost" style="color: #38bdf8; border: 1px solid rgba(56,189,248,0.5); padding: 0.32rem 0.8rem; font-size: 0.76rem;">
                                    👁️ View Profile
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const chartTextColor = '#e2e8f0';
    const chartMutedColor = '#94a3b8';
    const chartGridColor = 'rgba(255, 255, 255, 0.08)';
    const distinctPalette = [
        '#f59e0b', // Amber / Orange
        '#10b981', // Emerald Green
        '#38bdf8', // Electric Sky Blue
        '#8b5cf6', // Vivid Purple
        '#ec4899', // Pink Rose
        '#f43f5e', // Coral Red
        '#14b8a6'  // Teal
    ];

    // -------------------------------------------------------------------------
    // 1. Top Jobs by Applicant Volume (Multi-Color Horizontal Bar Chart)
    // -------------------------------------------------------------------------
    const topJobLabels = <?= json_encode(array_column($topJobs, 'title')) ?>;
    const topJobCounts = <?= json_encode(array_map('intval', array_column($topJobs, 'applicant_count'))) ?>;
    const topJobAvgScores = <?= json_encode(array_map('floatval', array_column($topJobs, 'avg_score'))) ?>;
    const topJobMaxScores = <?= json_encode(array_map('intval', array_column($topJobs, 'max_score'))) ?>;

    new Chart(document.getElementById('topJobsChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: topJobLabels.length ? topJobLabels : ['No Jobs Posted Yet'],
            datasets: [{
                label: 'Applicants',
                data: topJobCounts.length ? topJobCounts : [0],
                backgroundColor: distinctPalette.slice(0, Math.max(1, topJobLabels.length)),
                borderColor: distinctPalette.slice(0, Math.max(1, topJobLabels.length)),
                borderWidth: 1,
                borderRadius: 6
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.parsed.x + ' Applicants';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    ticks: { color: chartMutedColor, stepSize: 1 },
                    grid: { color: chartGridColor }
                },
                y: {
                    ticks: { color: chartTextColor, font: { weight: '600' } },
                    grid: { display: false }
                }
            }
        }
    });

    // -------------------------------------------------------------------------
    // 2. AI Match Quality by Job Posting (Dual Line Chart: Avg vs Top Score)
    // -------------------------------------------------------------------------
    new Chart(document.getElementById('jobQualityLineChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: topJobLabels.length ? topJobLabels : ['No Jobs'],
            datasets: [
                {
                    label: 'Highest Candidate Score (%)',
                    data: topJobMaxScores.length ? topJobMaxScores : [0],
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.14)',
                    pointBackgroundColor: '#10b981',
                    pointRadius: 5,
                    tension: 0.35,
                    fill: true
                },
                {
                    label: 'Average AI Match Score (%)',
                    data: topJobAvgScores.length ? topJobAvgScores : [0],
                    borderColor: '#8b5cf6',
                    backgroundColor: 'rgba(139, 92, 246, 0.14)',
                    pointBackgroundColor: '#8b5cf6',
                    pointRadius: 5,
                    tension: 0.35,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: chartTextColor, padding: 14 }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        color: chartMutedColor,
                        callback: function(v) { return v + '%'; }
                    },
                    grid: { color: chartGridColor }
                },
                x: {
                    ticks: { color: chartTextColor },
                    grid: { color: chartGridColor }
                }
            }
        }
    });

    // -------------------------------------------------------------------------
    // 3. ATS Recruitment Pipeline (Doughnut Chart)
    // -------------------------------------------------------------------------
    const funnelValues = [
        <?= (int)($funnelData['reviewed'] ?? 0) ?>,
        <?= (int)($funnelData['interviewing'] ?? 0) ?>,
        <?= (int)($funnelData['hired'] ?? 0) ?>,
        <?= (int)($funnelData['rejected'] ?? 0) ?>
    ];
    const funnelTotal = funnelValues.reduce((a, b) => a + b, 0);

    new Chart(document.getElementById('funnelChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: ['Reviewed / Pending', 'Interviewing', 'Hired / Offered', 'Rejected'],
            datasets: [{
                data: funnelTotal > 0 ? funnelValues : [1, 0, 0, 0],
                backgroundColor: ['#6366f1', '#38bdf8', '#10b981', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '62%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: chartTextColor, padding: 14 }
                }
            }
        }
    });

    // -------------------------------------------------------------------------
    // 4. Applications by Submission Date (Multi-Color Pie Chart)
    // -------------------------------------------------------------------------
    const trendLabels = <?= json_encode($trendData['labels']) ?>;
    const trendCounts = <?= json_encode($trendData['data']) ?>;

    new Chart(document.getElementById('trendChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: trendLabels.length ? trendLabels : ['No Submissions Yet'],
            datasets: [{
                label: 'Applications',
                data: trendCounts.length ? trendCounts : [1],
                backgroundColor: distinctPalette.slice(0, Math.max(1, trendLabels.length)),
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: chartTextColor, padding: 14 }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.label + ': ' + ctx.parsed + ' application(s)';
                        }
                    }
                }
            }
        }
    });

    // -------------------------------------------------------------------------
    // 5. Candidate AI Compatibility Tiers (Pie Chart)
    // -------------------------------------------------------------------------
    const tierLabels = <?= json_encode(array_keys($scoreDistribution)) ?>;
    const tierValues = <?= json_encode(array_values($scoreDistribution)) ?>;
    const tierTotal  = tierValues.reduce((a, b) => a + b, 0);

    new Chart(document.getElementById('scoreTierPieChart').getContext('2d'), {
        type: 'pie',
        data: {
            labels: tierLabels,
            datasets: [{
                data: tierTotal > 0 ? tierValues : [1, 0, 0],
                backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: chartTextColor, padding: 14 }
                }
            }
        }
    });

    // -------------------------------------------------------------------------
    // 6. Activity by Employment Type (Polar Area / Multi-Slice Chart)
    // -------------------------------------------------------------------------
    const empLabels = <?= json_encode($employmentBreakdown['labels']) ?>;
    const empValues = <?= json_encode($employmentBreakdown['data']) ?>;

    new Chart(document.getElementById('employmentPieChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: empLabels.length ? empLabels : ['Full-time'],
            datasets: [{
                data: empValues.length ? empValues : [1],
                backgroundColor: ['#f59e0b', '#38bdf8', '#10b981', '#8b5cf6', '#ec4899'],
                borderWidth: 0,
                hoverOffset: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '50%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: chartTextColor, padding: 14 }
                }
            }
        }
    });
</script>
