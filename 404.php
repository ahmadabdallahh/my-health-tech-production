<?php
require_once 'config.php';
require_once 'config/database.php';
require_once 'includes/functions.php';

$pageTitle = 'الصفحة غير موجودة - Health Tech';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;900&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="bg-gray-50 flex flex-col min-h-screen">
    <?php include 'includes/header.php'; ?>

    <main class="flex-grow flex items-center justify-center py-20">
        <div class="text-center px-4">
            <div class="mb-8">
                <i class="fas fa-exclamation-triangle text-9xl text-blue-500 animate-pulse"></i>
            </div>
            <h1 class="text-6xl font-black text-gray-900 mb-4">404</h1>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">عذراً، الصفحة التي تبحث عنها غير موجودة</h2>
            <p class="text-lg text-gray-600 mb-10 max-w-lg mx-auto">
                ربما تم نقل الصفحة، أو تم حذفها، أو الرابط الذي اتبعته غير صحيح.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="<?php echo BASE_URL; ?>index.php" class="px-8 py-3 bg-blue-600 text-white font-bold rounded-lg hover:bg-blue-700 transition shadow-lg hover:shadow-blue-500/30 flex items-center gap-2">
                    <i class="fas fa-home"></i>
                    العودة للرئيسية
                </a>
                <a href="<?php echo BASE_URL; ?>search.php" class="px-8 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition flex items-center gap-2">
                    <i class="fas fa-search"></i>
                    البحث عن دكتور
                </a>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
</body>

</html>