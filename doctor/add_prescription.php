<?php
require_once '../includes/functions.php';
require_once '../includes/prescription_functions.php';

if (!is_logged_in() || !is_doctor()) {
    header("Location: ../login.php");
    exit();
}

$user = get_logged_in_user();
$doctor_id = $user['id'];
$success_msg = '';
$error_msg = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'] ?? null;
    $diagnosis = $_POST['diagnosis'] ?? '';
    $notes = $_POST['notes'] ?? '';
    $appointment_id = !empty($_POST['appointment_id']) ? $_POST['appointment_id'] : null;
    
    // Process items
    $items = [];
    if (isset($_POST['drug_name']) && is_array($_POST['drug_name'])) {
        for ($i = 0; $i < count($_POST['drug_name']); $i++) {
            if (!empty($_POST['drug_name'][$i])) {
                $items[] = [
                    'drug_name' => $_POST['drug_name'][$i],
                    'dosage' => $_POST['dosage'][$i] ?? '',
                    'frequency' => $_POST['frequency'][$i] ?? '',
                    'duration' => $_POST['duration'][$i] ?? '',
                    'instructions' => $_POST['instructions'][$i] ?? '',
                ];
            }
        }
    }

    if ($patient_id && !empty($items)) {
        $result_id = create_prescription($appointment_id, $doctor_id, $patient_id, $diagnosis, $notes, $items);
        if ($result_id) {
            header("Location: view_prescription.php?id=$result_id");
            exit();
        } else {
            $error_msg = "حدث خطأ أثناء حفظ الروشتة";
        }
    } else {
        $error_msg = "الرجاء اختيار المريض وإضافة دواء واحد على الأقل";
    }
}

// Get Data for Form
$db = new Database();
$conn = $db->getConnection();
$stmtP = $conn->prepare("SELECT id, full_name, phone FROM users WHERE role = 'patient' OR user_type = 'patient' ORDER BY full_name");
$stmtP->execute();
$patients = $stmtP->fetchAll(PDO::FETCH_ASSOC);

// Pre-fill patient if existing parameter
$selected_patient_id = $_GET['patient_id'] ?? null;
$selected_appointment_id = $_GET['appointment_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إضافة روشتة جديدة | صحة</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts (Cairo) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&display=swap" rel="stylesheet">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif']
                    }
                }
            }
        }
    </script>
    
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
        .medicine-row {
            animation: fadeIn 0.3s ease-out;
        }
    </style>
</head>
<body class="font-cairo bg-gray-50 min-h-screen">

    <?php require_once '../includes/header.php'; ?>

    <main class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
            
            <!-- Page Header -->
            <div class="text-center mb-8 animate-fade-in">
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-2">
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">
                        إضافة روشتة طبية جديدة
                    </span>
                </h1>
                <p class="text-gray-500 text-lg">قم بملء البيانات التالية لإنشاء وصفة طبية للمريض</p>
            </div>

            <?php if ($error_msg): ?>
                <div class="mb-6 bg-red-50 border-r-4 border-red-500 p-4 rounded-lg animate-fade-in">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-500 text-xl ml-3"></i>
                        <p class="text-red-800 font-medium"><?php echo $error_msg; ?></p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden animate-fade-in">
                <form method="POST" class="p-6 md:p-8 space-y-6">
                    <input type="hidden" name="appointment_id" value="<?php echo htmlspecialchars($selected_appointment_id ?? ''); ?>">
                    
                    <!-- Patient Selection -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700 flex items-center">
                            <i class="fas fa-user-injured text-blue-500 ml-2"></i>
                            اختر المريض
                        </label>
                        <p class="text-gray-500 text-sm">اختر المريض من القائمة</p>
                        <select name="patient_id" required
                                class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 text-gray-900 font-medium">
                            <option value="">-- اختر المريض --</option>
                            <?php foreach ($patients as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo ($selected_patient_id == $p['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['full_name']); ?> (<?php echo htmlspecialchars($p['phone']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Diagnosis -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700 flex items-center">
                            <i class="fas fa-stethoscope text-blue-500 ml-2"></i>
                            التشخيص
                        </label>
                        <p class="text-gray-500 text-sm">أدخل تشخيص الحالة بالتفصيل</p>
                        <textarea name="diagnosis" required rows="4"
                                  placeholder="أدخل تشخيص الحالة بالتفصيل..."
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 resize-none"></textarea>
                    </div>

                    <!-- Medications Section -->
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="block text-sm font-bold text-gray-700 flex items-center">
                                <i class="fas fa-pills text-blue-500 ml-2"></i>
                                الأدوية والعلاج
                            </label>
                            <button type="button" onclick="addMedicineRow()"
                                    class="inline-flex items-center px-4 py-2 bg-blue-50 text-blue-600 font-bold rounded-lg hover:bg-blue-100 transition-colors duration-200">
                                <i class="fas fa-plus ml-2"></i>
                                إضافة دواء
                            </button>
                        </div>

                        <div id="medicines_container" class="space-y-4">
                            <!-- Initial Medicine Row -->
                            <div class="medicine-row bg-gradient-to-br from-gray-50 to-blue-50/30 p-4 rounded-xl border-2 border-gray-200 space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <input type="text" name="drug_name[]" required
                                           placeholder="اسم الدواء *"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                    <input type="text" name="dosage[]"
                                           placeholder="الجرعة (مثال: 500mg)"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <input type="text" name="frequency[]"
                                           placeholder="التكرار (مثال: 3 مرات يومياً)"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                    <input type="text" name="duration[]"
                                           placeholder="المدة (مثال: أسبوع)"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                    <input type="text" name="instructions[]"
                                           placeholder="ملاحظات (مثال: بعد الأكل)"
                                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                                </div>
                                <div class="flex justify-end">
                                    <button type="button" onclick="removeRow(this)"
                                            class="text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1 rounded-lg transition-colors duration-200 font-medium">
                                        <i class="fas fa-trash ml-1"></i>
                                        حذف
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div class="space-y-2">
                        <label class="block text-sm font-bold text-gray-700 flex items-center">
                            <i class="fas fa-sticky-note text-blue-500 ml-2"></i>
                            ملاحظات إضافية (اختياري)
                        </label>
                        <textarea name="notes" rows="3"
                                  placeholder="أي ملاحظات أو تعليمات إضافية للمريض..."
                                  class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-4 focus:ring-blue-100 transition-all duration-200 resize-none"></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-3 pt-4">
                        <button type="submit"
                                class="flex-1 inline-flex items-center justify-center px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-bold rounded-xl hover:from-blue-700 hover:to-indigo-700 shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            <i class="fas fa-save ml-2"></i>
                            حفظ الروشتة
                        </button>
                        <a href="prescriptions.php"
                           class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-4 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-all duration-200">
                            <i class="fas fa-times ml-2"></i>
                            إلغاء
                        </a>
                    </div>
                </form>
            </div>

            <!-- Help Text -->
            <div class="mt-6 bg-blue-50 border-r-4 border-blue-400 p-4 rounded-lg animate-fade-in">
                <div class="flex items-start">
                    <i class="fas fa-info-circle text-blue-500 text-xl ml-3 mt-0.5"></i>
                    <div class="text-sm text-blue-800">
                        <p class="font-bold mb-1">نصائح لكتابة الروشتة:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>تأكد من كتابة اسم الدواء بشكل واضح ودقيق</li>
                            <li>حدد الجرعة والتكرار والمدة بدقة</li>
                            <li>أضف أي تعليمات خاصة في حقل الملاحظات</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>
    </main>

    <?php include '../includes/footer.php'; ?>

    <script>
        function addMedicineRow() {
            const container = document.getElementById('medicines_container');
            const row = document.createElement('div');
            row.className = 'medicine-row bg-gradient-to-br from-gray-50 to-blue-50/30 p-4 rounded-xl border-2 border-gray-200 space-y-3';
            row.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input type="text" name="drug_name[]"
                           placeholder="اسم الدواء"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    <input type="text" name="dosage[]"
                           placeholder="الجرعة (مثال: 500mg)"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input type="text" name="frequency[]"
                           placeholder="التكرار (مثال: 3 مرات يومياً)"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    <input type="text" name="duration[]"
                           placeholder="المدة (مثال: أسبوع)"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                    <input type="text" name="instructions[]"
                           placeholder="ملاحظات (مثال: بعد الأكل)"
                           class="px-3 py-2 border border-gray-300 rounded-lg focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition-all">
                </div>
                <div class="flex justify-end">
                    <button type="button" onclick="removeRow(this)"
                            class="text-red-500 hover:text-red-700 hover:bg-red-50 px-3 py-1 rounded-lg transition-colors duration-200 font-medium">
                        <i class="fas fa-trash ml-1"></i>
                        حذف
                    </button>
                </div>
            `;
            container.appendChild(row);
        }

        function removeRow(btn) {
            const rows = document.querySelectorAll('.medicine-row');
            if (rows.length > 1) {
                btn.closest('.medicine-row').remove();
            } else {
                alert('يجب إضافة دواء واحد على الأقل');
            }
        }
    </script>
</body>
</html>
