<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\NewsArticle;

class NewsController extends BaseController {

    private const CRON_SECRET = 'rss_fetch_secret_2026';

    public function fetchFeeds(): void {
        $expectedSecret = app_env('CRON_SECRET', self::CRON_SECRET);
        $token = (string)($_GET['token'] ?? '');
        if ($token === '' || !hash_equals($expectedSecret, $token)) {
            $this->json(['success' => false, 'error' => 'Forbidden: Invalid security token.'], 403);
        }

        $newsModel = new NewsArticle();
        $totalInserted = $newsModel->fetchAndStoreRssFeed();

        $this->json([
            'success'  => true,
            'inserted' => $totalInserted,
            'message'  => "RSS Fetch complete. Inserted {$totalInserted} new articles."
        ]);
    }
}
