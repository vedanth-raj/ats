<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/json-database.php';

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['employee_id']) || !isset($data['first_name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data'
    ]);
    exit;
}

try {
    $db = new JsonDB('../data/');
    
    // Check if employee ID already exists
    $employees = $db->query('employees');
    foreach ($employees as $emp) {
        if ($emp['employee_id'] === $data['employee_id']) {
            echo json_encode([
                'success' => false,
                'message' => 'Employee ID already exists'
            ]);
            exit;
        }
    }
    
    // Create employee record
    $employee = [
        'employee_id' => $data['employee_id'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'] ?? '',
        'email' => $data['email'] ?? '',
        'phone' => $data['phone'] ?? '',
        'department' => $data['department'] ?? 'General',
        'dob' => $data['dob'] ?? '',
        'gender' => $data['gender'] ?? '',
        'job_title' => $data['job_title'] ?? '',
        'nic' => $data['nic'] ?? '',
        'address' => $data['address'] ?? '',
        'marital_status' => $data['marital_status'] ?? '',
        'photo_path' => "photos/{$data['employee_id']}.jpg",
        'is_dataset_available' => $data['is_dataset_available'] ?? 'False',
        'is_model_available' => $data['is_model_available'] ?? 'False',
        'dataset_image_count' => $data['dataset_image_count'] ?? 0,
        'dataset_creation_date' => date('Y-m-d'),
        'model_training_date' => date('Y-m-d'),
        'auto_id' => count($employees) + 1,
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s')
    ];
    
    // Add to employees array
    $employees[] = $employee;
    
    // Save to database
    $db->update('employees', [], $employees);
    
    echo json_encode([
        'success' => true,
        'message' => 'Employee created successfully',
        'employee' => $employee
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
