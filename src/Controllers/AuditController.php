<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Database;
use App\Middleware\AdminAuth;

class AuditController extends BaseController {
    
    public function index() {
        AdminAuth::check();

        // 1. Setup Sorting/Filtering (Reusing Admin logic for uniformity)
        $sort = $_GET['sort'] ?? 'created_at';
        $order = ($_GET['order'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';
        $action = $_GET['action'] ?? 'all';

        $db = Database::getInstance();
        $params = [];
        $where = "1=1";

        if ($action !== 'all') {
    // Correcting the column name to action_type
    $where .= " AND action_type LIKE :action";
    $params['action'] = $action . '%'; 
}

        // 2. Fetch Logs
        $sql = "SELECT * FROM system_logs WHERE $where ORDER BY $sort $order LIMIT 100";
        $logs = $db->fetchAll($sql, $params);
		
		// DEBUG: Look at the raw data returned from your database
error_log("DEBUG: Log Data Structure: " . print_r($logs, true));

        // 3. Render View
        $this->render('admin/logs', [
            'logs' => $logs,
            'currentSort' => $sort,
            'currentOrder' => $order,
            'currentAction' => $action
        ]);
    }
}