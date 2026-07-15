<?php
namespace App\Core;

class SecurityLogger {
    public static function logAlert($eventType, $tenantId, $description, $severity = 'CRITICAL') {
        // This maps the old method calls to your new, unified system
        AuditLogger::log(
            $eventType, 
            'System Security', 
            $severity, 
            $tenantId, 
            'security_event', 
            0, 
            ['message' => $description]
        );
    }
}