<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/json-database.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['employee_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Employee ID is required'
    ]);
    exit;
}

try {
    $db = new JsonDB();
    $employeeId = $data['employee_id'];
    $isModelAvailable = $data['is_model_available'] ?? 'True';
    
    // Get all employees
    $employees = $db->query('employees.json', []);
    
    // Find and update the employee
    $updated = false;
    foreach ($employees as &$employee) {
        if ($employee['employee_id'] === $employeeId) {
            $employee['is_model_available'] = $isModelAvailable;
            $updated = true;
            break;
        }
    }
    
    if (!$updated) {
        echo json_encode([
            'success' => false,
            'message' => 'Employee not found'
        ]);
        exit;
    }
    
    // Save updated employees
    $db->update('employees.json', [], $employees);
    
    echo json_encode([
        'success' => true,
        'message' => 'Employee model status updated'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
