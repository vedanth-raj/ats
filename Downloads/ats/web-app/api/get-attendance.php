<?php
/**
 * Get Attendance API Endpoint
 * Returns attendance records based on type (today, date range, etc.)
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/json-database.php';
require_once __DIR__ . '/../services/AttendanceService.php';

try {
    $db = new JsonDB(__DIR__ . '/../data/');
    $attendanceService = new AttendanceService($db);
    
    $type = $_GET['type'] ?? 'today';
    
    switch ($type) {
        case 'today':
            $records = $attendanceService->getTodayAttendance();
            break;
            
        case 'date':
            $date = $_GET['date'] ?? null;
            if (!$date) {
                throw new Exception('Date parameter required');
            }
            $records = $attendanceService->getAttendanceByDate($date);
            break;
            
        case 'range':
            $startDate = $_GET['start_date'] ?? null;
            $endDate = $_GET['end_date'] ?? null;
            if (!$startDate || !$endDate) {
                throw new Exception('start_date and end_date parameters required');
            }
            $records = $attendanceService->getAttendanceByDateRange($startDate, $endDate);
            break;
            
        default:
            throw new Exception('Invalid type parameter');
    }
    
    echo json_encode([
        'success' => true,
        'records' => $records,
        'count' => count($records)
    ]);
    
} catch (Exception $e) {
    error_log('Error in get-attendance.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'records' => []
    ]);
}
?>
