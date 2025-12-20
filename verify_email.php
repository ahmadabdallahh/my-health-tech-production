<?php
/**
 * verify_email.php - Page to verify user account via OTP
 */
require_once 'includes/functions.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$email = $_GET['email'] ?? '';
$error = '';
$success = '';

// If no email is provided, redirect to login
if (empty($email)) {
    header("Location: login.php");
    exit;
}

try {
    $db = new Database();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT id, is_verified, verification_code FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        header("Location: login.php?error=user_not_found");
        exit;
    }

    if ($user['is_verified']) {
        header("Location: login.php?msg=verified");
        exit;
    }
} catch (Exception $e) {
    $error = 'خطأ في النظام: ' . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && !$success) {
    $otp = '';
    // Collect 6 digits from the form
    for ($i = 1; $i <= 6; $i++) {
        if (isset($_POST['otp' . $i])) {
            $otp .= sanitize_input($_POST['otp' . $i]);
        }
    }

    if (strlen($otp) < 6) {
        $error = 'يرجى إدخال الرمز المكون من 6 أرقام بالكامل.';
    } else {
        if ($user['verification_code'] === $otp) {
            try {
                // Update user as verified
                $update = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE id = ?");
                if ($update->execute([$user['id']])) {
                    $success = 'تم تفعيل حسابك بنجاح! يمكنك الآن تسجيل الدخول.';
                    // No need for autorefresh header here, we'll show it in the UI
                } else {
                    $error = 'حدث خطأ أثناء تفعيل الحساب. يرجى المحاولة مرة أخرى.';
                }
            } catch (Exception $e) {
                $error = 'حدث خطأ: ' . $e->getMessage();
            }
        } else {
            $error = 'رمز التحقق غير صحيح. يرجى المحاولة مرة أخرى.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تفعيل الحساب - Health Tech</title>
    <link rel="stylesheet" href="assets/css/login.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .otp-inputs {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin: 25px 0;
            direction: ltr;
        }
        .otp-inputs input {
            width: 45px;
            height: 55px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            border: 2px solid #ddd;
            border-radius: 8px;
            transition: all 0.3s ease;
        }
        .otp-inputs input:focus {
            border-color: #6c84ee;
            box-shadow: 0 0 8px rgba(108, 132, 238, 0.3);
            outline: none;
        }
        .resend-btn {
            color: #6c84ee;
            font-weight: 600;
            text-decoration: underline;
        }
        .email-display {
            color: #333;
            font-weight: 600;
            background: #f0f4ff;
            padding: 5px 12px;
            border-radius: 6px;
            display: inline-block;
            margin: 10px 0;
            word-break: break-all;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="image-section">
                <div class="image-content">
                    <img src="assets/images/doctor-illustration.png" alt="Verification Illustration">
                    <div class="welcome-text" style="bottom: 40px;">
                        <h2>خطوة واحدة تفصلك!</h2>
                        <p>قم بتأكيد حسابك للبدء في استخدام كافة مميزات Health Tech</p>
                    </div>
                </div>
            </div>
            <div class="form-section">
                <div class="form-content">
                    <div class="logo">
                        <i class="fas fa-heartbeat"></i>
                        <span>Health Tech</span>
                    </div>
                    <h3>تفعيل الحساب</h3>
                    
                    <?php if ($success): ?>
                        <div class="message success" style="display: block; text-align: center; padding: 30px;">
                            <i class="fas fa-check-circle" style="font-size: 3rem; display: block; margin-bottom: 15px;"></i>
                            <p style="font-size: 1.2rem;"><?php echo $success; ?></p>
                            <a href="login.php" class="btn-login" style="display: block; margin-top: 20px; text-decoration: none;">انتقل لتسجيل الدخول</a>
                        </div>
                    <?php else: ?>
                        <p>لقد أرسلنا رمز المكون من 6 أرقام إلى:</p>
                        <div class="email-display"><?php echo htmlspecialchars($email); ?></div>

                        <?php if ($error): ?>
                            <div class="message error">
                                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="otpForm">
                            <div class="otp-inputs">
                                <input type="text" name="otp1" maxlength="1" pattern="[0-9]" required autofocus autocomplete="off">
                                <input type="text" name="otp2" maxlength="1" pattern="[0-9]" required autocomplete="off">
                                <input type="text" name="otp3" maxlength="1" pattern="[0-9]" required autocomplete="off">
                                <input type="text" name="otp4" maxlength="1" pattern="[0-9]" required autocomplete="off">
                                <input type="text" name="otp5" maxlength="1" pattern="[0-9]" required autocomplete="off">
                                <input type="text" name="otp6" maxlength="1" pattern="[0-9]" required autocomplete="off">
                            </div>
                            <button type="submit" class="btn-login">تفعيل الحساب</button>
                        </form>

                        <div class="links">
                            <p>لم يصلك الرمز؟ 
                                <a href="resend_code.php?email=<?php echo urlencode($email); ?>" class="resend-btn">إعادة الإرسال</a>
                            </p>
                            <p><a href="login.php"><i class="fas fa-arrow-right"></i> العودة لتسجيل الدخول</a></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        const inputs = document.querySelectorAll('.otp-inputs input');
        
        inputs.forEach((input, index) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && e.target.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('keypress', (e) => {
                if (!/[0-9]/.test(e.key)) {
                    e.preventDefault();
                }
            });
        });

        inputs[0].addEventListener('paste', (e) => {
            e.preventDefault();
            const pasteData = e.clipboardData.getData('text').slice(0, 6);
            if (!/^[0-9]+$/.test(pasteData)) return;

            for (let i = 0; i < pasteData.length; i++) {
                if (inputs[i]) {
                    inputs[i].value = pasteData[i];
                    if (i < inputs.length - 1) inputs[i+1].focus();
                }
            }
        });
    </script>
</body>
</html>
