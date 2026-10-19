<?php if (isset($_SESSION['duplicate_application_error'])): 
    $dup = $_SESSION['duplicate_application_error'];
    unset($_SESSION['duplicate_application_error']);
?>
<div id="duplicateAppModal" style="position: fixed; inset: 0; background: rgba(7, 10, 18, 0.88); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); display: flex; align-items: center; justify-content: center; z-index: 99999; padding: 1rem;">
    <div class="card" style="background: #111827; border: 1px solid rgba(239, 68, 68, 0.45); border-radius: 14px; max-width: 500px; width: 100%; padding: 2.25rem; box-shadow: 0 25px 60px rgba(0, 0, 0, 0.8); text-align: center;">
        <div style="width: 68px; height: 68px; border-radius: 50%; background: rgba(239, 68, 68, 0.12); border: 2px solid #ef4444; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.25rem; font-size: 2rem;">
            ⚠️
        </div>
        <h2 style="margin: 0 0 0.5rem 0; font-family: 'Outfit', sans-serif; font-size: 1.5rem; color: #ffffff;">Application Already Submitted</h2>
        <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.6; margin-bottom: 1.5rem;">
            You have already applied for <strong style="color: #ffffff;"><?= e($dup['job_title'] ?? $job['title'] ?? 'this role') ?></strong> with the email address <strong style="color: #38bdf8;"><?= e($dup['email'] ?? '') ?></strong>.
        </p>

        <div style="background: #1e293b; border: 1px solid #374151; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: 1.5rem; text-align: left; font-size: 0.88rem; line-height: 1.5;">
            <div style="display: flex; gap: 0.6rem; align-items: flex-start; margin-bottom: 0.5rem;">
                <span>ℹ️</span>
                <span style="color: #cbd5e1;"><strong>Status:</strong> Your profile and CV are already registered in the applicant pool and queued for review.</span>
            </div>
            <div style="display: flex; gap: 0.6rem; align-items: flex-start;">
                <span>🔒</span>
                <span style="color: #94a3b8;">Multiple submissions for the same position with identical email are restricted to protect fair evaluation and data integrity.</span>
            </div>
        </div>

        <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
            <button type="button" class="btn btn-secondary" style="flex: 1 1 140px; padding: 0.75rem 1rem;" onclick="document.getElementById('duplicateAppModal').style.display='none';">
                Close Window
            </button>
            <a href="/" class="btn btn-primary" style="flex: 1 1 160px; padding: 0.75rem 1rem; text-decoration: none;">
                Explore Other Jobs
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (isset($_SESSION['success_message'])): ?>
    <div style="background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-weight: 600;">
        ✓ <?= e($_SESSION['success_message']) ?>
        <?php unset($_SESSION['success_message']); // Clear message so it disappears on refresh ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div style="background-color: #fee2e2; border: 1px solid #ef4444; color: #991b1b; padding: 1rem; border-radius: var(--radius-sm); margin-bottom: 1.5rem; font-weight: 600;">
        ✕ <?= e($_SESSION['error_message']) ?>
        <?php unset($_SESSION['error_message']); ?>
    </div>
<?php endif; ?>


<div class="container" style="max-width: 900px; margin-top: 2rem; margin-bottom: 4rem;">
    
    <div style="margin-bottom: 1.5rem;">
        <a href="/" class="btn" style="background: var(--card-bg); border: 1px solid var(--border); color: var(--text); padding: 0.5rem 1rem; border-radius: var(--radius-sm); font-weight: 600; text-decoration: none; display: inline-block;">
            ← Back to Job Board
        </a>
    </div>

    <div class="card" style="padding: 2.5rem; margin-bottom: 2rem;">
        <div class="flex-between" style="margin-bottom: 1.5rem;">
            <div>
                <span style="color: var(--primary); font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 800;">
                    <?= e($job['company_name'] ?? 'Hiring Company') ?>
                </span>
                <h1 style="margin-top: 0.25rem; margin-bottom: 0.5rem; font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800;">
                    <?= e($job['title']) ?>
                </h1>
            </div>
            <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: var(--primary); align-self: flex-start;">
                <?= ucfirst(str_replace('-', ' ', e($job['employment_type'] ?? 'full-time'))) ?>
            </span>
        </div>

        <div style="display: flex; gap: 2rem; flex-wrap: wrap; padding-top: 1.5rem; border-top: 1px solid var(--border); font-size: 0.95rem;">
            <div>📍 <strong style="color: var(--text);">Location:</strong> <?= e($job['location'] ?? 'Remote') ?></div>
            <div>💷 <strong style="color: var(--text);">Salary:</strong> <?= e($job['salary'] ?? 'Competitive') ?></div>
            <div>📅 <strong style="color: var(--text);">Posted:</strong> <?= time_ago($job['created_at']) ?></div>
        </div>

        <div style="margin-top: 2.5rem; background: var(--bg); border-radius: var(--radius-sm); padding: 1.5rem; border-left: 4px solid var(--primary); border-top: 1px solid var(--border); border-right: 1px solid var(--border); border-bottom: 1px solid var(--border);">
            <h3 style="margin-top: 0; margin-bottom: 1.25rem; font-family: 'Outfit', sans-serif; font-size: 1.2rem;">About the Employer</h3>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; font-size: 0.95rem; margin-bottom: 1rem;">
                <div>
                    <strong style="color: var(--text); display: block; margin-bottom: 0.25rem;">Industry</strong>
                    <span style="color: var(--text-muted);"><?= e($job['industry'] ?? 'Not Specified') ?></span>
                </div>
                <div>
                    <strong style="color: var(--text); display: block; margin-bottom: 0.25rem;">Contact Person</strong>
                    <span style="color: var(--text-muted);"><?= e($job['contact_person'] ?? 'Hiring Manager') ?></span>
                </div>
                <?php if (!empty($job['website_url'])): ?>
                <div>
                    <strong style="color: var(--text); display: block; margin-bottom: 0.25rem;">Website</strong>
                    <a href="<?= e($job['website_url']) ?>" target="_blank" style="color: var(--primary); font-weight: 600; text-decoration: none;">Visit Company Site ↗</a>
                </div>
                <?php endif; ?>
            </div>
            
            <?php if (!empty($job['company_address'])): ?>
            <div style="font-size: 0.95rem; margin-top: 0.5rem;">
                <strong style="color: var(--text); display: block; margin-bottom: 0.25rem;">Headquarters</strong>
                <span style="color: var(--text-muted);"><?= nl2br(e($job['company_address'])) ?></span>
            </div>
            <?php endif; ?>
        </div>
        </div>

    <div class="card" style="padding: 2rem; margin-bottom: 2rem;">
        <h2 style="margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; font-family: 'Outfit', sans-serif;">Job Description</h2>
        <div style="line-height: 1.7; color: var(--text-muted); white-space: pre-line; margin-bottom: 2.5rem;">
            <?= e($job['description']) ?>
        </div>

        <?php if (!empty($job['requirements'])): ?>
            <h2 style="margin-top: 2.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; font-family: 'Outfit', sans-serif;">Requirements & Qualifications</h2>
            <div style="line-height: 1.7; color: var(--text-muted); white-space: pre-line;">
                <?= e($job['requirements']) ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card" style="padding: 2.5rem; border: 1px solid rgba(139, 92, 246, 0.3);">
        <h2 style="margin-bottom: 0.5rem; font-family: 'Outfit', sans-serif;">Apply For This Position</h2>
        <p style="color: var(--text-muted); margin-bottom: 2rem; font-size: 0.95rem;">Submit your application details below. Our automated asynchronous pipeline will process your CV directly.</p>

        <form action="/apply" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <input type="hidden" name="job_id" value="<?= (int)$job['job_id'] ?>">
            <input type="hidden" name="tenant_id" value="<?= (int)$job['tenant_id'] ?>">
            <input type="hidden" name="form_rendered_at" value="<?= time() ?>">

            <!-- Anti-Spam Honeypot Field (Hidden from humans, traps automated bot submission) -->
            <div style="display:none !important; visibility:hidden !important; opacity:0; position:absolute; left:-9999px; pointer-events:none;" aria-hidden="true">
                <label for="candidate_website_url">Website</label>
                <input type="text" name="candidate_website_url" id="candidate_website_url" value="" tabindex="-1" autocomplete="off">
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 1.5rem;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" placeholder="John Doe" required value="<?= e($oldInput['name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" placeholder="johndoe@example.com" required value="<?= e($oldInput['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" placeholder="+44 7123 456789" value="<?= e($oldInput['phone'] ?? '') ?>">
            </div>

            <div style="display: flex; gap: 0.5rem; margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                <button type="button" id="upload-tab" class="btn" style="background: var(--bg); border: 1px solid var(--border); padding: 0.5rem 1rem; font-size: 0.9rem;" onclick="showUploadOption()">📁 Upload CV Document</button>
                <button type="button" id="text-tab" class="btn" style="background: transparent; border: 1px solid transparent; padding: 0.5rem 1rem; font-size: 0.9rem; color: var(--text-muted);" onclick="showTextOption()">📝 Paste Plaintext CV</button>
            </div>

            <div id="upload-option" class="form-group" style="margin-bottom: 2rem;">
                <label class="form-label">Upload CV (PDF or DOCX format)</label>
                <input type="file" id="cv" name="cv_document" class="form-control" accept=".pdf,.docx,.doc">
                <small style="display: block; color: var(--text-muted); font-size: 0.82rem; margin-top: 0.4rem;">
                    Accepted formats: PDF, DOCX (Maximum file size: 5 MB)
                </small>
            </div>

            <div id="text-option" class="form-group" style="display: none; margin-bottom: 2rem;">
                <label class="form-label">Paste CV Text Contents Here</label>
                <textarea id="cv_text" name="cv_text" class="form-control" rows="10" placeholder="Paste full content history..." style="font-family: monospace; font-size: 0.9rem; resize: vertical;"><?= e($oldInput['cv_text'] ?? '') ?></textarea>
            </div>

            <script>
                function showUploadOption() {
                    document.getElementById('upload-option').style.display = 'block';
                    document.getElementById('text-option').style.display = 'none';
                    document.getElementById('upload-tab').style.background = 'var(--bg)';
                    document.getElementById('upload-tab').style.borderColor = 'var(--border)';
                    document.getElementById('text-tab').style.background = 'transparent';
                    document.getElementById('text-tab').style.borderColor = 'transparent';
                    document.getElementById('text-tab').style.color = 'var(--text-muted)';
                    document.getElementById('cv_text').value = '';
                }

                function showTextOption() {
                    document.getElementById('upload-option').style.display = 'none';
                    document.getElementById('text-option').style.display = 'block';
                    document.getElementById('upload-tab').style.background = 'transparent';
                    document.getElementById('upload-tab').style.borderColor = 'transparent';
                    document.getElementById('text-tab').style.background = 'var(--bg)';
                    document.getElementById('text-tab').style.borderColor = 'var(--border)';
                    document.getElementById('text-tab').style.color = 'var(--text)';
                    document.getElementById('cv').value = '';
                }

                window.addEventListener('DOMContentLoaded', () => {
                    if (document.getElementById('cv_text').value.trim().length > 0) {
                        showTextOption();
                    }
                });
            </script>

            <button type="submit" class="btn btn-primary" 
        style="width: 100%; padding: 1rem; font-weight: 600;" 
        onclick="this.disabled=true; this.innerText='Processing...'; this.form.submit();">
    📤 Submit Application
</button>
        </form>
    </div>
</div>