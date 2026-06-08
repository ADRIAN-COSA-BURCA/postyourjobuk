<?php
// src/views/home/index.php

// Ensure your variables are defined (optional but good practice)
$jobs = $jobs ?? []; 
?>



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
