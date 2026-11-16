<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Database;

class AuditController extends BaseController {

    /**
     * GET /api/admin/logs?action={action}&sort={sort}&order={order}
     */
    public function index(): void {
        $this->requireAdminAuth();

        $allowedSorts = ['created_at', 'action_type', 'severity', 'user_label', 'id'];
        $rawSort      = (string)($_GET['sort'] ?? 'created_at');
        $sort         = in_array($rawSort, $allowedSorts, true) ? $rawSort : 'created_at';
        $order        = (strtoupper((string)($_GET['order'] ?? 'DESC')) === 'ASC') ? 'ASC' : 'DESC';
        $action       = trim((string)($_GET['action'] ?? 'all'));

        $db     = Database::getInstance();
        $params = [];
        $where  = "1=1";

        if ($action !== 'all' && $action !== '') {
            $where .= " AND action_type LIKE :action";
            $params['action'] = $action . '%';
        }

        $sql  = "SELECT * FROM system_logs WHERE $where ORDER BY $sort $order LIMIT 100";
        $logs = $db->fetchAll($sql, $params);

        $this->json([
            'success'        => true,
            'logs'           => $logs,
            'current_sort'   => $sort,
            'current_order'  => $order,
            'current_action' => $action
        ]);
    }
}
