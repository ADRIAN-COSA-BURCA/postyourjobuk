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
                <input type="file" id="cv" name="cv" class="form-control" accept=".pdf,.docx,.doc">
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

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1rem; font-weight: 600;">📤 Submit Application</button>
        </form>
    </div>
</div>