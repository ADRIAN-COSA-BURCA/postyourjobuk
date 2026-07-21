<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;
use App\Models\NewsArticle;

class HomeController extends BaseController {
    
    public function index() {
        $jobModel = new Job();
        $newsModel = new NewsArticle();
        
        // 1. Capture search query
        $search = trim($_GET['search'] ?? '');

        // 2. Fetch jobs
        $jobs = $search ? $jobModel->search($search) : $jobModel->getActiveJobs(50);
        
        // 3. Fetch recent news
        $recentNews = $newsModel->getRecentNews(20);
        
        // AUTO-POPULATE: If news table is empty, trigger a silent fetch
        if (empty($recentNews) && method_exists($newsModel, 'fetchAndStoreRssFeed')) {
            @$newsModel->fetchAndStoreRssFeed();
            $recentNews = $newsModel->getRecentNews(20);
        }
        
        // 4. Prepare session messages
        $successMessage = $_SESSION['success_message'] ?? null;
        $errorMessage = $_SESSION['error_message'] ?? null;
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        // 5. Render view
        $this->render('home/index', [
            'jobs'           => $jobs,
            'recentNews'     => $recentNews,
            'searchQuery'    => htmlspecialchars($search, ENT_QUOTES, 'UTF-8'),
            'successMessage' => $successMessage,
            'errorMessage'   => $errorMessage
        ]);
    }
    
    public function about() {
        $this->render('home/about', ['title' => 'About Our Platform']);
    }

    public function contact() {
        $this->render('home/contact', ['title' => 'Get In Touch']);
    }

    public function vision() {
        $this->render('home/vision', ['title' => 'Our Company Vision']);
    }

    public function terms() {
        $this->render('home/terms', ['title' => 'Terms of Service & Data Policies']);
    }
	
	
	public function refreshNews() {
        $newsModel = new NewsArticle();
        try {
            $count = $newsModel->fetchAndStoreRssFeed();
            $_SESSION['success_message'] = "Successfully fetched {$count} new articles!";
        } catch (\Exception $e) {
            $_SESSION['error_message'] = "RSS Fetch Error: " . $e->getMessage();
        }
        header('Location: /');
        exit;
    }
}