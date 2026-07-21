<div class="page-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <h1 style="margin: 0; color: #ffffff;">Analytics Dashboard</h1>
        <p style="margin: 0.5rem 0 0 0; color: #94a3b8;">Insights for <?= htmlspecialchars($tenant['company_name']) ?></p>
    </div>
    <a href="/dashboard" class="btn btn-ghost">← Back to Dashboard</a>
</div>

<!-- Stats Grid -->
<section class="stats-grid" style="margin-bottom: 2rem !important;">
    <div class="card">
        <h3 style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Jobs</h3>
        <p class="stat-value" style="font-size: 1.875rem; font-weight: 700; color: #ffffff;"><?= (int)$stats['total_jobs'] ?></p>
    </div>
    <div class="card">
        <h3 style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">Active Jobs</h3>
        <p class="stat-value" style="font-size: 1.875rem; font-weight: 700; color: #10b981;"><?= (int)$stats['active_jobs'] ?></p>
    </div>
    <div class="card">
        <h3 style="color: #94a3b8; font-size: 0.875rem; margin-bottom: 0.5rem;">Total Applicants</h3>
        <p class="stat-value" style="font-size: 1.875rem; font-weight: 700; color: #6366f1;"><?= (int)$stats['total_applicants'] ?></p>
    </div>
</section>

<div class="card" style="margin-top: 1.5rem; margin-bottom: 1.5rem">
    <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Top Jobs by Applicant Volume</h2>
    <div style="height: 300px;"><canvas id="topJobsChart"></canvas></div>
</div>

<!-- Recruitment Funnel Chart -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    <!-- Funnel Chart -->
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Recruitment Pipeline</h2>
        <div style="height: 300px;"><canvas id="funnelChart"></canvas></div>
    </div>
    <!-- Trend Line Chart -->
    <div class="card">
        <h2 style="margin-bottom: 1.5rem; font-size: 1.25rem;">Applications (7 Days)</h2>
        <div style="height: 300px;"><canvas id="trendChart"></canvas></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>
    const topJobsCtx = document.getElementById('topJobsChart').getContext('2d');
    new Chart(topJobsCtx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($topJobs, 'title')) ?>,
            datasets: [{
                label: 'Applicants',
                data: <?= json_encode(array_column($topJobs, 'applicant_count')) ?>,
                backgroundColor: '#10b981'
            }]
        },
        options: { 
            indexAxis: 'y', // Makes it horizontal!
            responsive: true, 
            maintainAspectRatio: false ,
			plugins: {
                legend: { position: 'bottom' } // Legend moved to bottom
            }
        }
    });
</script>

<script>
    // 1. Funnel Chart
    const funnelData = <?= json_encode(array_values($funnelData)) ?>;
    const funnelLabels = <?= json_encode(array_map('ucfirst', array_keys($funnelData))) ?>;
    const funnelCtx = document.getElementById('funnelChart').getContext('2d');
    
    new Chart(funnelCtx, {
        type: 'doughnut',
        data: {
            labels: funnelLabels,
            datasets: [{
                data: funnelData,
                backgroundColor: ['#6366f1', '#f59e0b', '#10b981', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' } // Legend moved to bottom
		} }
    });

    // 2. Trend Line Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    new Chart(trendCtx, {
        type: 'bar', // Changed from 'line' to 'bar'
        data: {
            labels: <?= json_encode($trendData['labels']) ?>,
            datasets: [{
                label: 'New Applicants',
                data: <?= json_encode($trendData['data']) ?>,
                backgroundColor: '#6366f1',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' } // Legend moved to bottom
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
</script>

