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

    <?php
    $uniqueLocations = [];
    $uniqueIndustries = [];
    if (!empty($jobs)) {
        foreach ($jobs as $j) {
            if (!empty($j['location'])) {
                $loc = trim($j['location']);
                $uniqueLocations[strtolower($loc)] = $loc;
            }
            if (!empty($j['industry'])) {
                $ind = trim($j['industry']);
                $uniqueIndustries[strtolower($ind)] = $ind;
            }
        }
        asort($uniqueLocations);
        asort($uniqueIndustries);
    }
    ?>

    <style>
    .opportunities-filter-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        background: #1f2937;
        border: 1px solid #374151;
        padding: 0.85rem 1.25rem;
        border-radius: 8px;
    }
    .opportunities-filter-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        align-items: center;
    }
    .opp-filter-select {
        padding: 0.5rem 0.75rem;
        background: #111827;
        border: 1px solid #374151;
        border-radius: 6px;
        color: #fff;
        font-size: 0.875rem;
        outline: none;
        cursor: pointer;
        color-scheme: dark;
    }
    .opp-filter-select option {
        background-color: #111827 !important;
        color: #ffffff !important;
    }
    .opp-filter-select option:checked,
    .opp-filter-select option:hover {
        background-color: #4f46e5 !important;
        color: #ffffff !important;
    }
    .opp-filter-select:focus {
        border-color: #6366f1;
    }
    .job-card-link {
        text-decoration: none;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        height: 100%;
        transition: transform 0.2s, border-color 0.2s, box-shadow 0.2s;
        box-sizing: border-box;
    }
    .job-card-link:hover {
        transform: translateY(-3px);
        border-color: rgba(139, 92, 246, 0.5);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }
    </style>

    <!-- Main Section: Job Listings (Full Width) -->
    <div style="margin-bottom: 3rem;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 0.75rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.4rem;">Latest Opportunities</h3>
                <?php if (!empty($searchQuery)): ?>
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.85rem; color: #94a3b8;">
                        Matching search: "<strong style="color: #38bdf8;"><?= e($searchQuery) ?></strong>" 
                        <a href="/" style="color: #ef4444; text-decoration: none; margin-left: 0.5rem;">✕ Clear Search</a>
                    </p>
                <?php endif; ?>
            </div>
            <span id="opportunitiesCounterBadge" style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">
                Showing <?= count($jobs) ?> opportunities
            </span>
        </div>

        <!-- OPPORTUNITIES FILTER & SORT TOOLBAR -->
        <div class="opportunities-filter-toolbar">
            <div class="opportunities-filter-group" style="flex: 1 1 auto;">
                <!-- Job / Work Type Filter -->
                <select id="filterWorkType" class="opp-filter-select">
                    <option value="all">💼 All Work Types</option>
                    <option value="full-time">Full time</option>
                    <option value="part-time">Part time</option>
                    <option value="remote">Remote</option>
                </select>

                <!-- Dynamic Location Filter (populated strictly from posted jobs) -->
                <select id="filterLocation" class="opp-filter-select">
                    <option value="all">📍 All Locations (<?= count($uniqueLocations) ?>)</option>
                    <?php foreach ($uniqueLocations as $key => $locLabel): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($locLabel) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Dynamic Industry Filter (populated strictly from posted jobs) -->
                <select id="filterIndustry" class="opp-filter-select">
                    <option value="all">🏷️ All Industries (<?= count($uniqueIndustries) ?>)</option>
                    <?php foreach ($uniqueIndustries as $key => $indLabel): ?>
                        <option value="<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($indLabel) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Sorting Dropdown -->
                <select id="filterSort" class="opp-filter-select">
                    <option value="date_desc">🕒 Newest First</option>
                    <option value="date_asc">🕒 Oldest First</option>
                    <option value="title_asc">🔤 Title (A - Z)</option>
                    <option value="company_asc">🏢 Company (A - Z)</option>
                </select>
            </div>

            <div>
                <button type="button" id="btnResetFilters" class="btn btn-sm btn-ghost" style="font-size: 0.8rem; padding: 0.35rem 0.75rem; color: #94a3b8; border: 1px solid #374151; display: none;">
                    ✕ Reset Filters
                </button>
            </div>
        </div>

        <?php if (empty($jobs)): ?>
            <div class="card" style="padding: 3rem; text-align: center; color: var(--text-muted);">
                <p style="font-size: 1.1rem; margin-bottom: 1rem;">No job opportunities available matching your search.</p>
                <a href="/" class="btn btn-primary btn-sm">Browse All Jobs</a>
            </div>
        <?php else: ?>
            <div id="jobsGridContainer" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1.5rem;">
                <?php foreach ($jobs as $job): ?>
                    <?php
                        $rawType = strtolower($job['employment_type'] ?? 'full-time');
                        $rawLoc = strtolower($job['location'] ?? 'remote');
                        $rawInd = strtolower($job['industry'] ?? 'general');
                        $rawTitle = strtolower($job['title'] ?? '');
                        $rawCompany = strtolower($job['company_name'] ?? '');
                        $createdTimestamp = strtotime($job['created_at'] ?? 'now');
                    ?>
                    <div class="job-card-wrapper"
                         data-type="<?= htmlspecialchars($rawType) ?>"
                         data-location="<?= htmlspecialchars($rawLoc) ?>"
                         data-industry="<?= htmlspecialchars($rawInd) ?>"
                         data-title="<?= htmlspecialchars($rawTitle) ?>"
                         data-company="<?= htmlspecialchars($rawCompany) ?>"
                         data-date="<?= $createdTimestamp ?>">
                        <a href="/job?id=<?= (int)$job['job_id'] ?>" class="card job-card-link">
                            <div>
                                <!-- Header: FULL Company Name + Employment Badge -->
                                <div class="flex-between" style="margin-bottom: 1rem; align-items: center; gap: 0.75rem;">
                                    <div style="font-weight: 700; color: #f3f4f6; font-size: 1.05rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0; flex: 1;" title="<?= e($job['company_name'] ?? 'Company') ?>">
                                        <?= e($job['company_name'] ?? 'Hiring Company') ?>
                                    </div>
                                    <span class="badge" style="background: rgba(139, 92, 246, 0.15); color: #a78bfa; border: 1px solid rgba(139, 92, 246, 0.3); font-size: 0.75rem; white-space: nowrap; flex-shrink: 0;">
                                        <?= ucfirst(str_replace('-', ' ', e($job['employment_type'] ?? 'Full time'))) ?>
                                    </span>
                                </div>

                                <!-- Title -->
                                <h3 style="margin-bottom: 0.5rem; font-size: 1.15rem; color: #ffffff;"><?= e($job['title']) ?></h3>

                                <!-- Meta details (Location, Salary, Industry) -->
                                <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 1.25rem; display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                                    <span>📍 <?= e($job['location'] ?? 'Remote') ?></span>
                                    <span style="color: var(--border);">•</span>
                                    <span>💷 <?= e($job['salary'] ?? 'Competitive') ?></span>
                                    <?php if (!empty($job['industry'])): ?>
                                        <span style="color: var(--border);">•</span>
                                        <span style="color: #38bdf8;">🏷️ <?= e($job['industry']) ?></span>
                                    <?php endif; ?>
                                </p>
                            </div>

                            <!-- Card Footer: Time Ago (Left) + Unique Job ID (Right) -->
                            <div class="text-muted" style="font-size: 0.8rem; border-top: 1px solid var(--border); padding-top: 0.85rem; display: flex; justify-content: space-between; align-items: center;">
                                <span>🕒 Posted <?= time_ago($job['created_at']) ?></span>
                                <span style="font-family: monospace; font-size: 0.78rem; font-weight: 600; color: #94a3b8; background: rgba(255, 255, 255, 0.04); border: 1px solid var(--border); padding: 0.2rem 0.5rem; border-radius: 4px;">
                                    Job ID: <?= (int)$job['job_id'] ?>
                                </span>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>

            <div id="noOppResults" style="display: none; background: #1f2937; border: 1px solid #374151; border-radius: 8px; padding: 2.5rem; text-align: center; margin-top: 1rem;">
                <p style="font-size: 1.1rem; color: #94a3b8; margin-bottom: 1rem;">
                    🔍 No job opportunities match your selected filter criteria.
                </p>
                <button type="button" onclick="resetAllFilters()" class="btn btn-primary btn-sm">
                    Clear Filters
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('filterWorkType');
    const locSelect = document.getElementById('filterLocation');
    const indSelect = document.getElementById('filterIndustry');
    const sortSelect = document.getElementById('filterSort');
    const resetBtn = document.getElementById('btnResetFilters');
    const counterBadge = document.getElementById('opportunitiesCounterBadge');
    const container = document.getElementById('jobsGridContainer');
    const noResults = document.getElementById('noOppResults');

    if (!container) return;
    const allWrappers = Array.from(container.querySelectorAll('.job-card-wrapper'));
    if (allWrappers.length === 0) return;

    window.resetAllFilters = function () {
        if (typeSelect) typeSelect.value = 'all';
        if (locSelect) locSelect.value = 'all';
        if (indSelect) indSelect.value = 'all';
        if (sortSelect) sortSelect.value = 'date_desc';
        applyJobCardFilters();
    };

    if (resetBtn) {
        resetBtn.addEventListener('click', window.resetAllFilters);
    }

    function applyJobCardFilters() {
        const typeVal = typeSelect ? typeSelect.value : 'all';
        const locVal = locSelect ? locSelect.value : 'all';
        const indVal = indSelect ? indSelect.value : 'all';
        const sortVal = sortSelect ? sortSelect.value : 'date_desc';

        const isFiltered = (typeVal !== 'all' || locVal !== 'all' || indVal !== 'all' || sortVal !== 'date_desc');
        if (resetBtn) {
            resetBtn.style.display = isFiltered ? 'inline-block' : 'none';
        }

        // 1. Filter
        let visible = allWrappers.filter(wrapper => {
            const type = wrapper.getAttribute('data-type') || '';
            const loc = wrapper.getAttribute('data-location') || '';
            const ind = wrapper.getAttribute('data-industry') || '';

            // Type filter
            if (typeVal === 'full-time' && !type.includes('full')) return false;
            if (typeVal === 'part-time' && !type.includes('part')) return false;
            if (typeVal === 'remote' && !(type.includes('remote') || loc.includes('remote'))) return false;

            // Location filter
            if (locVal !== 'all' && loc !== locVal) return false;

            // Industry filter
            if (indVal !== 'all' && ind !== indVal) return false;

            return true;
        });

        // 2. Sort
        visible.sort((a, b) => {
            const dateA = parseInt(a.getAttribute('data-date') || '0', 10);
            const dateB = parseInt(b.getAttribute('data-date') || '0', 10);
            const titleA = a.getAttribute('data-title') || '';
            const titleB = b.getAttribute('data-title') || '';
            const compA = a.getAttribute('data-company') || '';
            const compB = b.getAttribute('data-company') || '';

            switch (sortVal) {
                case 'date_desc': return dateB - dateA;
                case 'date_asc': return dateA - dateB;
                case 'title_asc': return titleA.localeCompare(titleB);
                case 'company_asc': return compA.localeCompare(compB);
                default: return dateB - dateA;
            }
        });

        // 3. Hide all
        allWrappers.forEach(w => w.style.display = 'none');

        // 4. Re-append in sorted order and show
        visible.forEach(w => {
            w.style.display = '';
            container.appendChild(w);
        });

        // 5. Update counter and empty state
        if (counterBadge) {
            counterBadge.textContent = `Showing ${visible.length} of ${allWrappers.length} opportunities`;
        }
        if (noResults) {
            noResults.style.display = visible.length === 0 ? 'block' : 'none';
        }
    }

    if (typeSelect) typeSelect.addEventListener('change', applyJobCardFilters);
    if (locSelect) locSelect.addEventListener('change', applyJobCardFilters);
    if (indSelect) indSelect.addEventListener('change', applyJobCardFilters);
    if (sortSelect) sortSelect.addEventListener('change', applyJobCardFilters);
});
</script>