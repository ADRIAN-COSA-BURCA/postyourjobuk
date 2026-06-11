<?php
namespace App\Core;

class Helpers {
    public static function e(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect($url) {
    // There must be NO echo or print here
    header("Location: $url");
    exit();
}
	
	
	public static function csrf_verify($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
	
	
	public static function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

    public static function time_ago(string $timestamp): string {
        $timeAgo = strtotime($timestamp);
        if ($timeAgo === false) return 'unknown';
        $difference = time() - $timeAgo;
        // ... (insert your original time_ago logic here)
        return 'just now'; // Placeholder
    }
    
    // You can move all other global functions from your old config.php here
}