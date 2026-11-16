<?php
namespace App\Core;

class Helpers {
    public static function e(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $url): void {
        header("Location: $url");
        exit();
    }

    /**
     * Verify CSRF token across explicit argument, X-CSRF-Token header, $_POST, or JSON body
     */
    public static function csrf_verify(?string $token = null): bool {
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }
        $candidate = $token
            ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)
            ?? ($_POST['csrf_token'] ?? null);

        if ($candidate === null || $candidate === '') {
            $raw = file_get_contents('php://input');
            if ($raw) {
                $decoded = json_decode($raw, true);
                if (is_array($decoded) && !empty($decoded['csrf_token'])) {
                    $candidate = (string)$decoded['csrf_token'];
                }
            }
        }

        if (!is_string($candidate) || $candidate === '') {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $candidate);
    }

    public static function csrf_token(): string {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function time_ago(?string $timestamp): string {
        if (!$timestamp) return 'just now';
        $timeAgo = strtotime($timestamp);
        if ($timeAgo === false) return 'just now';
        $diff = time() - $timeAgo;
        if ($diff < 60) return 'just now';
        if ($diff < 3600) return floor($diff / 60) . ' mins ago';
        if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
        return floor($diff / 86400) . ' days ago';
    }

    public static function jsonResponse(array $payload, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
