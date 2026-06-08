<?php

?>

<div class="portal-page">
    <?php if ($successMessage): ?>
        <div class="alert alert-success">✅ <?php echo e($successMessage); ?></div>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <div class="alert alert-error">⚠️ <?php echo e($errorMessage); ?></div>
    <?php endif; ?>

    <div class="dashboard-intro">
        <div>
            <h1>Welcome back, <?php echo e($companyName); ?> 👋</h1>
            <p>Monitor jobs, review applicants, and manage your recruitment activity from one place.</p>
        </div>
        <a href="<?php echo BASE_URL; ?>/portal/create-job.php" class="btn btn-primary">➕ Create New Job</a>
    </div>

    <div class="dashboard-stats-grid">
        <div class="dashboard-stat-card">
            <div class="dashboard-stat-label">Active Jobs</div>
            <div class="dashboard-stat-value dashboard-stat-blue"><?php echo $stats['active_jobs']; ?></div>
        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-label">Total Jobs</div>
            <div class="dashboard-stat-value dashboard-stat-green"><?php echo $stats['total_jobs']; ?></div>
        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-label">Total Applicants</div>
            <div class="dashboard-stat-value dashboard-stat-purple"><?php echo $stats['total_applicants']; ?></div>
        </div>

        <div class="dashboard-stat-card">
            <div class="dashboard-stat-label">Recent (7 days)</div>
            <div class="dashboard-stat-value dashboard-stat-amber"><?php echo $stats['recent_applicants']; ?></div>
        </div>
    </div>

    <div class="dashboard-table-card">
        <div class="dashboard-table-head">
            <h2>Your Job Postings</h2>
            <?php if (!empty($jobs)): ?>
                <span class="dashboard-subtle"><?php echo count($jobs); ?> total job<?php echo count($jobs) === 1 ? '' : 's'; ?></span>
            <?php endif; ?>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="dashboard-empty-state">
                <p>📭 No jobs posted yet</p>
                <p>Create your first job posting to start receiving applications.</p>
                <a href="<?php echo BASE_URL; ?>/portal/create-job.php" class="btn btn-primary dashboard-empty-cta">Create Your First Job</a>
            </div>
        <?php else: ?>
            <div class="dashboard-jobs-table-wrap">
                <table class="dashboard-jobs-table">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Posted</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jobs as $job): ?>
                            <?php
                                $statusClass = 'dashboard-status-closed';
                                if ($job['status'] === 'active') {
                                    $statusClass = 'dashboard-status-active';
                                } elseif ($job['status'] === 'inactive') {
                                    $statusClass = 'dashboard-status-inactive';
                                }
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo e($job['title']); ?></strong>
                                    <span class="dashboard-subtle"><?php echo e(ucfirst(str_replace('-', ' ', $job['employment_type']))); ?></span>
                                </td>
                                <td class="dashboard-table-muted">
                                    <?php echo e($job['location'] ?? 'Remote'); ?>
                                </td>
                                <td>
                                    <span class="dashboard-status-pill <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($job['status']); ?>
                                    </span>
                                </td>
                                <td class="dashboard-table-muted">
                                    <?php echo time_ago($job['created_at']); ?>
                                </td>
                                <td>
                                    <div class="dashboard-action-links">
                                        <a href="<?php echo BASE_URL; ?>/portal/applicants.php?job_id=<?php echo $job['job_id']; ?>" class="dashboard-action-link dashboard-action-blue">👥 Applicants</a>
                                        <a href="<?php echo BASE_URL; ?>/portal/edit-job.php?id=<?php echo $job['job_id']; ?>" class="dashboard-action-link dashboard-action-green">✏️ Edit</a>
                                        <a href="<?php echo BASE_URL; ?>/portal/delete-job.php?id=<?php echo $job['job_id']; ?>" class="dashboard-action-link dashboard-action-red" onclick="return confirm('Are you sure you want to delete this job?');">🗑️ Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>