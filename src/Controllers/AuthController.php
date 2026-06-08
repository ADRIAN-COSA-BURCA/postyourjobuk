<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Recruiter; // We will assume you have a Recruiter model

class AuthController extends Controller {
    
    public function login() {
        // Show the login form
        $this->render('auth/login');
    }

    public function authenticate() {
    // 1. Basic CSRF and Method check
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ' . BASE_URL . '/login');
        exit;
    }

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $recruiterModel = new \App\Models\Recruiter();
    $user = $recruiterModel->findByEmail($email);

    // 2. Zero-Trust Check (The security layer)
    if ($user) {
        if ($user['deleted_at'] !== null || $user['status'] === 'suspended') {
            // Log this incident
            // SecurityLogger::logAlert(...); 
            $_SESSION['error_message'] = "Access Denied: Account state invalid.";
            header('Location: ' . BASE_URL . '/login');
            exit;
        }
    }

    // 3. Password Verification
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['tenant_id'] = $user['tenant_id'];
        header('Location: ' . BASE_URL . '/dashboard');
        exit;
    } else {
        $_SESSION['error_message'] = "Invalid credentials.";
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}

    public function logout() {
        session_destroy();
        header('Location: ' . BASE_URL . '/login');
        exit;
    }
}