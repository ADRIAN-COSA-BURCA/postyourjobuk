<?php
namespace App\Core;

class SecurityLogger {
    public static function logAlert($eventType, $tenantId, $description, $severity = 'CRITICAL') {
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
