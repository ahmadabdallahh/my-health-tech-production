<?php
require_once 'config.php';
require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    $stmt = $conn->query('DESCRIBE hospitals');
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} catch (Exception $e) {
    echo $e->getMessage();
}
