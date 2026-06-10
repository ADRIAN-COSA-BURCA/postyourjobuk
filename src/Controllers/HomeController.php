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
        // Ensure your Job model has a public search() method
        $jobs = $search ? $jobModel->search($search) : $jobModel->getActiveJobs(50);
        
        // 3. Prepare session messages for the view
        $successMessage = $_SESSION['success_message'] ?? null;
        $errorMessage = $_SESSION['error_message'] ?? null;

        // 4. Clear messages after reading (to prevent them from appearing on refresh)
        unset($_SESSION['success_message'], $_SESSION['error_message']);
        
        // 5. Pass data to the view
        $this->render('home/index', [
            'jobs'           => $jobs,
            'searchQuery'    => $search,
            'successMessage' => $successMessage,
            'errorMessage'   => $errorMessage
        ]);
    }
}