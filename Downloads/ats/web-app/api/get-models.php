<?php
/**
 * Get Models API Endpoint
 * Returns list of available face recognition models
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../config/json-database.php';

try {
    $db = new JsonDB(__DIR__ . '/../data/');
    
    // Get all employees with trained models
    $employees = $db->query('employees');
    $models = [];
    
    foreach ($employees as $employee) {
        if ($employee['is_model_available'] === 'True') {
            $modelPath = '/attendance-system/models/' . $employee['employee_id'] . '/model.json';
            
            // Check if model file exists
            $modelFile = __DIR__ . '/../models/' . $employee['employee_id'] . '/model.json';
            if (file_exists($modelFile)) {
                $models[] = [
                    'employee_id' => $employee['employee_id'],
                    'employee_name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'path' => $modelPath,
                    'training_date' => $employee['model_training_date'] ?? null
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'models' => $models,
        'count' => count($models)
    ]);
    
} catch (Exception $e) {
    error_log('Error in get-models.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'models' => []
    ]);
}
?>
