<div class="container" style="margin-top: 2rem;">
    <!-- Search Banner -->
    <div class="card" style="margin-bottom: 2rem; padding: 2rem; text-align: center;">
        <h1 style="margin-bottom: 1rem;">📋 PostYourJobUK</h1>
        <p style="color: var(--text-muted); margin-bottom: 1.5rem;">Find your dream job from top companies</p>

        <form method="GET" action="/" style="max-width: 600px; margin: 0 auto; display: flex; gap: 0.5rem;">
            <input type="text" name="search" class="form-control" placeholder="Search by title, company, location..." 
                   value="<?= e($searchQuery ?? '') ?>">
            <button type="submit" class="btn btn-primary">Search</button>
        </form>
    </div>

    <!-- Alerts -->
    <?php if ($successMessage ?? ''): ?>
        <div class="alert alert-success" style="margin-bottom: 1rem;">✅ <?= e($successMessage) ?></div>
    <?php endif; ?>

    <?php if ($errorMessage ?? ''): ?>
        <div class="alert alert-error" style="margin-bottom: 1rem;">⚠️ <?= e($errorMessage) ?></div>
    <?php endif; ?>

    
    <!-- Industry News Section -->
    <div class="card" style="margin-bottom: 2.5rem; padding: 2rem;">
        <div class="flex-between" style="margin-bottom: 1.25rem; border-bottom: 2px solid var(--primary); padding-bottom: 0.5rem;">
            <h3 style="display: flex; align-items: center; gap: 0.5rem; margin: 0;">
                📰 Industry News & Insights
            </h3>
            <a href="/api/refresh-news" class="btn btn-ghost btn-sm" style="font-size: 0.75rem; padding: 0.25rem 0.75rem;">
                🔄 Refresh News
            </a>
        </div>
        
        <?php if (!empty($recentNews)): ?>
            <!-- Scrollable Track Container -->
            <div style="display: flex; gap: 1.5rem; overflow-x: auto; padding-bottom: 1rem; scroll-snap-type: x mandatory; scrollbar-width: thin; scrollbar-color: var(--primary) var(--bg);">
                <?php foreach ($recentNews as $article): ?>
                    <div style="min-width: 300px; max-width: 320px; flex: 0 0 auto; scroll-snap-align: start; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border); border-radius: var(--radius-sm); padding: 1.25rem; display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <a href="<?= e($article['link']) ?>" target="_blank" style="text-decoration: none; font-weight: bold; color: inherit; display: block; margin-bottom: 0.5rem; font-size: 1rem; line-height: 1.4;">
                                <?= e($article['title']) ?>
                            </a>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1rem; line-height: 1.5;">
                                <?= e($article['description']) ?>
                            </p>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; border-top: 1px solid var(--border); padding-top: 0.75rem;">
                            <span>🌐 <?= e($article['source_name']) ?></span>
                            <span>🕒 <?= time_ago($article['published_at']) ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="font-size: 0.9rem; color: var(--text-muted); text-align: center; padding: 1.5rem 0;">
                No recent news available at the moment. Click "Refresh News" above to load feeds.
            </p>
        <?php endif; ?>
    </div>

    <!-- Main Section: Job Listings (Full Width) -->
    <div style="margin-bottom: 3rem;">
        <h3 style="margin-bottom: 1.5rem;">Latest Opportunities</h3>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
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
</div>