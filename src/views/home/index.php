<div class="container" style="margin-top: 2rem;">
    <div class="card" style="margin-bottom: 2rem; padding: 2rem; text-align: center;">
        <h1 style="margin-bottom: 1rem;">📋 PostYourJobHere.com</h1>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Find your dream job from top companies</p>

        <form method="GET" action="/" style="max-width: 600px; margin: 0 auto; display: flex; gap: 0.5rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by title, company, location..." 
                   value="<?= e($searchQuery ?? '') ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <?php if ($successMessage ?? ''): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;">✅ <?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage ?? ''): ?>
        <div class="alert alert-error" style="margin-bottom: 1rem;">⚠️ <?= e($errorMessage) ?></div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 1.5rem;">
        <?php foreach ($jobs as $job): ?>
            <a href="/job?id=<?= $job['job_id'] ?>" class="card" style="text-decoration: none; display: block; transition: transform 0.2s;">
                <div class="flex-between" style="margin-bottom: 1rem;">
                    <div style="font-weight: 800; padding: 0.5rem; background: var(--bg); border-radius: 8px;">
                        <?= strtoupper(substr($job['company_name'], 0, 2)) ?>
                    </div>
                    <span class="badge" style="background: rgba(139, 92, 246, 0.1); color: var(--primary);">
                        <?= ucfirst(str_replace('-', ' ', e($job['employment_type']))) ?>
                    </span>
                </div>

                <h3 style="margin-bottom: 0.5rem;"><?= e($job['title']) ?></h3>
                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1rem;">
                    📍 <?= e($job['location'] ?? 'Remote') ?> | 💷 <?= e($job['salary'] ?? 'Competitive') ?>
                </p>

                <div class="text-muted" style="font-size: 0.8rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    Posted <?= time_ago($job['created_at']) ?>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</div>