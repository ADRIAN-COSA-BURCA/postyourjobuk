<div class="dashboard-container">
    <h1>Welcome to your Dashboard</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <section class="stats-grid">
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

    <section class="card">
        <div class="flex-between">
            <h2>Your Current Postings</h2>
            <a href="/jobs/create" class="btn btn-primary">+ Create New Job</a>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Applicants</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($jobs as $job): ?>
                <tr>
                    <td><?= htmlspecialchars($job['title']) ?></td>
                    <td><?= htmlspecialchars($job['status']) ?></td>
                    <td><?= (int)$job['applicant_count'] ?></td>
                    <td><a href="/applicants?job_id=<?= (int)$job['job_id'] ?>">View Apps</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>