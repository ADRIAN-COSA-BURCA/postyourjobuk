<div class="container admin-wrapper-view">
    <div class="card">
        <h2>System Audit Logs</h2>
    </div>
	
	<style>
/* ---------- Desktop ---------- */
.log-filter-bar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.75rem;
    margin-bottom: 1rem;
}
.log-filter-links {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
}

/* ---------- Mobile ---------- */
@media (max-width: 768px) {
    /* let the card and wrapper shrink */
    .log-card,
    .log-table-wrap {
        min-width: 0 !important;
        max-width: 100% !important;
        width: 100% !important;
        overflow: visible !important;
        box-sizing: border-box;
    }

    /* filter bar: label on top, buttons wrap underneath */
    .log-filter-bar {
        flex-direction: column;
        align-items: flex-start;
        padding: 0 1rem;
    }
    .log-filter-links { width: 100%; }
    .log-filter-links .btn {
        flex: 1 1 auto;
        text-align: center;
        min-width: 0 !important;
    }

    /* hide desktop header */
    .log-table thead { display: none; }

    /* table parts become blocks, kill desktop min-widths/nowrap */
    .log-table,
    .log-table tbody,
    .log-table tr,
    .log-table td,
    .log-table .td-content {
        display: block;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        box-sizing: border-box !important;
        white-space: normal !important;
    }

    /* each row = card */
    .log-table tr {
        margin: 0 0 1rem;
        padding: 1rem;
        border: 1px solid #374151;
        border-radius: 0.5rem;
        background: #1e293b;
    }

    .log-table td {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        padding: 0.75rem 0 !important;
        text-align: left !important;
        border-bottom: 1px solid #334155;
        overflow-wrap: anywhere;
        word-break: break-word;
    }
    .log-table td:last-child {
        border-bottom: none;
        padding-bottom: 0 !important;
    }

    .log-table td::before {
        content: attr(data-label);
        font-weight: 700;
        color: #94a3b8;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.35rem;
    }

    /* badges keep natural size */
    .log-table .status-badge-flag,
    .log-table [class^="badge-"] {
        display: inline-block;
        width: auto !important;
    }

    /* details text: readable on dark card */
    .log-table .log-details { color: #94a3b8 !important; }

    /* empty-state row has no label */
    .log-table td[colspan]::before { content: none; }
}
</style>

<div class="card admin-data-card log-card" style="margin-top: 2rem;">
    <div class="log-filter-bar">
        <strong>Filter by Action:</strong>
        <div class="log-filter-links">
            <a href="?action=all" class="btn <?= $currentAction === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="?action=LOGIN" class="btn <?= $currentAction === 'LOGIN' ? 'btn-primary' : 'btn-secondary' ?>">Login</a>
            <a href="?action=TENANT_CREATED" class="btn <?= $currentAction === 'TENANT_CREATED' ? 'btn-primary' : 'btn-secondary' ?>">Creation</a>
        </div>
    </div>

    <div class="log-table-wrap">
        <table class="admin-table-view log-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Severity</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 2rem; color: var(--muted);">
                            No log entries found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td data-label="Timestamp">
                            <div class="td-content"><?= htmlspecialchars($log['created_at']) ?></div>
                        </td>
                        <td data-label="User">
                            <div class="td-content"><?= htmlspecialchars($log['user_label']) ?></div>
                        </td>
                        <td data-label="Action">
                            <div class="td-content">
                                <span class="status-badge-flag"><?= htmlspecialchars($log['action_type']) ?></span>
                            </div>
                        </td>
                        <td data-label="Severity">
                            <div class="td-content">
                                <span class="badge-<?= strtolower($log['severity'] ?? 'info') ?>">
                                    <?= htmlspecialchars($log['severity'] ?? 'INFO') ?>
                                </span>
                            </div>
                        </td>
                        <td data-label="Details" class="log-details" style="font-size: 0.85rem; color: #666;">
                            <div class="td-content"><?= htmlspecialchars($log['details']) ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

    
	
</div>