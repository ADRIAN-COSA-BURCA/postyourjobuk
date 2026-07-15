<?php
namespace App\Core; 

abstract class BaseModel {
    
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    
    public function __construct() {
        // Pointing cleanly to the application's unified Core Database instance
        $this->db = \App\Core\Database::getInstance();
    }
    
    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = ? LIMIT 1";
        return $this->db->fetchOne($sql, [$id]);
    }
    
    public function all($where = [], $orderBy = null, $limit = null) {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];
        
        if (!empty($where)) {
            $conditions = [];
            foreach ($where as $column => $value) {
                $conditions[] = "$column = ?";
                $params[] = $value;
            }
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit) $sql .= " LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function create(array $data) {
        $this->beforeCreate($data);
        
        $columns = array_keys($data);
        $placeholders = array_fill(0, count($columns), '?');
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        // FIX: Use prepare and execute
        $stmt = $this->db->prepare($sql);
        $stmt->execute(array_values($data));
        
        $insertId = $this->db->lastInsertId();
        $this->afterCreate($insertId);
        
        return $insertId;
    }
    
    public function update($id, array $data) {
        $this->beforeUpdate($id, $data);
        
        $columns = [];
        $params = [];
        foreach ($data as $column => $value) {
            $columns[] = "$column = ?";
            $params[] = $value;
        }
        $params[] = $id;
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $columns) . " WHERE {$this->primaryKey} = ?";
        
        // FIX: Use prepare and execute
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        $this->afterUpdate($id);
        return true;
    }
    
    public function delete($id) {
        $this->beforeDelete($id);
        
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?";
        $this->db->query($sql, [$id]);
        
        $this->afterDelete($id);
        
        return true;
    }
    
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
    
    // ============================================
    // EXTENSION POINTS (Strict signature alignment)
    // ============================================
    
    protected function beforeCreate(array &$data) {}
    protected function afterCreate($id) {}
    protected function beforeUpdate($id, array &$data) {}
    protected function afterUpdate($id) {}
    protected function beforeDelete($id) {}
    protected function afterDelete($id) {}
}