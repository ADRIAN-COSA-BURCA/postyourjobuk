<h1><?php echo e($job['title']); ?></h1>
<p>Location: <?php echo e($job['location']); ?></p>
<div class="job-description">
    <?php echo nl2br(e($job['description'])); ?>
</div>
<a href="<?= BASE_URL ?>/" class="btn">Back to Jobs</a>