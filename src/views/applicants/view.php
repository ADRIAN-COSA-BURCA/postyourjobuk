<div class="page-wrapper" style="margin-top: 2rem;">
    <div class="mb-3">
        <a href="/applicants?job_id=<?= (int)$applicant['job_id'] ?>" style="color: var(--text-muted); font-weight: 500;">
            ← Back to Applicants List
        </a>
    </div>

    <div class="grid-3">
        <div class="card" style="grid-column: span 1;">
            <div class="text-center mb-3">
                <div style="width: 70px; height: 70px; background: rgba(139, 92, 246, 0.15); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem auto; font-size: 2rem;">
                    👤
                </div>
                <h2><?= htmlspecialchars($applicant['name']) ?></h2>
                <p class="text-muted" style="font-size: 0.9rem;">Applied for <strong><?= htmlspecialchars($job['title']) ?></strong></p>
            </div>

            <hr style="border: 0; border-top: 1px solid var(--glass-border); margin: 1.5rem 0;">

            <div class="form-group">
                <label class="form-label">Email Address</label>
                <p style="font-weight: 500; color: var(--text);"><?= htmlspecialchars($applicant['email']) ?></p>
            </div>

            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <p style="font-weight: 500; color: var(--text);"><?= htmlspecialchars($applicant['phone'] ?: 'Not provided') ?></p>
            </div>

            <div class="form-group">
                <label class="form-label">Submission Date</label>
                <p style="font-weight: 500; color: var(--text-muted); font-size: 0.9rem;">
                    📅 <?= date('M d, Y — H:i', strtotime($applicant['created_at'] ?? 'now')) ?>
                </p>
            </div>
        </div>

        <div class="card" style="grid-column: span 2; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h2>Application Overview</h2>
                <p class="text-muted mb-3">Review candidate professional information and documentation references below.</p>
                
                <div class="alert alert-warning" style="display: inline-flex; align-items: center;">
                    ⚡ <strong>Match Score:</strong> This candidate has a custom evaluation index entry.
                </div>

                <div class="form-group mt-3">
                    <label class="form-label">Attached CV Reference</label>
                    <p class="text-subtle" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                        Original File: <code><?= htmlspecialchars($applicant['cv_filename']) ?></code>
                    </p>
                </div>
            </div>

            <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 1.25rem; border-radius: var(--radius-md); display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                <div>
                    <h4 style="margin: 0;">Curriculum Vitae Document</h4>
                    <p class="text-muted" style="font-size: 0.8rem; margin: 0;">Click to securely stream file directly from corporate repository storage.</p>
                </div>
                <a href="/applicant/download?id=<?= (int)$applicant['applicant_id'] ?>" class="btn btn-primary btn-sm">
                    📥 Download CV File
                </a>
            </div>
        </div>
    </div>
</div>