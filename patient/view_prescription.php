<?php
require_once '../includes/functions.php';
require_once '../includes/prescription_functions.php';

if (!is_logged_in() || !is_patient()) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['id'])) {
    die("رقم الروشتة غير محدد");
}

$prescription = get_prescription_details($_GET['id']);

if (!$prescription) {
    die("الروشتة غير موجودة");
}

// Verify this prescription belongs to this patient
if ($prescription['patient_id'] != $_SESSION['user_id']) {
    die("ليس لديك صلاحية لعرض هذه الروشتة");
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>روشتة طبية #<?php echo $prescription['id']; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600&family=Great+Vibes&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #edeff0; margin: 0; padding: 20px; }
        .prescription-container {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            position: relative;
        }
        .header {
            border-bottom: 2px solid #000;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .doctor-info h1 { margin: 0 0 5px 0; color: #2c3e50; font-size: 24px; }
        .doctor-info p { margin: 2px 0; color: #7f8c8d; }
        .clinic-info { text-align: left; }
        .clinic-info h2 { margin: 0 0 5px 0; color: #2c3e50; font-size: 20px; }
        .meta-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
        }
        .meta-item strong { color: #2c3e50; }
        .diagnosis-section { margin-bottom: 30px; }
        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .rx-symbol {
            font-family: serif;
            font-size: 40px;
            font-weight: bold;
            font-style: italic;
            color: #3498db;
            margin-bottom: 10px;
        }
        .medicine-list { margin-bottom: 30px; }
        .medicine-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px dashed #eee;
        }
        .medicine-name { font-weight: bold; font-size: 18px; color: #2c3e50; }
        .medicine-details { color: #555; margin-right: 20px; margin-top: 5px; }
        .instructions { color: #e74c3c; font-style: italic; display: block; margin-top: 5px; }
        
        .footer {
            margin-top: 50px;
            border-top: 2px solid #000;
            padding-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box { text-align: center; width: 220px; }
        .signature-line { border-bottom: 1px solid #000; margin: 40px auto 10px; width: 100%; }
        .signature-text {
            font-family: 'Great Vibes', 'Segoe UI', cursive;
            font-size: 32px;
            color: #2c3e50;
        }
        .signature-meta {
            margin-top: 5px;
            font-size: 13px;
            color: #7f8c8d;
        }

        .print-btn {
            position: fixed;
            bottom: 20px;
            left: 20px;
            background: #3498db;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(52, 152, 219, 0.4);
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }
        .back-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #95a5a6;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 30px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(149, 165, 166, 0.4);
            font-weight: bold;
            text-decoration: none;
        }
        
        @media print {
            body { background: white; padding: 0; }
            .prescription-container { box-shadow: none; width: 100%; max-width: 100%; padding: 20px; }
            .print-btn, .back-btn { display: none; }
        }
    </style>
    <!-- Add Font Awesome for icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>

    <div class="prescription-container">
        <div class="header">
            <div class="doctor-info">
                <h1>د. <?php echo htmlspecialchars($prescription['doctor_name']); ?></h1>
                <p><?php echo htmlspecialchars($prescription['specialty_name']); ?></p>
            </div>
            <div class="clinic-info">
                <h2><?php echo htmlspecialchars($prescription['clinic_name']); ?></h2>
                <p>التاريخ: <?php echo date('Y/m/d', strtotime($prescription['created_at'])); ?></p>
            </div>
        </div>

        <div class="meta-info">
            <div class="meta-item">
                <strong>اسم المريض:</strong> <?php echo htmlspecialchars($prescription['patient_name']); ?>
            </div>
            <div class="meta-item">
                <strong>رقم الروشتة:</strong> #<?php echo $prescription['id']; ?>
            </div>
        </div>

        <?php if (!empty($prescription['diagnosis'])): ?>
        <div class="diagnosis-section">
            <div class="section-title">التشخيص (Diagnosis)</div>
            <p><?php echo nl2br(htmlspecialchars($prescription['diagnosis'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="rx-symbol">Rx</div>

        <div class="medicine-list">
            <?php foreach ($prescription['items'] as $item): ?>
            <div class="medicine-item">
                <div class="medicine-name">
                    • <?php echo htmlspecialchars($item['drug_name']); ?> 
                    <?php if(!empty($item['dosage'])) echo " - " . htmlspecialchars($item['dosage']); ?>
                </div>
                <div class="medicine-details">
                    <?php 
                    $details = [];
                    if(!empty($item['frequency'])) $details[] = "التكرار: " . htmlspecialchars($item['frequency']);
                    if(!empty($item['duration'])) $details[] = "المدة: " . htmlspecialchars($item['duration']);
                    echo implode(' | ', $details);
                    ?>
                </div>
                <?php if(!empty($item['instructions'])): ?>
                <span class="instructions">
                    <i class="fas fa-info-circle"></i> <?php echo htmlspecialchars($item['instructions']); ?>
                </span>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (!empty($prescription['notes'])): ?>
        <div class="diagnosis-section">
            <div class="section-title">ملاحظات (Notes)</div>
            <p><?php echo nl2br(htmlspecialchars($prescription['notes'])); ?></p>
        </div>
        <?php endif; ?>

        <div class="footer">
            <div style="font-size: 12px; color: #999;">
                تم إنشاء هذه الروشتة إلكترونياً عبر نظام صحة
            </div>
            <div class="signature-box">
                التوقيع
                <div class="signature-line"></div>
                <div class="signature-text">د. <?php echo htmlspecialchars($prescription['doctor_name']); ?></div>
                <?php if (!empty($prescription['specialty_name'])): ?>
                <div class="signature-meta"><?php echo htmlspecialchars($prescription['specialty_name']); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <a href="#" onclick="window.print()" class="print-btn">
        <i class="fas fa-print"></i> طباعة
    </a>
    <a href="prescriptions.php" class="back-btn">
        <i class="fas fa-arrow-right"></i> رجوع
    </a>

</body>
</html>
