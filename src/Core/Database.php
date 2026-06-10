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

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance->connection;
    }

    // Prevent cloning and unserialization
    private function __clone() {}
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}