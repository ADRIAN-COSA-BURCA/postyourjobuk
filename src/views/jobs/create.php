<div class="card">
    <div class="flex-between" style="margin-bottom: 2rem;">
        <h2>➕ Create New Job Posting</h2>
        <a href="/dashboard" class="btn btn-secondary">← Back</a>
    </div>

    <form method="POST" action="/jobs/store">
        <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">

        <div class="form-group">
            <label class="form-label">Job Title *</label>
            <input type="text" name="title" class="form-control" required placeholder="e.g. Senior Software Engineer">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div class="form-group">
                <label class="form-label">Employment Type *</label>
                <select name="employment_type" class="form-control" required>
                    <option value="full-time">Full-time</option>
                    <option value="part-time">Part-time</option>
                    <option value="contract">Contract</option>
                    <option value="remote">Remote</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Salary Range *</label>
                <input type="text" name="salary" class="form-control" placeholder="e.g. £40,000 - £60,000" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Location *</label>
            <input type="text" name="location" class="form-control" placeholder="e.g. London, UK (or Remote)" required>
        </div>

        <div class="form-group">
            <label class="form-label">Job Description *</label>
            <textarea name="description" class="form-control" rows="6" required></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Requirements *</label>
            <textarea name="requirements" class="form-control" rows="6" required></textarea>
        </div>

        <button type="submit" class="btn btn-primary" style="margin-top: 1rem;">Post Job</button>
    </form>
</div>