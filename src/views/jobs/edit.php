<div class="page-shell page-shell-narrow max-w-4xl mx-auto px-4 py-8">

    <div class="page-back mb-6">
        <a href="/dashboard" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 transition">
            ← Back to Dashboard
        </a>
    </div>

    <div class="card bg-white p-8 rounded-xl shadow-md border border-gray-100">
        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-2">✏️ Edit Job Posting</h1>
        <p class="text-gray-500 mt-2 mb-6">
            Update the job details below. Changes will be reflected immediately across your workspace layout.
        </p>

        <!-- Dynamic Success/Error Alerts -->
        <?php if (!empty($_SESSION['error_message'])): ?>
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm flex items-center gap-2">
                ⚠️ <?php echo htmlspecialchars($_SESSION['error_message']); unset($_SESSION['error_message']); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/jobs/update" class="space-y-6">
            <!-- Security Tokens and Context Safe IDs -->
            <input type="hidden" name="csrf_token" value="<?php echo App\Core\Helpers::csrf_token(); ?>">
            <input type="hidden" name="job_id" value="<?php echo (int)($job['job_id'] ?? 0); ?>">

            <div class="form-group flex flex-col space-y-1">
                <label class="text-sm font-semibold text-gray-700" for="title">Job Title *</label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                    placeholder="e.g., Senior Software Engineer"
                    value="<?php echo htmlspecialchars($job['title'] ?? ''); ?>"
                    required
                    autofocus
                >
            </div>

            <div class="form-group flex flex-col space-y-1">
                <label class="text-sm font-semibold text-gray-700" for="description">Job Description *</label>
                <textarea
                    id="description"
                    name="description"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-sans"
                    rows="8"
                    required
                ><?php echo htmlspecialchars($job['description'] ?? ''); ?></textarea>
            </div>

            <div class="form-group flex flex-col space-y-1">
                <label class="text-sm font-semibold text-gray-700" for="requirements">Requirements & Qualifications *</label>
                <textarea
                    id="requirements"
                    name="requirements"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition font-sans"
                    rows="6"
                    required
                ><?php echo htmlspecialchars($job['requirements'] ?? ''); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col space-y-1">
                    <label class="text-sm font-semibold text-gray-700" for="location">Location</label>
                    <input
                        type="text"
                        id="location"
                        name="location"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        value="<?php echo htmlspecialchars($job['location'] ?? ''); ?>"
                    >
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-sm font-semibold text-gray-700" for="salary">Salary Range</label>
                    <input
                        type="text"
                        id="salary"
                        name="salary"
                        class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition"
                        value="<?php echo htmlspecialchars($job['salary'] ?? ''); ?>"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex flex-col space-y-1">
                    <label class="text-sm font-semibold text-gray-700" for="employment_type">Employment Type</label>
                    <select id="employment_type" name="employment_type" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="full-time" <?php echo ($job['employment_type'] ?? '') === 'full-time' ? 'selected' : ''; ?>>Full-Time</option>
                        <option value="part-time" <?php echo ($job['employment_type'] ?? '') === 'part-time' ? 'selected' : ''; ?>>Part-Time</option>
                        <option value="contract" <?php echo ($job['employment_type'] ?? '') === 'contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="internship" <?php echo ($job['employment_type'] ?? '') === 'internship' ? 'selected' : ''; ?>>Internship</option>
                    </select>
                </div>
                <div class="flex flex-col space-y-1">
                    <label class="text-sm font-semibold text-gray-700" for="status">Status</label>
                    <select id="status" name="status" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition bg-white">
                        <option value="active" <?php echo ($job['status'] ?? '') === 'active' ? 'selected' : ''; ?>>✅ Active</option>
                        <option value="inactive" <?php echo ($job['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>⏸️ Inactive</option>
                    </select>
                </div>
            </div>

            <div class="pt-4 flex items-center gap-4">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium px-6 py-2.5 rounded-lg transition shadow-sm">
                    💾 Save Changes
                </button>
                <a href="/dashboard" class="border border-gray-300 hover:bg-gray-50 text-gray-700 font-medium px-6 py-2.5 rounded-lg transition">
                    Cancel
                </a>
            </div>

        </form>
    </div>

</div>