<div class="admin-wrapper-view">
    <div style="margin-bottom: 2rem;">
        <h2 style="margin-bottom: 0.5rem;">Platform Analytics</h2>
        <p class="text-muted">Overview of platform growth and tenant activity.</p>
    </div>

    <!-- Metric Counters -->
    <div class="admin-counters-grid">
        <div class="admin-metric-card">
            <h3>Total Applications</h3>
            <p class="count-total"><?php echo htmlspecialchars($analytics['total_applications']); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Jobs (Last 30 Days)</h3>
            <p class="count-listings"><?php echo htmlspecialchars($analytics['recent_jobs']); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Active Tenants</h3>
            <p class="count-active"><?php echo htmlspecialchars($analytics['total_tenants']); ?></p>
        </div>
    </div>
	
    <!-- Charts Container: Changed to grid-2 to make charts larger and more detailed -->
    <div class="grid-2" style="gap: 1.5rem; margin-top: 2rem;">
        <!-- Pie Chart -->
        <div class="admin-data-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; font-size: 0.9rem;">Engagement</h3>
            <canvas id="pieChart"></canvas>
        </div>

        <!-- Line Chart -->
        <div class="admin-data-card" style="padding: 1.5rem;">
            <h3 style="margin-bottom: 1.5rem; font-size: 0.9rem;">Growth (Jobs/Day)</h3>
            <canvas id="growthChart"></canvas>
        </div>

        <!-- Bar Chart (Will drop to the next row and have more space) -->
        <div class="admin-data-card" style="padding: 1.5rem; grid-column: 1 / -1;">
            <h3 style="margin-bottom: 1.5rem; font-size: 0.9rem;">Top 5 Tenants</h3>
            <canvas id="tenantChart" height="100"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const primaryColor = '#8b5cf6';
    const textColor = '#f3f4f6';
    const gridColor = 'rgba(255, 255, 255, 0.1)';

    // 1. Pie Chart
    new Chart(document.getElementById('pieChart'), {
        type: 'pie',
        data: {
            labels: ['Recent Jobs', 'Total Applications'], // Label updated
            datasets: [{
                data: [<?php echo $analytics['pie_data']['jobs']; ?>, <?php echo $analytics['pie_data']['applicants']; ?>],
                backgroundColor: ['#8b5cf6', '#10b981'],
                borderWidth: 0 // Removes the white border around slices for a cleaner dark mode look
            }]
        },
        options: { 
            plugins: { 
                legend: { 
                    position: 'bottom', // Legend moved to bottom
                    labels: { color: textColor, padding: 20 } 
                } 
            } 
        }
    });

    // 2. Growth Line Chart
    new Chart(document.getElementById('growthChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($analytics['growth_chart'], 'date')); ?>,
            datasets: [{
                label: 'Jobs',
                data: <?php echo json_encode(array_column($analytics['growth_chart'], 'count')); ?>,
                borderColor: primaryColor,
                backgroundColor: 'rgba(139, 92, 246, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } },
                x: { ticks: { color: textColor }, grid: { color: gridColor } }
            },
            plugins: { 
                legend: { 
                    position: 'bottom', // Legend moved to bottom
                    labels: { color: textColor, padding: 20 } 
                } 
            }
        }
    });

    // 3. Tenant Bar Chart
    new Chart(document.getElementById('tenantChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($analytics['top_tenants_chart'], 'company_name')); ?>,
            datasets: [{
                label: 'Jobs',
                data: <?php echo json_encode(array_column($analytics['top_tenants_chart'], 'job_count')); ?>,
                backgroundColor: primaryColor,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: { ticks: { color: textColor }, grid: { display: false } },
                y: { beginAtZero: true, ticks: { color: textColor, stepSize: 1 }, grid: { color: gridColor } }
            },
            plugins: { 
                legend: { 
                    position: 'bottom', // Legend moved to bottom
                    labels: { color: textColor, padding: 20 } 
                } 
            }
        }
    });
</script>