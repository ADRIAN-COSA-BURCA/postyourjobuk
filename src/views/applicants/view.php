<style>
    .ai-summary-content h3 {
        color: #a78bfa; /* Soft purple variant to pop in dark mode layout themes */
        font-size: 1.05rem;
        font-weight: 600;
        margin-top: 1.5rem;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        letter-spacing: 0.02em;
    }
    .ai-summary-content h3:first-of-type {
        margin-top: 0;
    }
    .ai-summary-content ul {
        margin: 0 0 1.25rem 1.25rem;
        padding: 0;
        list-style-type: disc;
    }
    .ai-summary-content li {
        margin-bottom: 0.4rem;
        color: rgba(255, 255, 255, 0.85);
        line-height: 1.5;
    }
    .ai-summary-content li:last-child {
        margin-bottom: 0;
    }
    .ai-summary-content p {
        color: rgba(255, 255, 255, 0.85);
        margin-bottom: 1rem;
    }
</style>

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
                
                <?php if (isset($applicant['ai_score'])): ?>
                    <div class="alert alert-success" style="display: inline-flex; align-items: center; margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 0.75rem 1rem; border-radius: var(--radius-md); font-weight: 500;">
                        ⚡ <strong style="margin-right: 0.5rem; margin-left: 0.25rem;">Match Score:</strong> This candidate has been evaluated with a <?= (int)$applicant['ai_score'] ?>% matching compatibility index.
                    </div>
                <?php endif; ?>

                <?php if (!empty($applicant['ai_summary'])): ?>
                    <div class="form-group mt-2 mb-4">
                        <label class="form-label" style="text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; color: var(--text-muted);">AI Executive Summary</label>
                        <div class="ai-summary-content" style="background: rgba(255, 255, 255, 0.01); border: 1px solid var(--glass-border); padding: 1.5rem; border-radius: var(--radius-md); color: var(--text); line-height: 1.6; font-size: 0.95rem;">
                            <?= nl2br(strip_tags($applicant['ai_summary'], '<h3><ul><li><strong><b><p><br>')) ?>
                        </div>
                    </div>
                <?php endif; ?>

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