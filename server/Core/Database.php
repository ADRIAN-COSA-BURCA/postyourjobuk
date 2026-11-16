<?php
namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;
use Exception;

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        // Enforce SSL whenever connecting to Azure Database for MySQL Flexible Server
        if (defined('DB_HOST') && str_contains(DB_HOST, 'database.azure.com')) {
            $systemCa = '/etc/ssl/certs/ca-certificates.crt';
            $localCa  = (defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2)) . '/DigiCertGlobalRootG2.crt.pem';
            if (file_exists($systemCa)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $systemCa;
            } elseif (file_exists($localCa)) {
                $options[PDO::MYSQL_ATTR_SSL_CA] = $localCa;
            }
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new RuntimeException("Database connection failed.");
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function query($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function fetchOne($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    public function fetchColumn($sql, $params = []) {
        return $this->query($sql, $params)->fetchColumn();
    }

    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function prepare($sql) {
        return $this->connection->prepare($sql);
    }

    private function __clone() {}

    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
