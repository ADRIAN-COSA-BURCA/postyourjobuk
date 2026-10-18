<style>
    .ai-summary-card {
        background: rgba(255, 255, 255, 0.015);
        border: 1px solid var(--glass-border);
        padding: 1.25rem 1.5rem;
        border-radius: var(--radius-md);
        color: var(--text);
        line-height: 1.6;
        font-size: 0.95rem;
        margin-bottom: 1rem;
        width: 100%;
        box-sizing: border-box;
        transition: border-color 0.2s ease, background 0.2s ease;
    }
    .ai-summary-card:last-child {
        margin-bottom: 0;
    }
    .ai-summary-card:hover {
        background: rgba(255, 255, 255, 0.025);
        border-color: rgba(255, 255, 255, 0.15);
    }
    .ai-summary-card h3 {
        color: #a78bfa;
        font-size: 1.05rem;
        font-weight: 600;
        margin: 0 0 0.85rem 0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        letter-spacing: 0.02em;
    }
    .ai-summary-card .ai-card-body {
        color: rgba(255, 255, 255, 0.88);
    }
    .ai-summary-card ul {
        margin: 0 0 0 1.25rem;
        padding: 0;
        list-style-type: disc;
    }
    .ai-summary-card ol {
        margin: 0 0 0 1.25rem;
        padding: 0;
    }
    .ai-summary-card li {
        margin-bottom: 0.45rem;
        color: rgba(255, 255, 255, 0.88);
        line-height: 1.55;
    }
    .ai-summary-card ol li {
        margin-bottom: 0.85rem;
    }
    .ai-summary-card li:last-child {
        margin-bottom: 0;
    }
    .ai-summary-card p {
        color: rgba(255, 255, 255, 0.85);
        margin: 0;
    }
    .ai-summary-card code {
        background: rgba(255, 255, 255, 0.08);
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.88em;
    }
    .cv-document-card {
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--glass-border);
        padding: 1.25rem;
        border-radius: var(--radius-md);
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        margin-top: 1.5rem;
        width: 100%;
        box-sizing: border-box;
    }
    @media (max-width: 640px) {
        .cv-document-card {
            flex-direction: column;
            align-items: stretch;
            text-align: left;
        }
        .cv-document-card .btn {
            width: 100%;
            text-align: center;
            justify-content: center;
            margin-top: 0.5rem;
        }
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
                    <div class="alert alert-success" style="display: flex; align-items: center; margin-bottom: 1.5rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 0.85rem 1.25rem; border-radius: var(--radius-md); font-weight: 500; width: 100%; box-sizing: border-box;">
                        ⚡ <strong style="margin-right: 0.5rem; margin-left: 0.25rem;">Match Score:</strong> This candidate has been evaluated with a <?= (int)$applicant['ai_score'] ?>% matching compatibility index.
                    </div>
                <?php endif; ?>

                <?php if (!empty($applicant['ai_summary'])): ?>
                    <div class="form-group mt-2 mb-4">
                        <label class="form-label" style="text-transform: uppercase; letter-spacing: 0.05em; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 0.75rem; display: block;">AI Executive Summary</label>
                        <?php
                            $allowedTags = '<h3><h4><ul><ol><li><strong><b><p><br><span><em><div><code><small>';
                            $cleanSummary = strip_tags($applicant['ai_summary'], $allowedTags);
                            
                            // Check for review notice before the first <h3>
                            $firstH3Pos = stripos($cleanSummary, '<h3>');
                            if ($firstH3Pos !== false && $firstH3Pos > 0) {
                                $prefixNotice = trim(substr($cleanSummary, 0, $firstH3Pos));
                                if (!empty($prefixNotice)) {
                                    echo '<div style="margin-bottom: 1rem;">' . $prefixNotice . '</div>';
                                }
                            }
                            
                            // Split into distinct cards by <h3>
                            preg_match_all('/(<h3>.*?<\/h3>)(.*?)(?=(?:<h3>|$))/is', $cleanSummary, $matches, PREG_SET_ORDER);
                            
                            if (!empty($matches)) {
                                foreach ($matches as $match) {
                                    $heading = trim($match[1]);
                                    $body = trim($match[2]);
                                    ?>
                                    <div class="ai-summary-card">
                                        <?= $heading ?>
                                        <div class="ai-card-body">
                                            <?= $body ?>
                                        </div>
                                    </div>
                                    <?php
                                }
                            } else {
                                ?>
                                <div class="ai-summary-card">
                                    <?= (strpos($cleanSummary, '<') !== false) ? $cleanSummary : nl2br(htmlspecialchars($applicant['ai_summary'])) ?>
                                </div>
                                <?php
                            }
                        ?>
                    </div>
                <?php endif; ?>

                <div class="form-group mt-3">
                    <label class="form-label">Attached CV Reference</label>
                    <p class="text-subtle" style="font-size: 0.85rem; margin-bottom: 0.5rem;">
                        Original File: <code><?= htmlspecialchars($applicant['cv_filename']) ?></code>
                    </p>
                </div>
            </div>

            <div class="cv-document-card">
                <div>
                    <h4 style="margin: 0; color: var(--text);">Curriculum Vitae Document</h4>
                    <p class="text-muted" style="font-size: 0.85rem; margin: 0.25rem 0 0 0;">Click to securely stream file directly from corporate repository storage.</p>
                </div>
                <a href="/applicant/download?id=<?= (int)$applicant['applicant_id'] ?>" class="btn btn-primary btn-sm" style="white-space: nowrap;">
                    📥 Download CV File
                </a>
            </div>
        </div>
    </div>
</div>