<div class="container" style="margin-top: 2rem;">
    <div style="margin-bottom: 1rem;">
        <a href="/dashboard" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <h1>👥 Applications for: <?= htmlspecialchars($job['title']) ?></h1>
        <div class="app-page-meta">
            Status: 
            <span class="status-badge <?= $job['status'] === 'active' ? 'alert-success' : 'alert-error' ?>">
                <?= $job['status'] === 'active' ? '✅ Active' : '⏸️ Inactive' ?>
            </span>
        </div>
    </div>

    <?php if (!empty($stats)): ?>
    <div class="app-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
        <div class="card">
            <div class="form-label">Total Applicants</div>
            <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">
                <?= (int)$stats['total_applicants'] ?>
            </div>
        </div>
        </div>
    <?php endif; ?>

    <div class="card">
        <table class="app-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                    <th style="padding: 1rem;">Match Index</th>
                    <th style="padding: 1rem;">Full Name</th>
                    <th style="padding: 1rem;">Email</th>
                    <th style="padding: 1rem;">Submission</th>
                    <th style="padding: 1rem;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($applicants as $applicant): ?>
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 1rem; font-weight: bold; color: var(--success);">
                        <?= (int)$applicant['ai_score'] ?>%
                    </td>
                    <td style="padding: 1rem;">
                        <strong><?= htmlspecialchars($applicant['name']) ?></strong>
                        <?php if (!empty($applicant['ai_summary'])): ?>
                            <br><small style="color: var(--muted);"><?= htmlspecialchars($applicant['ai_summary']) ?></small>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; color: var(--secondary);"><?= htmlspecialchars($applicant['email']) ?></td>
                    <td style="padding: 1rem;"><?= date('M d, Y', strtotime($applicant['applied_at'])) ?></td>
                    <td style="padding: 1rem;">
                        <a href="/applicant/view/<?= (int)$applicant['applicant_id'] ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">👁️ Profile</a>
                        <a href="/applicant/download/<?= (int)$applicant['applicant_id'] ?>" class="btn btn-secondary" style="padding: 0.4rem 0.8rem; font-size: 0.8rem;">📥 CV</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>