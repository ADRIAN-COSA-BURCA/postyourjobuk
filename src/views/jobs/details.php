<div class="container" style="max-width: 800px; margin-top: 2rem;">
    <div style="margin-bottom: 1.5rem;">
        <a href="/" class="btn btn-secondary">← Back to Job Board</a>
    </div>

    <div class="card">
        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 1.5rem; margin-bottom: 1.5rem;">
            <h1 style="margin-bottom: 0.5rem; font-family: 'Outfit', sans-serif;"><?php echo e($job['title']); ?></h1>
            
            <div style="display: flex; gap: 1rem; color: var(--text-muted); font-size: 0.95rem;">
                <span class="badge badge-tech">📍 <?php echo e($job['location'] ?? 'Not specified'); ?></span>
                <span class="badge badge-tech">💼 <?php echo ucwords(str_replace('-', ' ', e($job['employment_type'] ?? 'N/A'))); ?></span>
                <span class="badge badge-tech">💰 <?php echo e($job['salary'] ?? 'Competitive'); ?></span>
            </div>
        </div>

        <div class="summary-box">
            <h3>Job Description</h3>
            <p><?php echo nl2br(e($job['description'])); ?></p>

            <?php if (!empty($job['requirements'])): ?>
                <h3>Requirements & Qualifications</h3>
                <p><?php echo nl2br(e($job['requirements'])); ?></p>
            <?php endif; ?>
        </div>
        
        <div style="margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem;">
            <a href="mailto:company@email.com" class="btn btn-primary">Apply Now</a>
        </div>
    </div>
</div>