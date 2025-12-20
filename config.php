<?php
// config.php

// **Simple .env Loader** //
function loadEnv($path) {
    if (!file_exists($path)) {
        error_log("Env file not found at: " . $path);
        return false;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0 || !strpos($line, '=')) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim(trim($value), "\"'");
        $_ENV[$name] = $value;
        $_SERVER[$name] = $value;
        putenv("$name=$value");
    }
    return true;
}
$env_path = __DIR__ . '/.env';
$is_env_loaded = loadEnv($env_path);

if (!$is_env_loaded) {
    // If we're in production, we might want to know why it's failing
    error_log("CRITICAL: .env file missing at " . $env_path);
}


// **Logging & Security Handlers** //
$log_dir = __DIR__ . '/logs';
if (!is_dir($log_dir)) {
    @mkdir($log_dir, 0755, true);
    file_put_contents($log_dir . '/.htaccess', "Deny from all");
}
ini_set('log_errors', 1);
ini_set('error_log', $log_dir . '/error_'.date('Y-m-d').'.log');

// **Environment Detection** //
$app_env = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?? 'development';

if ($app_env === 'development' || (isset($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'localhost') !== false)) {
    $app_env = 'development';
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// **Database Configuration Constants** //
define('DB_HOST', $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306');
define('DB_NAME', $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'medical_booking_test');
define('DB_USER', $_ENV['DB_USERNAME'] ?? $_ENV['DB_USER'] ?? getenv('DB_USERNAME') ?? getenv('DB_USER') ?: 'root');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? getenv('DB_PASSWORD') ?? getenv('DB_PASS') ?: '');


// **Base URL Detection** //
if (!defined('BASE_URL')) {
    $env_url = $_ENV['BASE_URL'] ?? getenv('BASE_URL') ?? null;
    if ($env_url) {
        define('BASE_URL', rtrim($env_url, '/') . '/');
    } else {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        define('BASE_URL', $protocol . $host . '/');
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
        PDO::ATTR_TIMEOUT            => 5, // Timeout after 5 seconds
    ];
    $conn = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    error_log('Database Connection Error: ' . $e->getMessage());
    if ($app_env === 'development') {
        die("<h3>❌ Database Error</h3>" . $e->getMessage());
    } else {
        die('عذراً، حدث خطأ أثناء الاتصال بقاعدة البيانات. يرجى المحاولة مرة أخرى لاحقاً.');
    }
}

