<?php
namespace App\Models;

use App\Core\BaseModel;

class Recruiter extends BaseModel {
    protected $table = 'recruiters';
    protected $primaryKey = 'id';

    /**
     * Find a recruiter by their email address
     * Used during the login process to verify identity
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$email]);
    }

    /**
     * Update recruiter's last login timestamp
     * Good practice for security auditing
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$id]);
    }
}