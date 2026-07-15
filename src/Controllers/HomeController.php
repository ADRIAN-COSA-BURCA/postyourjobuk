<?php
namespace App\Controllers;

use App\Core\BaseController;
use App\Models\Job;

class HomeController extends BaseController {
    public function index() {
        $jobModel = new Job();
        
        // 1. Capture search query
        $search = trim($_GET['search'] ?? '');

        // 2. Fetch jobs: If searching, use model search; otherwise get latest
        $jobs = $search ? $jobModel->search($search) : $jobModel->getActiveJobs(50);
        
        // 3. Prepare session messages for the view
        $successMessage = $_SESSION['success_message'] ?? null;
        $errorMessage = $_SESSION['error_message'] ?? null;

        // 4. Clear messages after reading (to prevent them from appearing on refresh)
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        // 5. UPDATE: Point to 'home/index' to match your actual file structure
        $this->render('home/index', [
            'jobs'           => $jobs,
            'searchQuery'    => htmlspecialchars($search, ENT_QUOTES, 'UTF-8'),
            'successMessage' => $successMessage,
            'errorMessage'   => $errorMessage
        ]);
    }
	
	/**
     * Renders the About Us informational layout
     */
    public function about() {
        $this->render('home/about', [
            'title' => 'About Our Platform'
        ]);
    }

    /**
     * Renders the Interactive Contact Form area
     */
    public function contact() {
        $this->render('home/contact', [
            'title' => 'Get In Touch'
        ]);
    }

    /**
     * Renders the Company Vision roadmap blueprint
     */
    public function vision() {
        $this->render('home/vision', [
            'title' => 'Our Company Vision'
        ]);
    }

    /**
     * Renders the Terms of Service document
     */
    public function terms() {
        $this->render('home/terms', [
            'title' => 'Terms of Service & Data Policies'
        ]);
    }
}