<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Database;
use App\Middleware\AdminAuth;

class AuditController extends BaseController {
    
    public function index() {
        AdminAuth::check();

        // 1. Setup Sorting/Filtering with strict SQL column allowlist
        $allowedSorts = ['created_at', 'action_type', 'severity', 'user_label', 'id'];
        $rawSort = $_GET['sort'] ?? 'created_at';
        $sort = in_array($rawSort, $allowedSorts, true) ? $rawSort : 'created_at';
        $order = (strtoupper($_GET['order'] ?? 'DESC') === 'ASC') ? 'ASC' : 'DESC';
        $action = $_GET['action'] ?? 'all';

        $db = Database::getInstance();
        $params = [];
        $where = "1=1";

        if ($action !== 'all') {
            $where .= " AND action_type LIKE :action";
            $params['action'] = $action . '%'; 
        }

        // 2. Fetch Logs
        $sql = "SELECT * FROM system_logs WHERE $where ORDER BY $sort $order LIMIT 100";
        $logs = $db->fetchAll($sql, $params);

        // 3. Render View
        $this->render('admin/logs', [
            'logs' => $logs,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'currentAction' => $action
        ]);
    }
}
