<?php
/**
 * Process Attendance API Endpoint
 * Marks attendance for an employee with validation
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/json-database.php';
require_once __DIR__ . '/../services/AttendanceService.php';

// Set timeout to 5 seconds
set_time_limit(5);

try {
    // Check request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }
    
    // Get employee_id from POST data
    $employeeId = $_POST['employee_id'] ?? null;
    
    if (empty($employeeId)) {
        throw new Exception('Employee ID is required');
    }
    
    // Initialize services
    $db = new JsonDB(__DIR__ . '/../data/');
    $attendanceService = new AttendanceService($db);
    
    // Mark attendance
    $result = $attendanceService->markAttendance($employeeId);
    
    // Return result
    echo json_encode($result);
    
} catch (Exception $e) {
    error_log('Error in process-attendance.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
