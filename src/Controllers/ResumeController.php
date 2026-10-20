<?php
namespace App\Controllers;

use App\Core\BaseController;

class ResumeController extends BaseController {

    public function index() {
        $this->render('resume/index', [
            'pageTitle' => 'Free ATS Resume Builder — PostYourJobUK'
        ]);
    }

    public function editor() {
        $allowedTemplates = ['professional', 'modern', 'executive', 'technical'];
        $template = strtolower(trim($_GET['template'] ?? 'professional'));

        if (!in_array($template, $allowedTemplates, true)) {
            $template = 'professional';
        }

        $this->render('resume/editor', [
            'pageTitle' => 'Resume Builder Studio — Live Preview',
            'template'  => $template
        ]);
    }
}
