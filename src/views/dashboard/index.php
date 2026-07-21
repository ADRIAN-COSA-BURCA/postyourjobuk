<div class="card" style="margin-bottom: 2rem; border-top: 4px solid #4f46e5; background: #111827; border: 1px solid #374151;">
        
        <!-- NEW HEADER FLEX CONTAINER -->
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
            <div>
                <h1 style="margin-bottom: 0.5rem; margin-top: 0; color: #ffffff;">Welcome, <?= htmlspecialchars($tenant['company_name'] ?? 'Recruiter') ?></h1>
                <p class="text-muted" style="margin-bottom: 0; color: #94a3b8;">Your Corporate Profile Dashboard</p>
            </div>
            <a href="/dashboard/analytics" class="btn btn-primary">📊 View Analytics</a>
        </div>
        <!-- END NEW HEADER FLEX CONTAINER -->
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; background: #1f2937; padding: 1rem; border-radius: 8px; border: 1px solid #374151;">
            <div>
                <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Industry</span>
                <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['industry'] ?? 'Not Specified') ?></strong>
            </div>
            <div>
                <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Contact Person</span>
                <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['contact_person'] ?? 'Not Specified') ?></strong>
            </div>
            <div>
                <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Email Address</span>
                <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['email'] ?? 'Not Specified') ?></strong>
            </div>
            <div>
                <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Phone Number</span>
                <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['phone_number'] ?? 'Not Specified') ?></strong>
            </div>
            <div>
                <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Website</span>
                <?php if (!empty($tenant['website_url'])): ?>
                    <a href="<?= htmlspecialchars($tenant['website_url']) ?>" target="_blank" style="color: #6366f1; font-weight: 600; text-decoration: none;">Visit Site ↗</a>
                <?php else: ?>
                    <strong style="color: #ffffff;">Not Specified</strong>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($tenant['company_address'])): ?>
        <div style="margin-top: 1.5rem; padding: 0 0.5rem;">
            <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Headquarters / Address</span>
            <p style="margin: 0; font-size: 0.95rem; color: #ffffff;"><?= nl2br(htmlspecialchars($tenant['company_address'])) ?></p>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">✅ <?= htmlspecialchars($_SESSION['success_message']) ?></div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">❌ <?= htmlspecialchars($_SESSION['error_message']) ?></div>
        <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>

    <section class="stats-grid" style="margin-bottom: 2rem !important;">
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
                            <td colspan="3" style="text-align: center; color: #6b7280; padding: 1rem;">
                                No job postings detected inside this corporate tenant workspace.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($jobs as $job): ?>
                            <?php 
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
                                <td class="col-posted" style="color: #9ca3af; font-size: 0.875rem;">
                                    <?= function_exists('time_ago') ? time_ago($job['created_at']) : htmlspecialchars($job['created_at']) ?>
                                </td>
                                <td>
                                    <div class="dashboard-action-links" style="display: flex; gap: 0.75rem;">
                                        <a href="/applicants?job_id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #3b82f6;">👥 Applicants</a>
                                        <a href="/jobs/edit?id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #10b981;">✏️ Edit</a>
                                        <a href="/jobs/delete?id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #ef4444;" onclick="return confirm('Are you sure you want to delete this job posting?');">🗑️ Delete</a>
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