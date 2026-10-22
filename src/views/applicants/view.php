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

    /* ATS Pipeline Control Panel on Candidate View */
    .ats-pipeline-panel {
        background: rgba(15, 23, 42, 0.65);
        border: 1px solid rgba(139, 92, 246, 0.3);
        border-radius: var(--radius-md);
        padding: 1.15rem 1.35rem;
        margin-bottom: 1.5rem;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
    }
    .ats-pipeline-buttons {
        display: flex;
        flex-wrap: wrap;
        gap: 0.6rem;
        align-items: center;
    }
    .ats-pipeline-buttons form {
        margin: 0;
        display: inline-flex;
    }
    .ats-btn {
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.84rem;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s ease;
        background: transparent;
    }
    .ats-btn:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .ats-btn-interview {
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.6);
        background: rgba(14, 165, 233, 0.1);
    }
    .ats-btn-interview:hover:not(:disabled) {
        background: rgba(14, 165, 233, 0.22);
    }
    .ats-btn-hire {
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.6);
        background: rgba(16, 185, 129, 0.1);
    }
    .ats-btn-hire:hover:not(:disabled) {
        background: rgba(16, 185, 129, 0.22);
    }
    .ats-btn-reject {
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.5);
        background: rgba(239, 68, 68, 0.08);
    }
    .ats-btn-reject:hover:not(:disabled) {
        background: rgba(239, 68, 68, 0.2);
    }

    .status-badge-flag {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.85rem;
        border-radius: 9999px;
        font-size: 0.8rem;
        font-weight: 700;
        margin-top: 0.5rem;
    }
    .stage-reviewed {
        background: rgba(99, 102, 241, 0.15);
        color: #a5b4fc;
        border: 1px solid rgba(99, 102, 241, 0.35);
    }
    .stage-interviewing {
        background: rgba(14, 165, 233, 0.16);
        color: #38bdf8;
        border: 1px solid rgba(14, 165, 233, 0.4);
    }
    .stage-hired {
        background: rgba(16, 185, 129, 0.16);
        color: #34d399;
        border: 1px solid rgba(16, 185, 129, 0.4);
    }
    .stage-rejected {
        background: rgba(239, 68, 68, 0.15);
        color: #f87171;
        border: 1px solid rgba(239, 68, 68, 0.35);
    }

    @media (max-width: 640px) {
        .cv-document-card,
        .ats-pipeline-panel {
            flex-direction: column;
            align-items: stretch;
            text-align: left;
        }
        .cv-document-card .btn,
        .ats-pipeline-buttons,
        .ats-pipeline-buttons form,
        .ats-pipeline-buttons button {
            width: 100%;
            text-align: center;
            justify-content: center;
        }
    }
</style>

<?php
    $rawStatus = strtolower(trim((string)($applicant['status'] ?? 'reviewed')));
    if ($rawStatus === 'interviewing') {
        $stageBadgeClass = 'stage-interviewing';
        $stageLabel = '🟢 Interviewing Stage';
    } elseif ($rawStatus === 'hired' || $rawStatus === 'offered') {
        $stageBadgeClass = 'stage-hired';
        $stageLabel = '🎉 Hired / Offer Extended';
    } elseif ($rawStatus === 'rejected') {
        $stageBadgeClass = 'stage-rejected';
        $stageLabel = '🛑 Rejected';
    } else {
        $stageBadgeClass = 'stage-reviewed';
        $stageLabel = '🔍 Reviewed / Under Evaluation';
    }
?>

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
                <p class="text-muted" style="font-size: 0.9rem; margin-bottom: 0.4rem;">Applied for <strong><?= htmlspecialchars($job['title']) ?></strong></p>
                <span class="status-badge-flag <?= $stageBadgeClass ?>">
                    <?= $stageLabel ?>
                </span>
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
                <p class="text-muted mb-3">Review candidate professional information, AI evaluation, and trigger automated hiring pipeline communications below.</p>
                
                <?php if (isset($applicant['ai_score'])): ?>
                    <div class="alert alert-success" style="display: flex; align-items: center; margin-bottom: 1.25rem; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); color: #10b981; padding: 0.85rem 1.25rem; border-radius: var(--radius-md); font-weight: 500; width: 100%; box-sizing: border-box;">
                        ⚡ <strong style="margin-right: 0.5rem; margin-left: 0.25rem;">Match Score:</strong> This candidate has been evaluated with a <?= (int)$applicant['ai_score'] ?>% matching compatibility index.
                    </div>
                <?php endif; ?>

                <!-- ATS Recruitment Pipeline Action Panel -->
                <div class="ats-pipeline-panel">
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: #a78bfa; margin-bottom: 0.2rem;">
                            ATS Pipeline &amp; Automated Candidate Follow-Up
                        </div>
                        <div style="font-size: 0.85rem; color: var(--text-muted);">
                            Select an action to transition candidate stage and send branded email notifications.
                        </div>
                    </div>

                    <div class="ats-pipeline-buttons">
                        <form method="POST" action="/applicant/status" onsubmit="return confirm('Invite <?= htmlspecialchars(addslashes($applicant['name'])) ?> to Interview? This will send an official Interview Invitation email with your Recruiter Business Card.');">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                            <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                            <input type="hidden" name="job_id" value="<?= (int)$applicant['job_id'] ?>">
                            <input type="hidden" name="return_to" value="view">
                            <input type="hidden" name="status" value="interviewing">
                            <button type="submit" class="ats-btn ats-btn-interview" <?= $rawStatus === 'interviewing' ? 'disabled title="Candidate is already in Interviewing stage"' : '' ?>>
                                🟢 Invite to Interview
                            </button>
                        </form>

                        <form method="POST" action="/applicant/status" onsubmit="return confirm('Extend Official Job Offer to <?= htmlspecialchars(addslashes($applicant['name'])) ?>? This will send a Congratulatory Job Offer email.');">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                            <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                            <input type="hidden" name="job_id" value="<?= (int)$applicant['job_id'] ?>">
                            <input type="hidden" name="return_to" value="view">
                            <input type="hidden" name="status" value="hired">
                            <button type="submit" class="ats-btn ats-btn-hire" <?= $rawStatus === 'hired' ? 'disabled title="Candidate is already marked as Hired"' : '' ?>>
                                🎉 Extend Offer
                            </button>
                        </form>

                        <form method="POST" action="/applicant/status" onsubmit="return confirm('Mark <?= htmlspecialchars(addslashes($applicant['name'])) ?> as Rejected? (Silent database update — no email sent)');">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                            <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                            <input type="hidden" name="job_id" value="<?= (int)$applicant['job_id'] ?>">
                            <input type="hidden" name="return_to" value="view">
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="ats-btn ats-btn-reject" <?= $rawStatus === 'rejected' ? 'disabled title="Candidate is already marked as Rejected"' : '' ?>>
                                🛑 Reject
                            </button>
                        </form>
                    </div>
                </div>

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
