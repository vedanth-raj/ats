<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$employeeId = $_GET['employee_id'] ?? '';

if (empty($employeeId)) {
    echo json_encode([
        'success' => false,
        'message' => 'Employee ID is required'
    ]);
    exit;
}

try {
    $datasetPath = $_SERVER['DOCUMENT_ROOT'] . "/attendance-system/datasets/{$employeeId}";
    
    if (!is_dir($datasetPath)) {
        echo json_encode([
            'success' => false,
            'message' => 'Dataset not found',
            'path' => $datasetPath
        ]);
        exit;
    }
    
    // Get all image files
    $images = glob($datasetPath . "/*.{jpg,jpeg,png,JPG,JPEG,PNG}", GLOB_BRACE);
    
    // Convert to relative URLs
    $imageUrls = array_map(function($path) use ($employeeId) {
        $filename = basename($path);
        return "/attendance-system/datasets/{$employeeId}/{$filename}";
    }, $images);
    
    echo json_encode([
        'success' => true,
        'images' => array_values($imageUrls),
        'count' => count($imageUrls)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
