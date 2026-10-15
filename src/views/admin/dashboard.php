<style>
    /* Mobile-first header: stacks heading and button group vertically */
    .admin-overview-header {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
        width: 100%;
    }

    /* Mobile button group: wraps buttons so they fit on narrow screens */
    .admin-overview-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        align-items: center;
        width: 100%;
    }

    /* Allow buttons to expand evenly on mobile for better touch targets */
    .admin-overview-actions .btn {
        flex: 1 1 calc(50% - 10px);
        min-width: 140px;
        text-align: center;
        justify-content: center;
    }

    /* Desktop overrides (Screens 768px and wider) */
    @media (min-width: 768px) {
        .admin-overview-header {
            flex-direction: row;
            justify-content: space-between;
            align-items: center;
        }

        .admin-overview-actions {
            width: auto;
            flex-wrap: nowrap;
        }

        .admin-overview-actions .btn {
            flex: initial;
        }
    }
</style>

<div class="container admin-wrapper-view">
    <div class="card" style="margin-bottom: 2rem;"> 
        <div class="admin-overview-header">
            <div>
                <h2 style="margin: 0;">System Overview</h2>
            </div>
            <div class="admin-overview-actions">
                <a href="/admin/logs" class="btn btn-primary">View System Logs</a>
                
                <!-- Analytics Button -->
                <a href="/admin/analytics" class="btn btn-primary">📊 View Analytics</a>
                
                <!-- NEW: Fairness & Bias Audit Button -->
                <a href="/admin/fairness" class="btn btn-primary">⚖️ Fairness Audit</a>
                
                <button onclick="document.getElementById('createTenantModal').style.display='flex'" class="btn btn-primary">
                    + Create New Recruiter
                </button>
            </div>
        </div>
    </div>
</div>

    <div id="createTenantModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center;">
        <div class="card" style="width: 500px; padding: 2rem; max-height: 90vh; overflow-y: auto;">
            <h3>Add New Recruiter</h3>
            <form action="/admin/tenant/create" method="POST">
                <input type="hidden" name="csrf_token" value="<?= \App\Core\Helpers::csrf_token() ?>">
                
                <div style="margin-bottom: 1rem;"><label>Company Name</label><input type="text" name="company_name" required style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Contact Person</label><input type="text" name="contact_person" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Admin Email</label><input type="email" name="email" required style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Phone Number</label><input type="text" name="phone_number" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Website URL</label><input type="url" name="website_url" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Industry</label><input type="text" name="industry" style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Company Address</label><textarea name="company_address" style="width: 100%; padding: 0.5rem;"></textarea></div>
                <div style="margin-bottom: 1rem;"><label>Initial Password</label><input type="password" name="password" required style="width: 100%; padding: 0.5rem;"></div>
                <div style="margin-bottom: 1rem;"><label>Recovery Code (Unique Identifier)</label><input type="text" name="recovery_code" required style="width: 100%; padding: 0.5rem;" placeholder="e.g., PJH-12345"></div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('createTenantModal').style.display='none'" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-counters-grid">
        <div class="admin-metric-card">
            <h3>Total Tenants</h3>
            <p class="count-total"><?= htmlspecialchars($stats['total_tenants'] ?? 0); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Active Tenants</h3>
            <p class="count-active"><?= htmlspecialchars($stats['active_tenants'] ?? 0); ?></p>
        </div>
        <div class="admin-metric-card">
            <h3>Total Job Postings</h3>
            <p class="count-listings"><?= htmlspecialchars($stats['total_jobs'] ?? 0); ?></p>
        </div>
    </div>

    <style>
/* ---------- Desktop ---------- */
.admin-filter-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.admin-filter-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}
.admin-sort-mobile { display: none; }

/* ---------- Mobile ---------- */
@media (max-width: 768px) {
    /* let the card and wrapper shrink */
    .admin-data-card,
    .admin-table-wrap {
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow: visible !important;
        box-sizing: border-box;
    }

    /* filter bar: label on top, buttons wrap underneath */
    .admin-filter-bar {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 1rem;
    }
    .admin-filter-links { width: 100%; }
    .admin-filter-links .btn {
        flex: 1 1 auto;
        text-align: center;
        min-width: 0 !important;
    }

    /* sort buttons (replace hidden table header) */
    .admin-sort-mobile {
        display: flex;
        gap: 0.5rem;
        padding: 0 1rem 1rem;
    }
    .admin-sort-mobile .btn {
        flex: 1;
        text-align: center;
        min-width: 0 !important;
    }

    /* hide desktop header */
    .admin-table-view thead { display: none; }

    /* table parts become blocks, kill desktop min-widths/nowrap */
    .admin-table-view,
    .admin-table-view tbody,
    .admin-table-view tr,
    .admin-table-view td,
    .admin-table-view .td-content,
    .admin-table-view .btn {
        display: block;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        white-space: normal !important;
    }

    /* each row = card */
    .admin-table-view tr {
        margin: 0 0 1rem;
        padding: 1rem;
        border: 1px solid #374151;
        border-radius: 0.5rem;
        background: #1e293b;
    }

    .admin-table-view td {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 0.75rem 0 !important;
        text-align: left !important;
        border-bottom: 1px solid #334155;
        overflow-wrap: anywhere;
    }
    .admin-table-view td:last-child {
        border-bottom: none;
        padding-bottom: 0 !important;
    }

    .admin-table-view td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #94a3b8;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
    }

    /* status badge keeps natural size */
    .admin-table-view .status-badge-flag {
        display: inline-block;
        width: auto !important;
    }

    /* full-width tap target */
    .admin-table-view .btn {
        text-align: center;
        padding: 0.7rem;
    }

    /* empty-state row has no label */
    .admin-table-view td[colspan]::before { content: none; }
}
</style>

<?php
    $nextOrder   = ($currentSort === 'company_name' && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
    $statusOrder = ($currentSort === 'status' && $currentOrder === 'ASC') ? 'DESC' : 'ASC';
?>

<div class="card admin-data-card">
    <div class="card-header" style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border);">
        <h2>Manage Tenants</h2>
    </div>

    <div class="admin-filter-bar">
        <strong>Filter by Status:</strong>
        <div class="admin-filter-links">
            <a href="?sort=<?= $currentSort ?>&order=<?= $currentOrder ?>&filter=all" class="btn <?= $currentFilter === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="?sort=<?= $currentSort ?>&order=<?= $currentOrder ?>&filter=active" class="btn <?= $currentFilter === 'active' ? 'btn-primary' : 'btn-secondary' ?>">Active</a>
            <a href="?sort=<?= $currentSort ?>&order=<?= $currentOrder ?>&filter=suspended" class="btn <?= $currentFilter === 'suspended' ? 'btn-primary' : 'btn-secondary' ?>">Suspended</a>
        </div>
    </div>

    <!-- Mobile-only sort buttons -->
    <div class="admin-sort-mobile">
        <a href="?sort=company_name&order=<?= $nextOrder ?>&filter=<?= htmlspecialchars($currentFilter) ?>" class="btn btn-secondary">Sort by name</a>
        <a href="?sort=status&order=<?= $statusOrder ?>&filter=<?= htmlspecialchars($currentFilter) ?>" class="btn btn-secondary">Sort by status</a>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table-view">
            <thead>
                <tr>
                    <th>
                        <a href="?sort=company_name&order=<?= $nextOrder ?>&filter=<?= htmlspecialchars($currentFilter) ?>" style="text-decoration:none; color:inherit;">
                            Company Profile Name <?= $currentSort === 'company_name' ? ($currentOrder === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=status&order=<?= $statusOrder ?>&filter=<?= htmlspecialchars($currentFilter) ?>" style="text-decoration:none; color:inherit;">
                            Routing Status <?= $currentSort === 'status' ? ($currentOrder === 'ASC' ? '▲' : '▼') : '' ?>
                        </a>
                    </th>
                    <th style="text-align: right;">Workspace Operations</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($tenants)): ?>
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 2rem; color: var(--muted);">
                            No tenant nodes configured in workspace infrastructure.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tenants as $tenant):
                        $status = strtolower($tenant['status'] ?? 'active'); ?>
                    <tr>
                        <td data-label="Company" style="font-weight: 600;">
                            <div class="td-content"><?= htmlspecialchars($tenant['company_name']); ?></div>
                        </td>
                        <td data-label="Status">
                            <div class="td-content">
                                <span class="status-badge-flag <?= $status; ?>">
                                    <?= htmlspecialchars(ucfirst($status)); ?>
                                </span>
                            </div>
                        </td>
                        <td data-label="Operations" style="text-align: right;">
                            <div class="td-content">
                                <a href="/admin/view-tenant?id=<?= urlencode($tenant['tenant_id']); ?>" class="btn btn-primary">
                                    Control Console
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
</div>