<?php
namespace App\Models;

use App\Core\BaseModel;
use PDO;

class Recruiter extends BaseModel {
    // Realigned to match your production Azure SQL structural design
    protected $table = 'tenants';
    protected $primaryKey = 'tenant_id';

    /**
     * Find a recruiter/tenant by their email address
     * Used during the login process to verify identity
     * @param string $email
     * @return array|null Returns the matching row array, or null if not found
     */
    public function findByEmail($email) {
        $sql = "SELECT * FROM {$this->table} WHERE email = ? LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        
        return $row ? $row : null;
    }

    /**
     * Find a recruiter/tenant by their Google ID
     * Used during the OAuth login process
     * @param string $googleId
     * @return array|null Returns the matching row array, or null if not found
     */
    public function findByGoogleId($googleId) {
        $sql = "SELECT * FROM {$this->table} WHERE google_id = ? LIMIT 1";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$googleId]);
        $row = $stmt->fetch();
        
        return $row ? $row : null;
    }

    /**
     * Link a Google ID to an existing tenant account
     * @param int $tenantId
     * @param string $googleId
     * @return bool
     */
    public function linkGoogleAccount($tenantId, $googleId) {
        $sql = "UPDATE {$this->table} SET google_id = ? WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$googleId, $tenantId]);
    }

    /**
     * Update recruiter's last login timestamp
     * Good practice for security auditing and tracking session timelines
     * @param int $id The tenant_id of the logged-in corporate user
     * @return bool
     */
    public function updateLastLogin($id) {
        $sql = "UPDATE {$this->table} SET last_login = NOW() WHERE {$this->primaryKey} = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
    
    public function create(array $data) {
        // Generate placeholder string: :company_name, :email, ...
        $keys = array_keys($data);
        $fields = implode(', ', $keys);
        $placeholders = ':' . implode(', :', $keys);

        $sql = "INSERT INTO {$this->table} ($fields) VALUES ($placeholders)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }

    public function getLastInsertId() {
        return $this->db->lastInsertId();
    }
}