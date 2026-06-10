

<div class="admin-wrapper-view">
    
    <div style="margin-bottom: 24px;">
        <a href="/admin" class="admin-action-btn" style="background: #64748b; border-color: #64748b;">&larr; Back to Dashboard</a>
    </div>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success_message']); unset($_SESSION['success_message']); ?>
        </div>
    <?php endif; ?>

    <div class="admin-brand-header" style="margin-bottom: 32px;">
        <h1>Tenant: <?= htmlspecialchars($tenant['company_name']); ?></h1>
        <p>Review active routing states, client registration logs, and isolated job database metrics.</p>
    </div>
    
    <div class="admin-data-card" style="margin-bottom: 32px; padding: 24px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
            <div>
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Central Contact Email</span>
                <p style="margin: 4px 0 0 0; font-weight: 600; color: #0f172a;"><?= htmlspecialchars($tenant['email']); ?></p>
            </div>
            <div>
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Current Gateway Status</span>
                <p style="margin: 4px 0 0 0;">
                    <span class="status-badge-flag <?= strtolower($tenant['status']); ?>">
                        <?= ucfirst(htmlspecialchars($tenant['status'])); ?>
                    </span>
                </p>
            </div>
            <div>
                <span style="font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase;">Infrastructure Enrollment</span>
                <p style="margin: 4px 0 0 0; color: #475569;"><?= htmlspecialchars($tenant['created_at']); ?></p>
            </div>
        </div>
    </div>

    <div class="admin-data-card" style="border-color: #fca5a5; margin-bottom: 48px; overflow: hidden;">
        <div class="card-title-bar" style="background: #fef2f2; border-bottom: 1px solid #fca5a5;">
            <h2 style="color: #991b1b;">Infrastructure Gateway Governance</h2>
        </div>
        <div style="padding: 24px;">
            <?php if ($tenant['status'] === 'active'): ?>
                <p style="margin: 0 0 16px 0; color: #475569;">Suspending this client workspace will instantly revoke routing authorization and lock out all associated recruiters.</p>
                <form action="/admin/suspend-tenant?id=<?= $tenant['tenant_id']; ?>" method="POST" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <input type="password" name="password" placeholder="Confirm Admin Password" required class="form-input" style="max-width: 300px; margin: 0;">
                    <button type="submit" class="admin-action-btn" style="background: #dc2626; border-color: #dc2626;">Suspend Client Node</button>
                </form>
            <?php else: ?>
                <p style="margin: 0 0 16px 0; color: #475569;">This workspace is currently **Suspended**. Restoring isolation credentials will instantly re-enable recruitment routing access.</p>
                <form action="/admin/activate-tenant?id=<?= $tenant['tenant_id']; ?>" method="POST" style="display: flex; flex-wrap: wrap; gap: 12px; align-items: center;">
                    <input type="password" name="password" placeholder="Confirm Admin Password" required class="form-input" style="max-width: 300px; margin: 0;">
                    <button type="submit" class="admin-action-btn" style="background: #16a34a; border-color: #16a34a;">Restore Client Node Access</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <div class="admin-brand-header" style="margin-bottom: 20px; border-bottom: none; padding-bottom: 0;">
        <h2>Active Isolated Job Postings</h2>
    </div>

    <div class="admin-data-card">
        <table class="admin-table-view">
            <thead>
                <tr>
                    <th>Job Title Listing</th>
                    <th>Routing State</th>
                    <th>Applicants Checked</th>
                    <th>Created On</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($jobs)): ?>
                    <tr>
                        <td colspan="4" style="text-align: center; color: #94a3b8; font-style: italic; padding: 32px;">No active job records tracked for this tenant node.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($jobs as $job): ?>
                    <tr>
                        <td style="font-weight: 600; color: #0f172a;"><?= htmlspecialchars($job['title']); ?></td>
                        <td>
                            <span class="status-badge-flag <?= strtolower($job['status'] ?? 'active'); ?>">
                                <?= htmlspecialchars(strtolower($job['status'] ?? 'active')); ?>
                            </span>
                        </td>
                        <td style="font-weight: 700; color: #0ea5e9;"><?= (int)$job['applicant_count']; ?> applicants</td>
                        <td style="color: #64748b;"><?= htmlspecialchars($job['created_at']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

