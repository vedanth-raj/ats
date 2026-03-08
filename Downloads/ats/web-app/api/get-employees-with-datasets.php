<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/json-database.php';

try {
    $db = new JsonDB('../data/');
    
    // Get all employees
    $employees = $db->query('employees');
    
    // Check which employees have datasets
    $employeesWithDatasets = [];
    $debugInfo = [];
    
    foreach ($employees as $employee) {
        // Use absolute path from document root
        $datasetPath = $_SERVER['DOCUMENT_ROOT'] . "/attendance-system/datasets/{$employee['employee_id']}";
        
        $debugInfo[] = [
            'employee_id' => $employee['employee_id'],
            'dataset_path' => $datasetPath,
            'exists' => is_dir($datasetPath)
        ];
        
        if (is_dir($datasetPath)) {
            $images = glob($datasetPath . "/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
            $imageCount = count($images);
            
            $debugInfo[count($debugInfo) - 1]['image_count'] = $imageCount;
            
            if ($imageCount > 0) {
                $employeesWithDatasets[] = [
                    'employee_id' => $employee['employee_id'],
                    'name' => $employee['first_name'] . ' ' . $employee['last_name'],
                    'dataset_count' => $imageCount,
                    'has_model' => $employee['is_model_available'] === 'True'
                ];
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'employees' => $employeesWithDatasets,
        'count' => count($employeesWithDatasets),
        'debug' => $debugInfo
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
