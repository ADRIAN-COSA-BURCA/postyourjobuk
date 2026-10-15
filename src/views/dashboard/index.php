<style>
    /* Mobile-first flex container for the header */
    .dashboard-header-flex {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    /* Mobile-first grid for the company details (1 column) */
    .dashboard-details-grid {
        display: grid;
        grid-template-columns: 1fr; 
        gap: 1rem; 
        background: #1f2937; 
        padding: 1rem; 
        border-radius: 8px; 
        border: 1px solid #374151; 
        align-items: start;
    }

    /* Desktop overrides (Screens wider than 768px) */
    @media (min-width: 768px) {
        .dashboard-header-flex {
            flex-direction: row;
            justify-content: space-between;
        }
        .dashboard-details-grid {
            /* Restores your specific 5-column weighting */
            grid-template-columns: 1fr 1fr 1.8fr 1fr 1fr; 
        }
    }
</style>
<style>
    @media (max-width: 768px) {
    /* let every ancestor shrink */
    .card,
    .table-responsive {
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow: visible !important;
    }

    .job-header-mobile {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch !important;
        text-align: center;
    }

    .responsive-job-table thead { display: none; }

    /* kill any desktop min-width / nowrap */
    .responsive-job-table,
    .responsive-job-table tbody,
    .responsive-job-table tr,
    .responsive-job-table td,
    .responsive-job-table .td-content,
    .responsive-job-table .dashboard-action-links,
    .responsive-job-table .btn {
        display: block;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        white-space: normal !important;
    }

    .responsive-job-table tr {
        margin-bottom: 1.5rem;
        border: 1px solid #374151;
        border-radius: 0.5rem;
        background: #1e293b;
        padding: 1rem;
    }

    .responsive-job-table td {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 0.75rem 0 !important;   /* beats inline padding */
        margin: 0 !important;
        text-align: left !important;
        border-bottom: 1px solid #334155;
    }
    .responsive-job-table td:last-child { border-bottom: none; padding-bottom: 0 !important; }

    .responsive-job-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #94a3b8;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
    }

    .responsive-job-table .dashboard-action-links {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }
    .responsive-job-table .dashboard-action-links a {
        display: block;
        text-align: center;
        padding: 0.6rem;
    }
}
</style>

<div class="card" style="margin-bottom: 2rem; border-top: 4px solid #4f46e5; background: #111827; border: 1px solid #374151;">
        
    <!-- NEW HEADER FLEX CONTAINER -->
    <div class="dashboard-header-flex">
        <div>
            <h1 style="margin-bottom: 0.5rem; margin-top: 0; color: #ffffff;">Welcome, <?= htmlspecialchars($tenant['company_name'] ?? 'Recruiter') ?></h1>
            <p class="text-muted" style="margin-bottom: 0; color: #94a3b8;">Your Corporate Profile Dashboard</p>
        </div>
        <a href="/dashboard/analytics" class="btn btn-primary">📊 View Analytics</a>
    </div>
    <!-- END NEW HEADER FLEX CONTAINER -->
    
    <!-- ADJUSTED GRID COLUMNS -->
    <div class="dashboard-details-grid">
        <div style="min-width: 0;">
            <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Industry</span>
            <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['industry'] ?? 'Not Specified') ?></strong>
        </div>
        <div style="min-width: 0;">
            <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Contact Person</span>
            <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['contact_person'] ?? 'Not Specified') ?></strong>
        </div>
        <div style="min-width: 0;">
            <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Email Address</span>
            <!-- Note: Removed white-space: nowrap so long emails wrap safely on small mobile screens -->
            <strong style="color: #ffffff; display: block; word-wrap: break-word;"><?= htmlspecialchars($tenant['email'] ?? 'Not Specified') ?></strong>
        </div>
        <div style="min-width: 0;">
            <span style="display: block; font-size: 0.875rem; color: #94a3b8; margin-bottom: 0.25rem;">Phone Number</span>
            <strong style="color: #ffffff;"><?= htmlspecialchars($tenant['phone_number'] ?? 'Not Specified') ?></strong>
        </div>
        <div style="min-width: 0;">
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
    <div class="flex-between job-header-mobile" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
        <h2 style="margin: 0;">Your Current Postings</h2>
        <a href="/jobs/create" class="btn btn-primary">+ Create New Job</a>
    </div>
    
    <div class="table-responsive">
        <table class="responsive-job-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: left; padding: 1rem; color: #94a3b8;">Title</th>
                    <th style="text-align: left; padding: 1rem; color: #94a3b8;">Status</th>
                    <th style="text-align: left; padding: 1rem; color: #94a3b8;">Applicants</th>
                    <th style="text-align: left; padding: 1rem; color: #94a3b8;">Posted</th>
                    <th style="text-align: left; padding: 1rem; color: #94a3b8;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; color: #6b7280; padding: 1rem;">
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
                            <td data-label="Title" style="font-weight: 500; color: #4f46e5; padding: 1rem;">
                                <div class="td-content">
                                    <?= htmlspecialchars($job['title']) ?>
                                </div>
                            </td>
                            <td data-label="Status" style="padding: 1rem;">
                                <div class="td-content">
                                    <span class="badge <?= $statusClass ?>">
                                        <?= $statusStr === 'active' ? '✅ Active' : '⏸️ Inactive' ?>
                                    </span>
                                </div>
                            </td>
                            <td data-label="Applicants" style="padding: 1rem;">
                                <div class="td-content">
                                    <strong style="color: #4f46e5;"><?= (int)$job['applicant_count'] ?></strong> applicants
                                </div>
                            </td>
                            <td data-label="Posted" class="col-posted" style="color: #9ca3af; font-size: 0.875rem; padding: 1rem;">
                                <div class="td-content">
                                    <?= function_exists('time_ago') ? time_ago($job['created_at']) : htmlspecialchars($job['created_at']) ?>
                                </div>
                            </td>
                            <td data-label="Actions" style="padding: 1rem;">
                                <div class="td-content">
                                    <div class="dashboard-action-links">
                                        <a href="/applicants?job_id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #3b82f6; border: 1px solid #3b82f6;">👥 Applicants</a>
                                        <a href="/jobs/edit?id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #10b981; border: 1px solid #10b981;">✏️ Edit</a>
                                        <a href="/jobs/delete?id=<?= (int)$job['job_id'] ?>" class="btn btn-sm btn-ghost" style="color: #ef4444; border: 1px solid #ef4444;" onclick="return confirm('Are you sure you want to delete this job posting?');">🗑️ Delete</a>
                                    </div>
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