<?php
// config.php

// **Simple .env Loader** //
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($name) . '=' . trim($value));
    }
}
loadEnv(__DIR__ . '/.env');

// **Logging & Security Handlers** //
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
    file_put_contents($log_dir . '/.htaccess', "Deny from all");
}
ini_set('log_errors', 1);
ini_set('error_log', $log_dir . '/error_'.date('Y-m-d').'.log');

// **Environment Detection** //
$app_env = $_ENV['APP_ENV'] ?? (in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']) || $_SERVER['HTTP_HOST'] === 'localhost' ? 'development' : 'production');

if ($app_env === 'development') {
    // **Local Development Configuration** //
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'medical_booking_test');
    define('DB_USER', $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'root');
    define('DB_PASS', $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '');
    
    // Enable error reporting for development
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    // **Production Configuration** //
    define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
    define('DB_PORT', $_ENV['DB_PORT'] ?? '3306');
    define('DB_NAME', $_ENV['DB_NAME'] ?? 'your_production_db_name');
    define('DB_USER', $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? 'your_production_db_user');
    define('DB_PASS', $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? '');

    // Disable error reporting for production for security
    error_reporting(0);
    ini_set('display_errors', 0);
}

// **Base URL Detection** //
if (!defined('BASE_URL')) {
    $env_url = $_ENV['BASE_URL'] ?? null;
    if ($env_url) {
        define('BASE_URL', rtrim($env_url, '/') . '/');
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'];
        $script_name = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
        $base_path = rtrim($script_name, '/') . '/';
        define('BASE_URL', $protocol . $host . $base_path);
    }
}

// Start session if not started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// **Database Connection Helper** //
// This provides a global $conn for legacy scripts, but using the Database class is preferred.
try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    if ($is_localhost) {
        die('Database Connection Error: ' . $e->getMessage());
    } else {
        die('عذراً، حدث خطأ أثناء الاتصال بقاعدة البيانات. يرجى المحاولة مرة أخرى لاحقاً.');
    }
}
