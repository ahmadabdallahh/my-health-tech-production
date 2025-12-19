<?php
// config/mail_config.php

require_once __DIR__ . '/../config.php';

/**
 * PHPMailer Settings
 * These settings are now environment-aware.
 * Priority: 1. .env file, 2. system env vars, 3. Hardcoded fallbacks
 */
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com');
define('SMTP_USERNAME', $_ENV['SMTP_USERNAME'] ?? 'healthh.tech404@gmail.com');
define('SMTP_PASSWORD', $_ENV['SMTP_PASSWORD'] ?? 'rkaxfjgeyaiaxmpp');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls');

define('MAIL_FROM_ADDRESS', $_ENV['MAIL_FROM_ADDRESS'] ?? 'healthh.tech404@gmail.com');
define('MAIL_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? 'Health Tech');
define('MAIL_IS_HTML', true); // إرسال الرسائل كـ HTML
