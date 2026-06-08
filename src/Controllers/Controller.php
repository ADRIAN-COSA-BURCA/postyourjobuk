<?php
namespace App\Core;

abstract class Controller {
    protected $db;

    public function __construct() {
        $this->db = \Database::getInstance();
    }

    protected function render($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../../views/' . $view . '.php';
    }
}