<?php
require_once __DIR__ . '/../config/database.php';

/**
 * Create a new prescription
 * 
 * @param int|null $appointment_id
 * @param int $doctor_id
 * @param int $patient_id
 * @param string $diagnosis
 * @param string $notes
 * @param array $items Array of items with keys: drug_name, dosage, frequency, duration, instructions
 * @return int|false Prescription ID or false on failure
 */
function create_prescription($appointment_id, $doctor_id, $patient_id, $diagnosis, $notes, $items) {
    try {
        $db = new Database();
        $conn = $db->getConnection();
        
        $conn->beginTransaction();

        // Insert prescription
        $stmt = $conn->prepare("
            INSERT INTO prescriptions (appointment_id, doctor_id, patient_id, diagnosis, notes, created_at)
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $appointment_id,
            $doctor_id,
            $patient_id,
            $diagnosis,
            $notes
        ]);
        
        $prescription_id = $conn->lastInsertId();

        // Insert items
        $stmtItem = $conn->prepare("
            INSERT INTO prescription_items (prescription_id, drug_name, dosage, frequency, duration, instructions)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($items as $item) {
            $stmtItem->execute([
                $prescription_id,
                $item['drug_name'],
                $item['dosage'] ?? '',
                $item['frequency'] ?? '',
                $item['duration'] ?? '',
                $item['instructions'] ?? ''
            ]);
        }

        $conn->commit();
        return $prescription_id;

    } catch (Exception $e) {
        if (isset($conn)) {
            $conn->rollBack();
        }
        error_log("Create prescription error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get prescriptions for a specific patient
 * 
 * @param int $patient_id
 * @return array
 */
function get_patient_prescriptions($patient_id) {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT p.*, d.full_name as doctor_name, s.name as specialty_name
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.user_id
            LEFT JOIN specialties s ON d.specialty_id = s.id
            WHERE p.patient_id = ?
            ORDER BY p.created_at DESC
        ");
        
        $stmt->execute([$patient_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Get patient prescriptions error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get prescriptions created by a specific doctor
 * 
 * @param int $doctor_id User ID of the doctor
 * @return array
 */
function get_doctor_prescriptions($doctor_id) {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("
            SELECT p.*, u.full_name as patient_name
            FROM prescriptions p
            JOIN users u ON p.patient_id = u.id
            WHERE p.doctor_id = ?
            ORDER BY p.created_at DESC
        ");
        
        $stmt->execute([$doctor_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (Exception $e) {
        error_log("Get doctor prescriptions error: " . $e->getMessage());
        return [];
    }
}

/**
 * Get details of a specific prescription
 * 
 * @param int $prescription_id
 * @return array|false
 */
function get_prescription_details($prescription_id) {
    try {
        $db = new Database();
        $conn = $db->getConnection();

        // Get main prescription info
        $stmt = $conn->prepare("
            SELECT p.*, 
                   d.full_name as doctor_name, 
                   u.full_name as patient_name,
                   s.name as specialty_name,
                   c.name as clinic_name
            FROM prescriptions p
            JOIN doctors d ON p.doctor_id = d.user_id
            JOIN users u ON p.patient_id = u.id
            LEFT JOIN specialties s ON d.specialty_id = s.id
            LEFT JOIN clinics c ON d.clinic_id = c.id
            WHERE p.id = ?
        ");
        
        $stmt->execute([$prescription_id]);
        $prescription = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$prescription) {
            return false;
        }

        // Get items
        $stmtItems = $conn->prepare("SELECT * FROM prescription_items WHERE prescription_id = ?");
        $stmtItems->execute([$prescription_id]);
        $items = $stmtItems->fetchAll(PDO::FETCH_ASSOC);

        $prescription['items'] = $items;
        return $prescription;

    } catch (Exception $e) {
        error_log("Get prescription details error: " . $e->getMessage());
        return false;
    }
}
