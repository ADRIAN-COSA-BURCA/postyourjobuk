<?php
namespace App\Core;

class AuditLogger {
    /**
     * @param string $action The action performed
     * @param string $userLabel The name of the user/tenant
     * @param string $severity The log level (INFO, WARNING, CRITICAL)
     * @param int|null $tenantId The ID of the tenant (0 for Admin)
     * @param string|null $resourceType
     * @param int|null $resourceId
     * @param array $details
     */
    public static function log($action, $userLabel, $severity = 'INFO', $tenantId = null, $resourceType = null, $resourceId = null, $details = []) {
        $db = Database::getInstance();
        
        // Explicitly include severity in the column list and the values list
        $sql = "INSERT INTO system_logs (tenant_id, user_label, action_type, severity, resource_type, resource_id, ip_address, details) 
                VALUES (:tenant_id, :user_label, :action_type, :severity, :resource_type, :resource_id, :ip, :details)";
        
        $db->prepare($sql)->execute([
            'tenant_id'     => $tenantId,
            'user_label'    => $userLabel,
            'action_type'   => $action,
            'severity'      => $severity, // Ensure this is passed
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'ip'            => $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            'details'       => json_encode($details)
        ]);
    }
}