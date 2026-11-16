<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Core\Helpers;
use App\Models\Job;
use App\Models\NewsArticle;

class HomeController extends BaseController {

    /**
     * GET /api/public/home
     * Returns active jobs (or search results), recent RSS news, unique filter values, and CSRF token
     */
    public function index(): void {
        $jobModel  = new Job();
        $newsModel = new NewsArticle();

        $search = trim((string)($_GET['search'] ?? ''));
        $jobs   = $search !== '' ? $jobModel->search($search) : $jobModel->getActiveJobs(50);

        $recentNews = $newsModel->getRecentNews(20);
        if (empty($recentNews) && method_exists($newsModel, 'fetchAndStoreRssFeed')) {
            @$newsModel->fetchAndStoreRssFeed();
            $recentNews = $newsModel->getRecentNews(20);
        }

        $uniqueLocations  = [];
        $uniqueIndustries = [];

        foreach ($jobs as &$job) {
            $loc = !empty($job['location']) ? trim((string)$job['location']) : 'Remote';
            $ind = !empty($job['industry']) ? trim((string)$job['industry']) : 'General';
            $job['time_ago'] = Helpers::time_ago($job['created_at'] ?? '');

            if (!in_array($loc, $uniqueLocations, true)) {
                $uniqueLocations[] = $loc;
            }
            if (!in_array($ind, $uniqueIndustries, true)) {
                $uniqueIndustries[] = $ind;
            }
        }
        unset($job);

        sort($uniqueLocations);
        sort($uniqueIndustries);

        foreach ($recentNews as &$article) {
            $article['time_ago'] = Helpers::time_ago($article['published_at'] ?? '');
        }
        unset($article);

        $this->json([
            'success'      => true,
            'search_query' => $search,
            'jobs'         => $jobs,
            'news'         => $recentNews,
            'filters'      => [
                'locations'  => $uniqueLocations,
                'industries' => $uniqueIndustries
            ],
            'csrf_token'   => Helpers::csrf_token()
        ]);
    }

    /**
     * POST|GET /api/news/refresh
     */
    public function refreshNews(): void {
        $newsModel = new NewsArticle();
        try {
            $count = $newsModel->fetchAndStoreRssFeed();
            $recentNews = $newsModel->getRecentNews(20);
            foreach ($recentNews as &$article) {
                $article['time_ago'] = Helpers::time_ago($article['published_at'] ?? '');
            }
            unset($article);

            $this->json([
                'success'  => true,
                'inserted' => $count,
                'message'  => "Successfully fetched {$count} new articles!",
                'news'     => $recentNews
            ]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'error'   => 'RSS Fetch Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/public/contact
     */
    public function contactSubmit(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['success' => false, 'error' => 'Method not allowed.'], 405);
        }

        $body    = $this->getBody();
        $name    = trim((string)($body['name'] ?? ''));
        $email   = trim((string)($body['email'] ?? ''));
        $subject = trim((string)($body['subject'] ?? 'Platform Inquiry'));
        $message = trim((string)($body['message'] ?? ''));

        if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $message === '') {
            $this->json([
                'success' => false,
                'error'   => 'Please provide your name, a valid email address, and a message.'
            ], 422);
        }

        \App\Core\AuditLogger::log('PUBLIC_CONTACT_INQUIRY', $name, 'INFO', 0, 'contact', 0, [
            'email'   => $email,
            'subject' => $subject,
            'preview' => mb_substr($message, 0, 160)
        ]);

        $this->json([
            'success' => true,
            'message' => 'Thank you for reaching out! Your message has been received by our support team.'
        ]);
    }
}
