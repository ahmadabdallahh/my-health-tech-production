<?php
// db_check.php - Simple DB connection check
require_once 'config.php';
header('Content-Type: text/plain');

echo "Environment: " . (isset($_ENV['APP_ENV']) ? $_ENV['APP_ENV'] : 'N/A') . "\n";
echo "Host: " . DB_HOST . ":" . DB_PORT . "\n";
echo "DB: " . DB_NAME . "\n";

try {
    $stmt = $conn->query("SELECT 1 as result");
    $row = $stmt->fetch();
    if ($row && $row['result'] == 1) {
        echo "SUCCESS: Database connection working!\n";
    } else {
        echo "ERROR: Unexpected result from database.\n";
    }
} catch (Exception $e) {
    echo "FAILURE: " . $e->getMessage() . "\n";
}
?>
