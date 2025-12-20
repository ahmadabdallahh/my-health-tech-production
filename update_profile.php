<?php
/**
 * update_profile.php - AJAX handler for profile updates
 */
require_once 'config.php';
require_once 'includes/functions.php';

header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'جلسة العمل انتهت، يرجى تسجيل الدخول مرة أخرى.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $user_id = $_SESSION['user_id'];
    
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $username = sanitize_input($_POST['username'] ?? '');
    $phone = sanitize_input($_POST['phone'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($full_name) || empty($email) || empty($username)) {
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة (الاسم، البريد، اسم المستخدم)']);
        exit;
    }

    try {
        // Use the global $conn if it exists, or get from Database class
        if (!isset($conn) || !($conn instanceof PDO)) {
            $db = new Database();
            $conn_obj = $db->getConnection();
        } else {
            $conn_obj = $conn;
        }

        // 1. Check if email or username is already taken
        $check = $conn_obj->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $check->execute([$email, $username, $user_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني أو اسم المستخدم مستخدم بالفعل.']);
            exit;
        }

        // 2. Begin Transaction
        $conn_obj->beginTransaction();

        // 3. Update basic info
        $stmt = $conn_obj->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $email, $username, $phone, $user_id]);

        // 4. Update password if requested
        if (!empty($new_password)) {
            $pw_stmt = $conn_obj->prepare("SELECT password FROM users WHERE id = ?");
            $pw_stmt->execute([$user_id]);
            $user_pw = $pw_stmt->fetchColumn();

            if (password_verify($current_password, $user_pw)) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 8) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $upd_pw = $conn_obj->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $upd_pw->execute([$hashed, $user_id]);
                    } else {
                        throw new Exception('كلمة المرور الجديدة يجب أن تكون 8 أحرف على الأقل.');
                    }
                } else {
                    throw new Exception('كلمة المرور الجديدة غير متطابقة.');
                }
            } else {
                throw new Exception('كلمة المرور الحالية غير صحيحة.');
            }
        }

        $conn_obj->commit();

        // Update session
        $_SESSION['user_name'] = $full_name;
        $_SESSION['email'] = $email;

        // Fetch updated user
        $stmt = $conn_obj->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $final_user = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'success' => true, 
            'message' => 'تم تحديث البيانات بنجاح.',
            'user' => $final_user
        ]);

    } catch (Exception $e) {
        if (isset($conn_obj) && $conn_obj->inTransaction()) {
            $conn_obj->rollBack();
        }
        echo json_encode(['success' => false, 'message' => 'حدث خطأ: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح.']);
}
