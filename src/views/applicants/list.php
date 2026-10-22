<style>
    .filter-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        background: #1f2937;
        border: 1px solid #374151;
        padding: 0.85rem 1.15rem;
        border-radius: 8px;
    }
    .filter-toolbar-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .filter-input {
        padding: 0.5rem 0.75rem !important;
        background: #111827 !important;
        border: 1px solid #374151 !important;
        border-radius: 6px;
        color: #fff !important;
        font-size: 0.85rem !important;
        outline: none;
    }
    .filter-input:focus {
        border-color: #6366f1 !important;
    }
    .filter-select {
        width: auto !important;
        min-width: 145px;
        flex: 0 1 auto;
        padding: 0.5rem 2.15rem 0.5rem 0.75rem !important;
        background-color: #111827 !important;
        border: 1px solid #374151 !important;
        border-radius: 6px;
        color: #fff !important;
        font-size: 0.84rem !important;
        outline: none;
        cursor: pointer;
        color-scheme: dark;
    }
    .filter-select option {
        background-color: #111827 !important;
        color: #ffffff !important;
    }
    .filter-select option:checked,
    .filter-select option:hover {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }
    .filter-select:focus {
        border-color: #6366f1 !important;
    }

    /* Override global .table-responsive nowrap / min-width rules on desktop */
    .responsive-applicant-table {
        width: 100% !important;
        min-width: 0 !important;
        border-collapse: collapse !important;
        table-layout: auto;
    }
    .responsive-applicant-table th,
    .responsive-applicant-table td {
        white-space: normal !important;
        min-width: 0 !important;
        padding: 0.95rem 0.85rem !important;
        vertical-align: middle !important;
    }

    .dashboard-action-links {
        display: flex;
        gap: 0.4rem;
        align-items: center;
        flex-wrap: wrap;
        justify-content: flex-end;
    }
    .dashboard-action-links form {
        display: inline-flex;
        margin: 0;
    }
    .dashboard-action-links .btn {
        border-radius: var(--radius-pill) !important;
        padding: 0.36rem 0.75rem !important;
        font-size: 0.76rem !important;
        white-space: nowrap;
    }

    .pipeline-action-bar {
        display: flex;
        gap: 0.38rem;
        align-items: center;
        flex-wrap: wrap;
        margin-top: 0.45rem;
    }
    .pipeline-action-bar form {
        display: inline-flex;
        margin: 0;
    }
    .pipeline-btn {
        font-size: 0.74rem !important;
        padding: 0.32rem 0.68rem !important;
        border-radius: 9999px !important;
        font-weight: 600 !important;
        cursor: pointer;
        transition: all 0.15s ease;
        background: transparent;
        white-space: nowrap;
    }
    .pipeline-btn:disabled {
        opacity: 0.42;
        cursor: not-allowed;
    }
    .pipeline-btn-interview {
        color: #38bdf8;
        border: 1px solid rgba(56, 189, 248, 0.55);
    }
    .pipeline-btn-interview:hover:not(:disabled) {
        background: rgba(56, 189, 248, 0.15);
    }
    .pipeline-btn-hire {
        color: #10b981;
        border: 1px solid rgba(16, 185, 129, 0.55);
    }
    .pipeline-btn-hire:hover:not(:disabled) {
        background: rgba(16, 185, 129, 0.15);
    }
    .pipeline-btn-reject {
        color: #f87171;
        border: 1px solid rgba(248, 113, 113, 0.45);
    }
    .pipeline-btn-reject:hover:not(:disabled) {
        background: rgba(248, 113, 113, 0.15);
    }

    /* ATS Stage Badges */
    .status-badge-flag {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 0.24rem 0.65rem;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.03em;
        white-space: nowrap;
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
    .stage-failed {
        background: rgba(245, 158, 11, 0.16);
        color: #fbbf24;
        border: 1px solid rgba(245, 158, 11, 0.4);
    }

    /* Mobile Responsive Cards (Screens under 768px) */
    @media (max-width: 768px) {
        .card,
        .table-responsive {
            min-width: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
            overflow: visible !important;
        }

        .filter-toolbar {
            flex-direction: column;
            align-items: stretch;
            gap: 0.75rem;
        }
        .filter-toolbar-group {
            flex-direction: column;
            align-items: stretch;
        }
        .filter-input, .filter-select {
            width: 100% !important;
            box-sizing: border-box;
        }

        .responsive-applicant-table thead {
            display: none;
        }

        .responsive-applicant-table,
        .responsive-applicant-table tbody,
        .responsive-applicant-table tr,
        .responsive-applicant-table td,
        .responsive-applicant-table .td-content,
        .responsive-applicant-table .dashboard-action-links,
        .responsive-applicant-table .pipeline-action-bar,
        .responsive-applicant-table .btn {
            display: block;
            width: 100% !important;
            min-width: 0 !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            white-space: normal !important;
        }

        .responsive-applicant-table tr {
            margin-bottom: 1.5rem;
            border: 1px solid #374151;
            border-radius: 0.5rem;
            background: #1e293b;
            padding: 1rem;
        }

        .responsive-applicant-table td {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            padding: 0.75rem 0 !important;
            margin: 0 !important;
            text-align: left !important;
            border-bottom: 1px solid #334155;
        }
        .responsive-applicant-table td:last-child {
            border-bottom: none;
            padding-bottom: 0 !important;
        }

        .responsive-applicant-table td::before {
            content: attr(data-label);
            font-weight: 700;
            color: #94a3b8;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.35rem;
        }

        .responsive-applicant-table .dashboard-action-links,
        .responsive-applicant-table .pipeline-action-bar {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            width: 100%;
            justify-content: stretch;
        }
        .responsive-applicant-table .dashboard-action-links a,
        .responsive-applicant-table .dashboard-action-links form,
        .responsive-applicant-table .dashboard-action-links button,
        .responsive-applicant-table .pipeline-action-bar form,
        .responsive-applicant-table .pipeline-action-bar button {
            display: block;
            width: 100% !important;
            text-align: center;
            padding: 0.6rem !important;
            border-radius: var(--radius-pill) !important;
            box-sizing: border-box !important;
            font-size: 0.84rem !important;
        }
        .responsive-applicant-table .dashboard-action-links form,
        .responsive-applicant-table .pipeline-action-bar form {
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            background: transparent !important;
        }
    }
</style>

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
    <div class="app-stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
        <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="form-label">Total Applicants</div>
                <div class="stat-value">
                    <?= (int)($stats['total_applicants'] ?? 0) ?>
                </div>
            </div>

            <?php if ((int)($stats['total_applicants'] ?? 0) > 0): ?>
                <form id="massDeleteForm" method="POST" action="/applicant/delete-all" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                    <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                    
                    <button type="button" onclick="showDeleteModal()" class="btn btn-sm" style="background: rgba(239, 68, 68, 0.12); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.45); border-radius: 6px; cursor: pointer; padding: 0.35rem 0.65rem; font-weight: 700; font-size: 0.74rem;">
                        ⚠️ Wipe All
                    </button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="form-label">Interviewing</div>
            <div class="stat-value">
                <?= (int)($stats['interviewing_count'] ?? 0) ?>
            </div>
        </div>

        <div class="card">
            <div class="form-label">Hired / Offered</div>
            <div class="stat-value">
                <?= (int)($stats['hired_count'] ?? 0) ?>
            </div>
        </div>

        <div class="card">
            <div class="form-label">Rejected</div>
            <div class="stat-value">
                <?= (int)($stats['rejected_count'] ?? 0) ?>
            </div>
        </div>
    </div>

    <div id="customDeleteModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
        <div style="background: white; padding: 2rem; border-radius: 8px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🚨</div>
            <h3 style="margin-top: 0; color: var(--danger, #dc3545); font-size: 1.25rem;">CRITICAL WARNING</h3>
            <p style="color: #444; margin-bottom: 1.5rem; line-height: 1.5;">
                Are you absolutely sure you want to delete ALL candidates and their CVs for this job? <br><br>
                <strong>This will wipe cloud storage and CANNOT be undone.</strong>
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center;">
                <button type="button" onclick="hideDeleteModal()" class="btn" style="background: #e2e8f0; color: #333; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                    Cancel
                </button>
                <button type="button" onclick="submitMassDelete()" class="btn" style="background: var(--danger, #dc3545); color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-weight: 500;">
                    Yes, Delete All
                </button>
            </div>
        </div>
    </div>

    <script>
        function showDeleteModal() {
            document.getElementById('customDeleteModal').style.display = 'flex';
        }
        function hideDeleteModal() {
            document.getElementById('customDeleteModal').style.display = 'none';
        }
        function submitMassDelete() {
            document.getElementById('massDeleteForm').submit();
        }
    </script>
    <?php endif; ?>

    <div class="card">
        <!-- APPLICANTS FILTER & SORT TOOLBAR -->
        <div class="filter-toolbar">
            <div style="flex: 1 1 200px; min-width: 180px;">
                <input type="text" id="candidateSearchInput" placeholder="🔍 Search candidate name or email..." class="filter-input" style="width: 100%; box-sizing: border-box;">
            </div>
            <div class="filter-toolbar-group">
                <select id="statusFilterSelect" class="filter-select">
                    <option value="all">All Pipeline Stages</option>
                    <option value="reviewed">🔍 Reviewed / Pending</option>
                    <option value="interviewing">🟢 Interviewing</option>
                    <option value="hired">🎉 Hired / Offered</option>
                    <option value="rejected">🛑 Rejected</option>
                </select>
                <select id="scoreFilterSelect" class="filter-select">
                    <option value="all">All Match Scores</option>
                    <option value="80">Score ≥ 80% (High Match)</option>
                    <option value="50">Score ≥ 50% (Qualified)</option>
                    <option value="low">Score &lt; 50% (Low / Non-Fit)</option>
                </select>
                <select id="limitFilterSelect" class="filter-select">
                    <option value="all">Show All</option>
                    <option value="5">Top 5 Candidates</option>
                    <option value="10">Top 10 Candidates</option>
                </select>
                <select id="candidateSortSelect" class="filter-select">
                    <option value="score_desc">Score (Highest First)</option>
                    <option value="score_asc">Score (Lowest First)</option>
                    <option value="date_desc">Newest Submission</option>
                    <option value="date_asc">Oldest Submission</option>
                    <option value="name_asc">Name (A - Z)</option>
                    <option value="name_desc">Name (Z - A)</option>
                </select>
                <span id="candidateCountBadge" style="font-size: 0.78rem; color: #94a3b8; white-space: nowrap; padding-left: 0.25rem;">
                    Showing <?= count($applicants) ?> candidates
                </span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="responsive-applicant-table">
                <thead>
                    <tr style="text-align: left; border-bottom: 2px solid var(--border);">
                        <th style="color: #94a3b8; width: 90px;">Match</th>
                        <th style="color: #94a3b8; width: 32%;">Candidate Profile</th>
                        <th style="color: #94a3b8; width: 34%;">ATS Pipeline &amp; Follow-Up</th>
                        <th style="color: #94a3b8; width: 26%; text-align: right;">Profile &amp; CV Actions</th>
                    </tr>
                </thead>
                <tbody id="applicantTableBody">
                    <?php if (empty($applicants)): ?>
                        <tr id="emptyInitialRow">
                            <td colspan="4" style="text-align: center; color: #6b7280; padding: 2rem;">
                                No applications received for this job position yet.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applicants as $applicant): ?>
                            <?php
                                $score = (int)($applicant['ai_score'] ?? 0);
                                if ($score >= 80) {
                                    $scoreColor = '#10b981'; // Green
                                } elseif ($score >= 50) {
                                    $scoreColor = '#f59e0b'; // Amber
                                } else {
                                    $scoreColor = '#ef4444'; // Red
                                }
                                $appliedTimestamp = strtotime($applicant['applied_at'] ?? ($applicant['created_at'] ?? 'now'));
                                $rawStatus = strtolower(trim((string)($applicant['status'] ?? 'reviewed')));

                                if ($rawStatus === 'interviewing') {
                                    $stageBadgeClass = 'stage-interviewing';
                                    $stageLabel = '🟢 Interviewing';
                                    $filterStage = 'interviewing';
                                } elseif ($rawStatus === 'hired' || $rawStatus === 'offered') {
                                    $stageBadgeClass = 'stage-hired';
                                    $stageLabel = '🎉 Hired / Offered';
                                    $filterStage = 'hired';
                                } elseif ($rawStatus === 'rejected') {
                                    $stageBadgeClass = 'stage-rejected';
                                    $stageLabel = '🛑 Rejected';
                                    $filterStage = 'rejected';
                                } elseif ($rawStatus === 'processing_failed') {
                                    $stageBadgeClass = 'stage-failed';
                                    $stageLabel = '⚠️ Needs Review';
                                    $filterStage = 'reviewed';
                                } else {
                                    $stageBadgeClass = 'stage-reviewed';
                                    $stageLabel = '🔍 Reviewed';
                                    $filterStage = 'reviewed';
                                }
                            ?>
                            <tr class="applicant-row" 
                                data-score="<?= $score ?>"
                                data-status="<?= htmlspecialchars($filterStage) ?>"
                                data-name="<?= htmlspecialchars(strtolower($applicant['name'] ?? '')) ?>"
                                data-email="<?= htmlspecialchars(strtolower($applicant['email'] ?? '')) ?>"
                                data-date="<?= $appliedTimestamp ?>"
                                style="border-bottom: 1px solid var(--border);">
                                
                                <!-- Column 1: Match Score & Date -->
                                <td data-label="Match Index">
                                    <div class="td-content">
                                        <span style="font-weight: 800; font-size: 1.2rem; color: <?= $scoreColor ?>; display: block; line-height: 1.2;">
                                            <?= $score ?>%
                                        </span>
                                        <span style="font-size: 0.74rem; color: #9ca3af; display: block; margin-top: 0.2rem;">
                                            <?= date('M d, Y', $appliedTimestamp) ?>
                                        </span>
                                    </div>
                                </td>

                                <!-- Column 2: Candidate Name, Email & Compact Summary -->
                                <td data-label="Candidate Profile">
                                    <div class="td-content">
                                        <strong style="font-size: 1.02rem; color: var(--text); display: block; line-height: 1.3;">
                                            <?= htmlspecialchars($applicant['name']) ?>
                                        </strong>
                                        <span style="font-size: 0.82rem; color: #a5b4fc; display: block; margin: 0.15rem 0 0.35rem 0; word-break: break-all;">
                                            <?= htmlspecialchars($applicant['email']) ?>
                                        </span>
                                        
                                        <?php if (!empty($applicant['ai_summary'])): ?>
                                            <?php 
                                                $clean_summary_preview = strip_tags($applicant['ai_summary']); 
                                            ?>
                                            <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0; line-height: 1.38; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; max-width: 310px;" title="<?= htmlspecialchars($clean_summary_preview) ?>">
                                                <?= htmlspecialchars($clean_summary_preview) ?>
                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </td>

                                <!-- Column 3: Current Stage + Horizontal ATS Follow-Up Buttons -->
                                <td data-label="ATS Pipeline & Follow-Up">
                                    <div class="td-content">
                                        <div>
                                            <span class="status-badge-flag <?= $stageBadgeClass ?>">
                                                <?= $stageLabel ?>
                                            </span>
                                        </div>

                                        <div class="pipeline-action-bar">
                                            <form method="POST" action="/applicant/status" onsubmit="return confirm('Invite <?= htmlspecialchars(addslashes($applicant['name'])) ?> to Interview? This will automatically send an official Interview Invitation email with your recruiter contact details.');">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                                                <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                                                <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                                                <input type="hidden" name="return_to" value="list">
                                                <input type="hidden" name="status" value="interviewing">
                                                <button type="submit" class="pipeline-btn pipeline-btn-interview" <?= $rawStatus === 'interviewing' ? 'disabled title="Already in Interviewing stage"' : '' ?>>
                                                    🟢 Interview
                                                </button>
                                            </form>

                                            <form method="POST" action="/applicant/status" onsubmit="return confirm('Extend Job Offer to <?= htmlspecialchars(addslashes($applicant['name'])) ?>? This will automatically send an official Congratulatory Job Offer email.');">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                                                <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                                                <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                                                <input type="hidden" name="return_to" value="list">
                                                <input type="hidden" name="status" value="hired">
                                                <button type="submit" class="pipeline-btn pipeline-btn-hire" <?= $rawStatus === 'hired' ? 'disabled title="Already marked as Hired"' : '' ?>>
                                                    🎉 Offer
                                                </button>
                                            </form>

                                            <form method="POST" action="/applicant/status" onsubmit="return confirm('Mark <?= htmlspecialchars(addslashes($applicant['name'])) ?> as Rejected? (Silent status update — no email will be sent)');">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                                                <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                                                <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                                                <input type="hidden" name="return_to" value="list">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="pipeline-btn pipeline-btn-reject" <?= $rawStatus === 'rejected' ? 'disabled title="Already marked as Rejected"' : '' ?>>
                                                    🛑 Reject
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>

                                <!-- Column 4: Profile & CV Actions -->
                                <td data-label="Profile & CV Actions">
                                    <div class="td-content">
                                        <div class="dashboard-action-links">
                                            <a href="/applicant/view?id=<?= (int)$applicant['applicant_id'] ?>" 
                                               class="btn btn-sm btn-ghost" 
                                               style="color: #3b82f6; border: 1px solid #3b82f6;">
                                                👁️ Overview
                                            </a>
                                            
                                            <a href="/applicant/download?id=<?= (int)$applicant['applicant_id'] ?>" 
                                               class="btn btn-sm btn-ghost" 
                                               style="color: #10b981; border: 1px solid #10b981;">
                                                📥 CV
                                            </a>
                                            
                                            <form method="POST" action="/applicant/delete" style="display:inline; margin:0;" onsubmit="return confirm('Are you sure you want to permanently delete this candidate and their CV? This cannot be undone.');">
                                                <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                                                <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                                                <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                                                <button type="submit" class="btn btn-sm btn-ghost" style="color: #ef4444; border: 1px solid #ef4444; background: transparent; cursor: pointer;">
                                                    🗑️ Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    <tr id="noResultsFilterRow" style="display: none;">
                        <td colspan="4" style="text-align: center; color: #94a3b8; padding: 2rem;">
                            No candidates match the selected filter criteria.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('candidateSearchInput');
    const statusFilter = document.getElementById('statusFilterSelect');
    const scoreFilter = document.getElementById('scoreFilterSelect');
    const limitFilter = document.getElementById('limitFilterSelect');
    const sortSelect = document.getElementById('candidateSortSelect');
    const countBadge = document.getElementById('candidateCountBadge');
    const tableBody = document.getElementById('applicantTableBody');
    const noResultsRow = document.getElementById('noResultsFilterRow');

    function applyApplicantFilters() {
        const query = searchInput.value.trim().toLowerCase();
        const statusVal = statusFilter ? statusFilter.value : 'all';
        const scoreVal = scoreFilter.value;
        const limitVal = limitFilter.value;
        const sortVal = sortSelect.value;

        const allRows = Array.from(document.querySelectorAll('.applicant-row'));
        if (allRows.length === 0) return;

        // 1. Filter rows
        let visibleRows = allRows.filter(row => {
            const name = row.getAttribute('data-name') || '';
            const email = row.getAttribute('data-email') || '';
            const status = row.getAttribute('data-status') || 'reviewed';
            const score = parseInt(row.getAttribute('data-score') || '0', 10);

            // Search filter
            if (query && !name.includes(query) && !email.includes(query)) {
                return false;
            }

            // Pipeline Stage filter
            if (statusVal !== 'all' && status !== statusVal) {
                return false;
            }

            // Score filter
            if (scoreVal === '80' && score < 80) return false;
            if (scoreVal === '50' && score < 50) return false;
            if (scoreVal === 'low' && score >= 50) return false;

            return true;
        });

        // 2. Sort visible rows
        visibleRows.sort((a, b) => {
            const scoreA = parseInt(a.getAttribute('data-score') || '0', 10);
            const scoreB = parseInt(b.getAttribute('data-score') || '0', 10);
            const dateA = parseInt(a.getAttribute('data-date') || '0', 10);
            const dateB = parseInt(b.getAttribute('data-date') || '0', 10);
            const nameA = a.getAttribute('data-name') || '';
            const nameB = b.getAttribute('data-name') || '';

            switch (sortVal) {
                case 'score_desc': return scoreB - scoreA;
                case 'score_asc':  return scoreA - scoreB;
                case 'date_desc':  return dateB - dateA;
                case 'date_asc':   return dateA - dateB;
                case 'name_asc':   return nameA.localeCompare(nameB);
                case 'name_desc':  return nameB.localeCompare(nameA);
                default:           return scoreB - scoreA;
            }
        });

        // 3. Hide all rows initially
        allRows.forEach(r => r.style.display = 'none');

        // 4. Apply Limit (Top 5 / Top 10)
        let displayLimit = visibleRows.length;
        if (limitVal === '5') displayLimit = Math.min(5, visibleRows.length);
        if (limitVal === '10') displayLimit = Math.min(10, visibleRows.length);

        const rowsToDisplay = visibleRows.slice(0, displayLimit);

        // Re-append in sorted order and show
        rowsToDisplay.forEach(row => {
            row.style.display = '';
            tableBody.appendChild(row);
        });

        // Keep no results row at bottom
        tableBody.appendChild(noResultsRow);

        // Update counter & empty state
        countBadge.textContent = `Showing ${rowsToDisplay.length} of ${allRows.length} candidates`;
        if (rowsToDisplay.length === 0) {
            noResultsRow.style.display = '';
        } else {
            noResultsRow.style.display = 'none';
        }
    }

    searchInput.addEventListener('input', applyApplicantFilters);
    if (statusFilter) statusFilter.addEventListener('change', applyApplicantFilters);
    scoreFilter.addEventListener('change', applyApplicantFilters);
    limitFilter.addEventListener('change', applyApplicantFilters);
    sortSelect.addEventListener('change', applyApplicantFilters);

    // Initial run
    applyApplicantFilters();
});
</script>
