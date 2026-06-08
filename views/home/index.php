

<style>
    .homepage-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 1rem;
        padding: 3rem 1.5rem;
        margin-bottom: 2rem;
        color: white;
        text-align: center;
        box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    }
    .homepage-hero h1 {
        font-size: 2.5rem;
        margin-bottom: 0.5rem;
    }
    .homepage-hero p {
        font-size: 1.125rem;
        opacity: 0.95;
        margin-bottom: 2rem;
    }
    .search-container {
        max-width: 700px;
        margin: 0 auto;
    }
    .search-form {
        display: flex;
        gap: 0.5rem;
        background: white;
        padding: 0.5rem;
        border-radius: 9999px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.18);
    }
    .search-input {
        flex: 1;
        border: none;
        padding: 0.9rem 1.25rem;
        font-size: 1rem;
        border-radius: 9999px;
        outline: none;
    }
    .search-btn {
        border: none;
        background: #3b82f6;
        color: white;
        padding: 0.9rem 1.5rem;
        border-radius: 9999px;
        font-weight: 600;
        cursor: pointer;
    }
    .search-btn:hover { background: #2563eb; }
    .page-section { margin-bottom: 2rem; }
    .job-count {
        text-align: center;
        color: #4b5563;
        font-size: 1rem;
        margin-bottom: 1.5rem;
    }
    .job-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
        gap: 1.5rem;
    }
	
    .job-card {
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.06);
    transition: transform 0.2s, box-shadow 0.2s, border-color 0.2s;
    color: inherit;
    display: block;
}

.job-card:hover {
    transform: translateY(-3px);
    border-color: #93c5fd;
    box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
}
    .job-card-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .job-logo {
        width: 56px;
        height: 56px;
        border-radius: 0.75rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    .job-company-name {
        font-weight: 600;
        color: #4b5563;
        font-size: 0.9rem;
    }
    .job-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: #111827;
    }
    .job-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-bottom: 1rem;
        color: #6b7280;
        font-size: 0.9rem;
    }
    .job-description {
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .job-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #f3f4f6;
        margin-top: 1rem;
    }
    .job-type-badge {
        background: #dbeafe;
        color: #1e40af;
        padding: 0.3rem 0.75rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 600;
    }
    .job-posted {
        color: #9ca3af;
        font-size: 0.85rem;
    }
    .empty-state {
        background: white;
        border-radius: 1rem;
        padding: 3rem 2rem;
        text-align: center;
        box-shadow: 0 1px 3px rgba(0,0,0,0.08);
    }
    .empty-state h2 {
        margin-bottom: 1rem;
        color: #111827;
    }
    .empty-state p {
        color: #6b7280;
        font-size: 1rem;
    }
    .homepage-footer-note {
        text-align: center;
        margin-top: 2rem;
        color: #6b7280;
        font-size: 0.95rem;
    }
    @media (max-width: 768px) {
        .homepage-hero h1 { font-size: 2rem; }
        .search-form { flex-direction: column; border-radius: 1rem; }
        .search-input, .search-btn { border-radius: 0.75rem; width: 100%; }
        .job-grid { grid-template-columns: 1fr; }
        .job-footer { flex-direction: column; align-items: flex-start; }
    }
</style>

<section class="homepage-hero">
    <h1>📋 PostYourJobHere.com</h1>
    <p>Find your dream job from top companies</p>

    <div class="search-container">
        <form method="GET" action="" class="search-form">
            <input
                type="text"
                name="search"
                class="search-input"
                placeholder="Search jobs by title, company, location..."
                value="<?php echo e($searchQuery); ?>"
            >
            <button type="submit" class="search-btn">🔍 Search</button>
        </form>
    </div>
</section>

<?php if ($successMessage): ?>
    <div class="alert alert-success">✅ <?php echo e($successMessage); ?></div>
<?php endif; ?>

<?php if ($errorMessage): ?>
    <div class="alert alert-error">⚠️ <?php echo e($errorMessage); ?></div>
<?php endif; ?>

<section class="page-section">
    <?php if (!empty($jobs)): ?>
        <div class="job-count">
            <?php if ($searchQuery): ?>
                Found <strong><?php echo count($jobs); ?></strong> jobs matching "<?php echo e($searchQuery); ?>"
            <?php else: ?>
                <strong><?php echo count($jobs); ?></strong> jobs available
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($jobs)): ?>
        <div class="empty-state">
            <h2>😕 No jobs found</h2>
            <p>
                <?php if ($searchQuery): ?>
                    Try adjusting your search or <a href="<?php echo BASE_URL; ?>/public/index.php" style="color:#3b82f6;">browse all jobs</a>
                <?php else: ?>
                    No job postings available at the moment. Check back soon!
                <?php endif; ?>
            </p>
        </div>
    <?php else: ?>
        <div class="job-grid">
            <?php foreach ($jobs as $job): ?>
                <a href="<?php echo BASE_URL; ?>/public/job-details.php?id=<?php echo $job['job_id']; ?>" class="job-card">
                    <div class="job-card-header">
                        <div class="job-logo"><?php echo strtoupper(substr($job['company_name'], 0, 2)); ?></div>
                        <div>
                            <div class="job-company-name"><?php echo e($job['company_name']); ?></div>
                        </div>
                    </div>

                    <h2 class="job-title"><?php echo e($job['title']); ?></h2>

                    <div class="job-meta">
                        <?php if ($job['location']): ?>
                            <div>📍 <?php echo e($job['location']); ?></div>
                        <?php else: ?>
                            <div>🌍 Remote</div>
                        <?php endif; ?>

                        <?php if ($job['salary']): ?>
                            <div>💷 <?php echo e($job['salary']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="job-description"><?php echo e($job['description']); ?></div>

                    <div class="job-footer">
                        <span class="job-type-badge"><?php echo ucfirst(str_replace('-', ' ', $job['employment_type'])); ?></span>
                        <span class="job-posted">Posted <?php echo time_ago($job['created_at']); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<div class="homepage-footer-note">
    Browse open roles, search by keyword, or use the navigation above to access recruiter or admin areas.
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
