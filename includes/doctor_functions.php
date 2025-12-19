<?php
require_once __DIR__ . '/../config/database.php';

if (!function_exists('get_doctor_data')) {
    /**
     * Get doctor data by User ID.
     * This is the bridge between User table and Doctors table.
     */
    function get_doctor_data($user_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("SELECT * FROM doctors WHERE user_id = ?");
            $stmt->execute([$user_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return false;
        }
    }
}

if (!function_exists('get_upcoming_appointments')) {
    function get_upcoming_appointments($doctor_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            // Assumes doctor_id passed is the DOCTOR table ID
            $stmt = $conn->prepare("
                SELECT a.*, u.full_name as patient_name 
                FROM appointments a 
                JOIN users u ON a.user_id = u.id 
                WHERE a.doctor_id = ? 
                AND a.appointment_date >= CURDATE() 
                AND a.status IN ('confirmed', 'pending')
                ORDER BY a.appointment_date ASC, a.appointment_time ASC
                LIMIT 10
            ");
            $stmt->execute([$doctor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('get_doctor_stats')) {
    function get_doctor_stats($doctor_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            // Assumes doctor_id passed is the DOCTOR table ID
            
            $stats = [
                'total_appointments' => 0,
                'today_appointments' => 0,
                'avg_rating' => 0,
                'total_patients' => 0
            ];
            
            // Total Appointments
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ?");
            $stmt->execute([$doctor_id]);
            $stats['total_appointments'] = $stmt->fetchColumn();
            
            // Today Appointments
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE doctor_id = ? AND appointment_date = CURDATE()");
            $stmt->execute([$doctor_id]);
            $stats['today_appointments'] = $stmt->fetchColumn();
            
            // Avg Rating
            $stmt = $conn->prepare("SELECT rating FROM doctors WHERE id = ?");
            $stmt->execute([$doctor_id]);
            $stats['avg_rating'] = $stmt->fetchColumn() ?: 0;
            
            // Total Patients
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) FROM appointments WHERE doctor_id = ?");
            $stmt->execute([$doctor_id]);
            $stats['total_patients'] = $stmt->fetchColumn();
            
            return $stats;
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('get_working_hours')) {
    function get_working_hours($doctor_id) {
        try {
            $db = new Database();
            $conn = $db->getConnection();
            $stmt = $conn->prepare("
                SELECT day_of_week as day_name, start_time, end_time
                FROM working_hours
                WHERE doctor_id = ? AND is_available = 1
                ORDER BY FIELD(day_of_week, 'saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday')
            ");
            $stmt->execute([$doctor_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
// Removed get_doctor_reviews as it exists in functions.php
?>
