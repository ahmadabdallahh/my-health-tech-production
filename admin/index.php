<?php
session_start();
require_once '../includes/functions.php';
require_once '../config/database.php';

// Ensure only admins can access this page
if (!check_user_role('admin')) {
    redirect('../login.php');
}

// Get database connection
$db = new Database();
$conn = $db->getConnection();

// Fetch statistics
$total_patients = get_user_type_count($conn, 'patient');
$total_doctors = get_user_type_count($conn, 'doctor');
$total_appointments = get_total_count($conn, 'appointments');
$estimated_revenue = get_estimated_revenue($conn); // Dynamic calculation from database

// Fetch recent patients
$recent_patients = get_recent_patients($conn, 5);

$page_title = "لوحة تحكم المسؤول";
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Health Tech</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Alpine.js for dropdown menu -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }

        .content-area {
            transition: margin-right 0.3s ease;
        }

        .stat-card {
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">

    <div class="flex h-screen">
        <!-- Sidebar -->
        <?php include '../includes/dashboard_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden lg:mr-64">
            <?php include '../includes/dashboard_header.php'; ?>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
                <div class="container mx-auto">
                    <h3 class="text-2xl font-bold text-gray-800 mb-6">لوحة التحكم الرئيسية</h3>

                    <!-- Welcome Section -->
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-2xl p-8 text-white mb-8">
                        <h1 class="text-3xl font-bold mb-2">مرحباً بك، المسؤول! 👋</h1>
                        <p class="text-blue-100">نحن سعداء بوجودك في منصة Health Tech للرعاية الصحية</p>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                        <!-- Total Patients -->
                        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">إجمالي المرضى</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($total_patients); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">مستخدم نشط</p>
                                </div>
                                <div class="bg-blue-100 rounded-full p-3">
                                    <i class="fas fa-users text-blue-600 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Total Doctors -->
                        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">إجمالي الأطباء</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($total_doctors); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">طبيب مسجل</p>
                                </div>
                                <div class="bg-green-100 rounded-full p-3">
                                    <i class="fas fa-user-md text-green-600 text-xl"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Total Appointments -->
                        <div class="bg-white rounded-xl shadow-md p-6 border-l-4 border-purple-500">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm font-medium">إجمالي الحجوزات</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2"><?php echo number_format($total_appointments); ?></p>
                                    <p class="text-xs text-gray-400 mt-1">موعد محجوز</p>
                                </div>
                                <div class="bg-purple-100 rounded-full p-3">
                                    <i class="fas fa-calendar-check text-purple-600 text-xl"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">إجراءات سريعة</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <a href="manage_users.php" class="group bg-blue-50 p-4 rounded-lg hover:bg-blue-100 transition-colors">
                                <div class="text-blue-600 mb-3">
                                    <i class="fas fa-users-cog text-2xl"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 group-hover:text-blue-600">إدارة المستخدمين</h3>
                                <p class="text-sm text-gray-600 mt-1">عرض وتعديل حسابات المستخدمين</p>
                            </a>

                            <a href="manage_doctors.php" class="group bg-green-50 p-4 rounded-lg hover:bg-green-100 transition-colors">
                                <div class="text-green-600 mb-3">
                                    <i class="fas fa-user-md text-2xl"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 group-hover:text-green-600">إدارة الأطباء</h3>
                                <p class="text-sm text-gray-600 mt-1">متابعة الأطباء والطلبات</p>
                            </a>

                            <a href="manage_bookings.php" class="group bg-purple-50 p-4 rounded-lg hover:bg-purple-100 transition-colors">
                                <div class="text-purple-600 mb-3">
                                    <i class="fas fa-calendar-alt text-2xl"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 group-hover:text-purple-600">إدارة الحجوزات</h3>
                                <p class="text-sm text-gray-600 mt-1">عرض جميع المواعيد والحجوزات</p>
                            </a>

                            <a href="profile.php" class="group bg-orange-50 p-4 rounded-lg hover:bg-orange-100 transition-colors">
                                <div class="text-orange-600 mb-3">
                                    <i class="fas fa-user-circle text-2xl"></i>
                                </div>
                                <h3 class="font-semibold text-gray-800 group-hover:text-orange-600">الملف الشخصي</h3>
                                <p class="text-sm text-gray-600 mt-1">تعديل بيانات الحساب</p>
                            </a>
                        </div>
                    </div>

                    <!-- Recent Patients Table -->
                    <div class="bg-white rounded-xl shadow-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="text-xl font-bold text-gray-800">أحدث المرضى المسجلين</h4>
                            <a href="manage_users.php" class="text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center gap-1">
                                <span>عرض الكل</span>
                                <i class="fas fa-arrow-left"></i>
                            </a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">اسم المريض</th>
                                        <th scope="col" class="px-6 py-3">تاريخ التسجيل</th>
                                        <th scope="col" class="px-6 py-3">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_patients)): ?>
                                        <?php foreach ($recent_patients as $patient): ?>
                                            <tr class="bg-white border-b hover:bg-gray-50 transition-colors duration-150">
                                                <td class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                                    <div class="flex items-center">
                                                        <i class="fas fa-user-circle text-gray-400 ml-2"></i>
                                                        <span><?php echo htmlspecialchars($patient['full_name']); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <div class="flex items-center text-gray-600">
                                                        <i class="fas fa-calendar text-gray-400 ml-2"></i>
                                                        <span><?php echo date('d-m-Y', strtotime($patient['created_at'])); ?></span>
                                                    </div>
                                                </td>
                                                <td class="px-6 py-4">
                                                    <span class="px-3 py-1.5 text-xs font-semibold leading-tight rounded-full text-green-800 bg-green-100 border border-green-200 inline-flex items-center">
                                                        <i class="fas fa-check-circle ml-1"></i>
                                                        نشط
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr class="bg-white border-b">
                                            <td colspan="3" class="px-6 py-4 text-center text-gray-500">
                                                لا يوجد مرضى لعرضهم حالياً.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarToggle = document.getElementById('sidebar-toggle');
            const sidebar = document.getElementById('sidebar');

            if (sidebarToggle && sidebar) {
                // Event to toggle sidebar visibility
                sidebarToggle.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevents the click from bubbling up to the document
                    sidebar.classList.toggle('hidden');
                });

                // Event to hide sidebar when clicking outside
                document.addEventListener('click', function(e) {
                    const isClickInsideSidebar = sidebar.contains(e.target);
                    const isClickOnToggle = sidebarToggle.contains(e.target);

                    // If sidebar is visible and click is outside both sidebar and toggle button
                    if (!sidebar.classList.contains('hidden') && !isClickInsideSidebar && !isClickOnToggle) {
                        sidebar.classList.add('hidden');
                    }
                });
            }
        });
    </script>

</body>

</html>