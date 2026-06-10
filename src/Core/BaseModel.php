<?php
namespace App\Core; 

abstract class BaseModel {
    
    // Database instance wrapper matching App\Core\Database
    protected $db;
    
    // Table name (must be set by child class)
    protected $table;
    
    // Primary key column name
    protected $primaryKey = 'id';
    
    /**
     * Constructor
     */
    public function __construct() {
        // Pointing cleanly to the application's unified Core Database instance
        $this->db = \App\Core\Database::getInstance();
    }
    
    /**
     * Find record by primary key
     * * @param int $id Primary key value
     * @return array|false Record or false if not found
     */
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    /**
     * Find all records with optional conditions
     * * @param array $where WHERE conditions ['column' => 'value']
     * @param string $orderBy ORDER BY clause
     * @param int $limit LIMIT clause
     * @return array Array of records
     */
    public function all($where = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        // Build WHERE clause
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        // Add ORDER BY
        if ($orderBy) {
            $sql .= " ORDER BY $orderBy";
        }
        
        // Add LIMIT
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        return $this->db->fetchAll($sql, $params);
    }
    
    /**
     * Create new record
     * * @param array $data Associative array of column => value
     * @return int|false Inserted ID or false
     */
    public function create($data) {
        // Pass data by reference securely
        $this->beforeCreate($data);
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->db->query($sql, array_values($data));
        $insertId = $this->db->lastInsertId();
        
        // Extension point (can be overridden by child)
        $this->afterCreate($insertId);
        
        return $insertId;
    }
    
    /**
     * Update record by primary key
     * * @param int $id Primary key value
     * @param array $data Associative array of column => value
     * @return bool Success
     */
    public function update($id, $data) {
        // FIX: $data must be passed by reference here to match the declaration signature below
        $this->beforeUpdate($id, $data);
        
        $columns = [];
        $params = [];
        
        foreach ($data as $column => $value) {
            $columns[] = "$column = ?";
            $params[] = $value;
        }
        
        $params[] = $id; // Add ID for WHERE clause
        
        $sql = "UPDATE {$this->table} 
                SET " . implode(', ', $columns) . " 
                WHERE {$this->primaryKey} = ?";
        
        $this->db->query($sql, $params);
        
        // Extension point (can be overridden by child)
        $this->afterUpdate($id);
        
        return true;
    }
    
    /**
     * Delete record by primary key
     * * @param int $id Primary key value
     * @return bool Success
     */
    public function delete($id) {
        // Extension point (can be overridden by child)
        $this->beforeDelete($id);
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
        
        // Extension point (can be overridden by child)
        $this->afterDelete($id);
        
        return true;
    }
    
    /**
     * Count records with optional conditions
     * * @param array $where WHERE conditions
     * @return int Count
     */
    public function count($where = []) {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        return (int) $this->db->fetchColumn($sql, $params);
    }
    
    /**
     * Check if record exists
     * * @param int $id Primary key value
     * @return bool
     */
    public function exists($id) {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE {$this->primaryKey} = ?";
        return (bool) $this->db->fetchColumn($sql, [$id]);
    }
    
    // ============================================
    // EXTENSION POINTS (Strict signature alignment)
    // ============================================
    
    /**
     * Called before create() - Override to add validation
     * * @param array &$data Data to be inserted (by reference, can modify)
     */
    protected function beforeCreate(&$data) {
        // Empty by default
    }
    
    /**
     * Called after create() - Override to trigger events
     * * @param int $id Newly inserted ID
     */
    protected function afterCreate($id) {
        // Empty by default
    }
    
    /**
     * Called before update() - Override to add validation
     * * @param int $id Record ID
     * @param array &$data Data to be updated (by reference, can modify)
     */
    protected function beforeUpdate($id, &$data) {
        // Empty by default
    }
    
    /**
     * Called after update() - Override to trigger events
     * * @param int $id Updated record ID
     */
    protected function afterUpdate($id) {
        // Empty by default
    }
    
    /**
     * Called before delete() - Override to add checks
     * * @param int $id Record ID to be deleted
     */
    protected function beforeDelete($id) {
        // Empty by default
    }
    
    /**
     * Called after delete() - Override to clean up
     * * @param int $id Deleted record ID
     */
    protected function afterDelete($id) {
        // Empty by default
    }
}
?>