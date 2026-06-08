<?php
require_once '../bootstrap.php';
try {
    $db = Database::getInstance();
    echo "<h1>Success!</h1>";
    echo "<p>Database connection established successfully inside Docker.</p>";
} catch (Exception $e) {
    echo "<h1>Connection Failed</h1>";
    echo "<p>" . $e->getMessage() . "</p>";
}
?>