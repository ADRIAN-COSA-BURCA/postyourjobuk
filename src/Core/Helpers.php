<?php
namespace App\Core;

class Helpers {
    public static function e(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    public static function redirect(string $url): void {
        header('Location: ' . $url);
        exit;
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