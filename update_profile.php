<?php
/**
 * update_profile.php - AJAX handler for profile updates
 */
require_once 'config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

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
        echo json_encode(['success' => false, 'message' => 'يرجى ملء جميع الحقول المطلوبة.']);
        exit;
    }

    try {
        $db = new Database();
        $conn = $db->getConnection();

        // 1. Check if email or username is already taken by another user
        $check = $conn->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
        $check->execute([$email, $username, $user_id]);
        if ($check->fetch()) {
            echo json_encode(['success' => false, 'message' => 'البريد الإلكتروني أو اسم المستخدم محجوز بالفعل.']);
            exit;
        }

        // 2. Begin Transaction
        $conn->beginTransaction();

        // 3. Update basic info
        $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, username = ?, phone = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$full_name, $email, $username, $phone, $user_id]);

        // 4. Update password if requested
        if (!empty($new_password)) {
            // Get current password hash
            $pw_stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
            $pw_stmt->execute([$user_id]);
            $user_pw = $pw_stmt->fetchColumn();

            if (password_verify($current_password, $user_pw)) {
                if ($new_password === $confirm_password) {
                    if (strlen($new_password) >= 8) {
                        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
                        $upd_pw = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
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

        $conn->commit();

        // Update session
        $_SESSION['user_name'] = $full_name;
        $_SESSION['email'] = $email;

        // Fetch updated user data to return
        $final_user = get_logged_in_user();

        echo json_encode([
            'success' => true, 
            'message' => 'تم تحديث البيانات بنجاح.',
            'user' => $final_user
        ]);

    } catch (Exception $e) {
        if (isset($conn)) $conn->rollBack();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'طلب غير صالح.']);
}
