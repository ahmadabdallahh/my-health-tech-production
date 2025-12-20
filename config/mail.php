<?php
// SMTP Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? getenv('SMTP_PORT') ?: 587);
define('SMTP_USERNAME', $_ENV['SMTP_USER'] ?? getenv('SMTP_USER') ?: 'healthh.tech404@gmail.com');
define('SMTP_PASSWORD', $_ENV['SMTP_PASS'] ?? getenv('SMTP_PASS') ?: 'rkaxfjgeyaiaxmpp');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? getenv('SMTP_SECURE') ?: 'tls');
define('SMTP_FROM_EMAIL', $_ENV['MAIL_FROM_ADDRESS'] ?? getenv('MAIL_FROM_ADDRESS') ?: 'healthh.tech404@gmail.com');
define('SMTP_FROM_NAME', $_ENV['MAIL_FROM_NAME'] ?? getenv('MAIL_FROM_NAME') ?: 'Health Tech');
define('MAIL_IS_HTML', true); 
