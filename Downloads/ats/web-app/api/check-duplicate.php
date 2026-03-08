<?php
/**
 * Check Duplicate Attendance API Endpoint
 * Checks if attendance already marked for employee on specified date
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/json-database.php';
require_once __DIR__ . '/../services/AttendanceService.php';

try {
    $employeeId = $_GET['employee_id'] ?? null;
    $date = $_GET['date'] ?? null;
    
    if (!$employeeId) {
        throw new Exception('employee_id parameter required');
    }
    
    $db = new JsonDB(__DIR__ . '/../data/');
    $attendanceService = new AttendanceService($db);
    
    $duplicate = $attendanceService->checkDuplicateToday($employeeId, $date);
    
    echo json_encode([
        'success' => true,
        'duplicate' => $duplicate
    ]);
    
} catch (Exception $e) {
    error_log('Error in check-duplicate.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'duplicate' => false
    ]);
}
?>
