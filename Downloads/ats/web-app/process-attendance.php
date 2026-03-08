<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

require_once 'config/json-database.php';
$db = new JsonDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    if ($action == 'mark_attendance') {
        $employee_id = $_POST['employee_id'] ?? '';
        
        if (empty($employee_id)) {
            echo json_encode(['success' => false, 'message' => 'Employee ID required']);
            exit();
        }
        
        // Get employee details
        $employees = $db->query('employees');
        $employee_found = false;
        $employee_name = '';
        
        foreach ($employees as $emp) {
            if ($emp['employee_id'] == $employee_id) {
                $employee_found = true;
                $employee_name = $emp['first_name'] . ' ' . $emp['last_name'];
                break;
            }
        }
        
        if (!$employee_found) {
            echo json_encode(['success' => false, 'message' => 'Employee not found']);
            exit();
        }
        
        // Check if already marked today
        $today = date('d-m-Y');
        $attendance = $db->query('attendance');
        $already_marked = false;
        
        foreach ($attendance as $record) {
            if ($record['employee_id'] == $employee_id && $record['_date'] == $today) {
                $already_marked = true;
                break;
            }
        }
        
        if ($already_marked) {
            echo json_encode(['success' => false, 'message' => 'Attendance already marked for today']);
            exit();
        }
        
        // Mark attendance
        $now = new DateTime();
        $currentTime = $now->format('h:i:s A');
        
        $db->insert('attendance', [
            'employee_id' => $employee_id,
            'in_time' => $currentTime,
            'out_time' => '',
            '_date' => $today,
            'face_recognition_entering' => 'True',
            'face_recognition_exiting' => '',
            'face_recognition_entering_img_path' => '',
            'face_recognition_exiting_img_path' => ''
        ]);
        
        echo json_encode([
            'success' => true, 
            'message' => "Attendance marked for $employee_name at $currentTime"
        ]);
        exit();
    }
}

echo json_encode(['success' => false, 'message' => 'Invalid request']);
?>
