<?php
namespace App\Core;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        // We use constants that we will define in bootstrap.php
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // ONLY enforce SSL if we are NOT on local Docker development
        if (defined('DB_HOST') && DB_HOST !== 'db' && ($_ENV['APP_ENV'] ?? '') !== 'development') {
            $options[PDO::MYSQL_ATTR_SSL_CA] = ROOT_PATH . DIRECTORY_SEPARATOR . 'DigiCertGlobalRootG2.crt.pem';
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Database connection failed.");
        }
    }

    // 1. Update this method to return the Database instance
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance; 
    }

    // 2. Add a query helper to match your BaseModel's expectations
    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    // 3. Keep your fetch methods as you have them...
    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

// Ensure you also add this to allow BaseModel to use execute/prepare directly
public function prepare($sql) {
    return $this->connection->prepare($sql);
}

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}