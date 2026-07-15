<div class="container admin-wrapper-view">
    <div class="card">
        <h2>System Audit Logs</h2>
    </div>

    <div class="card admin-data-card" style="margin-top: 2rem;">
        <div style="margin-bottom: 1rem;">
            <strong>Filter by Action:</strong>
            <a href="?action=all" class="btn <?= $currentAction === 'all' ? 'btn-primary' : 'btn-secondary' ?>">All</a>
            <a href="?action=LOGIN" class="btn <?= $currentAction === 'LOGIN' ? 'btn-primary' : 'btn-secondary' ?>">Login</a>
            <a href="?action=TENANT_CREATED" class="btn <?= $currentAction === 'TENANT_CREATED' ? 'btn-primary' : 'btn-secondary' ?>">Creation</a>
        </div>

        <table class="admin-table-view">
    <thead>
        <tr>
            <th>Timestamp</th>
            <th>User</th>
            <th>Action</th>
            <th>Severity</th> <th>Details</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($logs as $log): ?>
        <tr>
            <td><?= htmlspecialchars($log['created_at']) ?></td>
            <td><?= htmlspecialchars($log['user_label']) ?></td>
            <td><span class="status-badge-flag"><?= htmlspecialchars($log['action_type']) ?></span></td>
            <td>
                <span class="badge-<?= strtolower($log['severity'] ?? 'info') ?>">
                    <?= htmlspecialchars($log['severity'] ?? 'INFO') ?>
                </span>
            </td>
            <td style="font-size: 0.85rem; color: #666;">
                <?= htmlspecialchars($log['details']) ?>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
    </div>
</div>