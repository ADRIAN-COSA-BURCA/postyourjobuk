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
                    <td style="padding: 1rem; max-width: 400px; vertical-align: top;">
                        <strong style="display: block; font-size: 1.05rem; margin-bottom: 0.25rem; color: var(--text);"><?= htmlspecialchars($applicant['name']) ?></strong>
                        
                        <?php if (!empty($applicant['ai_summary'])): ?>
                            <?php 
                                // Strip out the HTML structure tags to show a clean inline preview string
                                $clean_summary_preview = strip_tags($applicant['ai_summary']); 
                            ?>
                            <p style="color: var(--text-muted); font-size: 0.85rem; margin: 0; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis;" title="<?= htmlspecialchars($clean_summary_preview) ?>">
                                <?= htmlspecialchars($clean_summary_preview) ?>
                            </p>
                        <?php endif; ?>
                    </td>
                    <td style="padding: 1rem; color: var(--secondary);"><?= htmlspecialchars($applicant['email']) ?></td>
                    <td style="padding: 1rem;"><?= date('M d, Y', strtotime($applicant['applied_at'] ?? 'now')) ?></td>
                    <td style="padding: 1rem;">
                        <a href="/applicant/view?id=<?= (int)$applicant['applicant_id'] ?>" class="btn btn-sm">Profile</a>
                        <a href="/applicant/download?id=<?= (int)$applicant['applicant_id'] ?>" class="btn btn-sm">CV</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>