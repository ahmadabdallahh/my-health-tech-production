<?php
require_once '../includes/functions.php';
require_once '../includes/prescription_functions.php';

if (!is_logged_in() || !is_patient()) {
    header("Location: ../login.php");
    exit();
}

$user = get_logged_in_user();
$patient_id = $user['id'];

// Get prescriptions
$prescriptions = get_patient_prescriptions($patient_id);
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روشتاتي | صحة</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts (Cairo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <script>
        // Custom Tailwind Config
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f3f4f6;
        }

        /* Smooth fade in animation */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .prescription-card {
            transition: all 0.3s ease;
        }

        .prescription-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-cairo flex flex-col min-h-screen">

    <?php require_once '../includes/header.php'; ?>

    <main class="flex-1 py-10 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Page Header -->
            <div class="text-center mb-10 animate-fade-in">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                        سجل وصفاتي الطبية
                    </span>
                </h1>
                <p class="text-gray-500 text-lg">جميع الروشيتات التي تم صرفها لك</p>
            </div>

            <!-- Prescriptions Content -->
            <?php if (empty($prescriptions)): ?>
                <div class="animate-fade-in bg-white rounded-3xl shadow-xl p-12 text-center max-w-2xl mx-auto border border-gray-100">
                    <div class="w-24 h-24 bg-blue-50 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-file-medical-alt text-4xl text-blue-500"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">لا توجد روشتات مسجلة بعد</h3>
                    <p class="text-gray-500 mb-8 leading-relaxed">ستظهر هنا جميع الوصفات الطبية التي يتم صرفها لك من قبل الأطباء</p>
                    <a href="../search.php" class="inline-flex items-center justify-center px-8 py-4 border border-transparent text-base font-bold rounded-xl text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1">
                        <i class="fas fa-search ml-2"></i>
                        ابحث عن طبيب
                    </a>
                </div>
            <?php else: ?>

                <!-- Desktop View (Table) -->
                <div class="hidden md:block bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100 animate-fade-in">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        رقم الروشتة
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        الدكتور
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        التخصص
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        التشخيص
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        التاريخ
                                    </th>
                                    <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">
                                        الإجراءات
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php foreach ($prescriptions as $index => $p): ?>
                                    <tr class="hover:bg-blue-50/30 transition-colors duration-200" style="animation-delay: <?php echo $index * 100; ?>ms">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center justify-center">
                                                <span class="px-3 py-1 bg-blue-100 text-blue-700 font-bold text-lg rounded-lg shadow-sm">
                                                    #<?php echo $p['id']; ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center text-blue-600 shadow-sm ml-3">
                                                    <i class="fas fa-user-md"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-gray-900"><?php echo htmlspecialchars($p['doctor_name']); ?></div>
                                                    <div class="text-xs text-gray-500">طبيب متخصص</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <?php if (!empty($p['specialty_name'])): ?>
                                                <span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full">
                                                    <?php echo htmlspecialchars($p['specialty_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-gray-400 text-xs">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm text-gray-900 font-medium max-w-xs truncate" title="<?php echo htmlspecialchars($p['diagnosis']); ?>">
                                                <?php echo htmlspecialchars(mb_strimwidth($p['diagnosis'], 0, 50, "...")); ?>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="text-sm font-bold text-gray-800 flex items-center">
                                                    <i class="far fa-calendar ml-2 text-blue-500"></i>
                                                    <?php echo date('d M Y', strtotime($p['created_at'])); ?>
                                                </span>
                                                <span class="text-xs text-gray-500 mt-1 flex items-center">
                                                    <i class="far fa-clock ml-2 text-blue-400"></i>
                                                    <?php echo date('H:i', strtotime($p['created_at'])); ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="view_prescription.php?id=<?php echo $p['id']; ?>"
                                               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                                <i class="fas fa-eye ml-2"></i>
                                                عرض التفاصيل
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile View (Cards) -->
                <div class="md:hidden space-y-4 animate-fade-in">
                    <?php foreach ($prescriptions as $index => $p): ?>
                        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100 prescription-card" style="animation-delay: <?php echo $index * 100; ?>ms">
                            <!-- Header: Prescription ID & Doctor Info -->
                            <div class="flex justify-between items-start mb-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-12 w-12 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center text-blue-600 shadow-sm ml-3">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($p['doctor_name']); ?></h3>
                                        <p class="text-sm text-blue-600 font-medium"><?php echo htmlspecialchars($p['specialty_name'] ?: 'طبيب متخصص'); ?></p>
                                    </div>
                                </div>
                                <div class="px-3 py-1 bg-blue-100 text-blue-700 font-bold text-lg rounded-lg shadow-sm">
                                    #<?php echo $p['id']; ?>
                                </div>
                            </div>

                            <!-- Details -->
                            <div class="space-y-3 mb-5 bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-start">
                                    <div class="w-8 flex justify-center ml-2 mt-1">
                                        <i class="fas fa-stethoscope text-blue-500"></i>
                                    </div>
                                    <div class="flex-1">
                                        <span class="text-xs text-gray-500 font-medium">التشخيص:</span>
                                        <p class="text-sm text-gray-700 font-medium mt-1"><?php echo htmlspecialchars($p['diagnosis']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center text-gray-700">
                                    <div class="w-8 flex justify-center ml-2">
                                        <i class="far fa-calendar-alt text-blue-500"></i>
                                    </div>
                                    <span class="font-medium"><?php echo date('d M Y', strtotime($p['created_at'])); ?></span>
                                </div>
                                <div class="flex items-center text-gray-700">
                                    <div class="w-8 flex justify-center ml-2">
                                        <i class="far fa-clock text-blue-500"></i>
                                    </div>
                                    <span class="font-medium"><?php echo date('H:i', strtotime($p['created_at'])); ?></span>
                                </div>
                            </div>

                            <!-- Actions -->
                            <a href="view_prescription.php?id=<?php echo $p['id']; ?>"
                               class="block w-full text-center bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold py-3 rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-eye ml-2"></i>
                                عرض التفاصيل
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </main>

    <?php include '../includes/footer.php'; ?>
</body>
</html>
