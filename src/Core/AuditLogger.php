<?php
namespace App\Core;

class AuditLogger {
    /**
     * @param string $action The action performed
     * @param string $userLabel The human-readable name of the user/tenant (e.g., "Company Name" or "Admin: email")
     * @param int|null $tenantId The ID of the tenant (0 for Admin)
     * @param string|null $resourceType
     * @param int|null $resourceId
     * @param array $details
     */
    public static function log($action, $userLabel, $tenantId = null, $resourceType = null, $resourceId = null, $details = []) {
        $db = Database::getInstance();
        
        $sql = "INSERT INTO system_logs (tenant_id, user_label, action_type, resource_type, resource_id, ip_address, details) 
                VALUES (:tenant_id, :user_label, :action_type, :resource_type, :resource_id, :ip, :details)";
        
        $db->prepare($sql)->execute([
            'tenant_id'     => $tenantId,
            'user_label'    => $userLabel,
            'action_type'   => $action,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'details'       => json_encode($details)
        ]);
    }
}