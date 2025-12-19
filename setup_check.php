<?php
/**
 * Setup Check Utility
 * Use this script on your production server to verify the environment.
 * IMPORTANT: Delete this file after verification for security!
 */

require_once 'config.php';

echo "<!DOCTYPE html><html><head><meta charset='utf-8'><title>Setup Check</title>";
echo "<style>body{font-family:sans-serif;padding:20px;line-height:1.6;} .ok{color:green;} .fail{color:red;} .warning{color:orange;}</style>";
echo "</head><body>";
echo "<h1>فحص إعدادات الموقع (Setup Check)</h1>";

function check($title, $condition, $fail_msg, $is_warning = false) {
    echo "<div><strong>$title:</strong> ";
    if ($condition) {
        echo "<span class='ok'>✅ جاهز</span>";
    } else {
        $class = $is_warning ? 'warning' : 'fail';
        $icon = $is_warning ? '⚠️' : '❌';
        echo "<span class='$class'>$icon $fail_msg</span>";
    }
    echo "</div>";
}

// 1. PHP Version
check("إصدار PHP", version_compare(PHP_VERSION, '7.4.0', '>='), "يُفضل استخدام PHP 7.4 أو أحدث. إصدارك الحالي: " . PHP_VERSION);

// 2. Database Connection
$db_ok = false;
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $test_conn = new PDO($dsn, DB_USER, DB_PASS);
    $db_ok = true;
} catch (Exception $e) {
    $db_error = $e->getMessage();
}
check("الاتصال بقاعدة البيانات", $db_ok, "فشل الاتصال: " . ($db_error ?? 'خطأ غير معروف'));

// 3. Write Permissions
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0755, true);
}
check("صلاحيات مجلد الرفع (uploads/)", is_writable($upload_dir), "المجلد غير قابل للكتابة. يرجى تعديل التصاريح (CHMOD 755/777)");

// 4. Base URL
echo "<div><strong>رابط الموقع المكتشف (BASE_URL):</strong> <code>" . BASE_URL . "</code></div>";

// 5. Important Extensions
check("إضافة PDO MySQL", extension_loaded('pdo_mysql'), "إضافة pdo_mysql غير مفعلة");
check("إضافة GD (للصور)", extension_loaded('gd'), "إضافة gd غير مفعلة (قد تواجه مشاكل في رفع الصور)", true);
check("إضافة MBString", extension_loaded('mbstring'), "إضافة mbstring غير مفعلة", true);

echo "<hr><p style='color:red;'><strong>هام جداً:</strong> قم بحذف هذا الملف (<code>setup_check.php</code>) فور الانتهاء من الفحص لأسباب أمنية.</p>";
echo "</body></html>";
