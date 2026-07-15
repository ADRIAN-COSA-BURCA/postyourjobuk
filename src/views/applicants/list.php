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
        
        <div class="card" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <div class="form-label">Total Applicants</div>
                <div style="font-size: 1.5rem; font-weight: bold; color: var(--primary);">
                    <?= (int)$stats['total_applicants'] ?>
                </div>
            </div>

            <?php if ((int)$stats['total_applicants'] > 0): ?>
                <form id="massDeleteForm" method="POST" action="/applicant/delete-all" style="margin: 0;">
                    <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                    <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                    
                    <button type="button" onclick="showDeleteModal()" class="btn btn-sm" style="background: transparent; color: var(--danger, #dc3545); border: 1px solid var(--danger, #dc3545); border-radius: 4px; cursor: pointer; padding: 0.4rem 0.8rem; font-weight: bold;">
                        ⚠️ Wipe All Candidates
                    </button>
                </form>
            <?php endif; ?>
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
            // Use 'flex' to center the modal content properly
            document.getElementById('customDeleteModal').style.display = 'flex';
        }
        
        function hideDeleteModal() {
            document.getElementById('customDeleteModal').style.display = 'none';
        }
        
        function submitMassDelete() {
            // Once they confirm, we submit the hidden form via JavaScript
            document.getElementById('massDeleteForm').submit();
        }
    </script>
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
                        
                        <form method="POST" action="/applicant/delete" style="display:inline;" onsubmit="return confirm('Are you sure you want to permanently delete this candidate and their CV? This cannot be undone.');">
                            <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                            <input type="hidden" name="applicant_id" value="<?= (int)$applicant['applicant_id'] ?>">
                            <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background: var(--danger, #dc3545); color: white; border: none; cursor: pointer;">
                                Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>