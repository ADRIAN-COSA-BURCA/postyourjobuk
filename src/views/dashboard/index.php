<div class="dashboard-container dashboard-flow">
    <h1>Welcome to your Dashboard</h1>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
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
        <div class="flex-between" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h2>Your Current Postings</h2>
            <a href="/jobs/create" class="btn btn-primary">+ Create New Job</a>
        </div>
        
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Applicants</th>
                        <th>Posted</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($jobs)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center; color: #d7dae0; padding: 2rem;">
                                No job postings detected inside this corporate tenant workspace.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <?php 
                                // Map semantic conditional statuses to CSS flags cleanly
                                $statusStr = strtolower($job['status']);
                                $statusClass = ($statusStr === 'active') ? 'badge-success' : 'badge-muted';
                            ?>
                            <tr>
                                <td style="font-weight: 500; color: #4f46e5;">
                                    <?= htmlspecialchars($job['title']) ?>
                                </td>
                                <td>
                                    <span class="badge <?= $statusClass ?>">
                                        <?= $statusStr === 'active' ? '✅ Active' : '⏸️ Inactive' ?>
                                    </span>
                                </td>
                                <td>
                                    <strong style="color: #4f46e5;"><?= (int)$job['applicant_count'] ?></strong> applicants
                                </td>
                                <td style="color: #6b7280; font-size: 0.875rem;">
                                    <?= function_exists('time_ago') ? time_ago($job['created_at']) : htmlspecialchars($job['created_at']) ?>
                                </td>
                                <td>
                                    <div class="dashboard-action-links" style="display: flex; gap: 0.75rem;">
                                        <a href="/applicants?job_id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #2563eb;">
                                            👥 Applicants
                                        </a>
                                        <a href="/jobs/edit?id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #16a34a;">
                                            ✏️ Edit
                                        </a>
                                        <a href="/jobs/delete?id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #dc2626;" onclick="return confirm('Are you sure you want to delete this job posting? This will permanently wipe all associated applicants and evaluation data.');">
                                            🗑️ Delete
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</div>