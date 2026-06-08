<div class="page-narrow">
    <div class="card">
        <h1>➕ Create New Job Posting</h1>
        
        <?php 
        // Syncing with the controller's error session key
        if (!empty($_SESSION['error_message'])): ?>
            <div class="alert alert-error">⚠️ <?= htmlspecialchars($_SESSION['error_message']) ?></div>
            <?php unset($_SESSION['error_message']); // Clear after display ?>
        <?php endif; ?>

        <form method="POST" action="/jobs/store">
            <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
            
            <div class="form-group">
                <label>Job Title *</label>
                <input type="text" name="title" class="form-input" required>
            </div>

            <div class="form-group">
                <label>Description *</label>
                <textarea name="description" class="form-input" rows="5" required></textarea>
            </div>

            <div class="form-group">
                <label>Requirements *</label>
                <textarea name="requirements" class="form-input" rows="3" required></textarea>
            </div>
            
            <button type="submit" class="btn btn-primary">Create Job Posting</button>
        </form>
    </div>
</div>