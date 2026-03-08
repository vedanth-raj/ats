<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['employee_id']) || !isset($data['images'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data'
    ]);
    exit;
}

try {
    $employeeId = $data['employee_id'];
    $images = $data['images'];
    
    // Create dataset directory
    $datasetDir = $_SERVER['DOCUMENT_ROOT'] . "/attendance-system/datasets/{$employeeId}";
    if (!is_dir($datasetDir)) {
        mkdir($datasetDir, 0777, true);
    }
    
    // Save each image
    $savedCount = 0;
    foreach ($images as $index => $imageData) {
        // Remove data:image/jpeg;base64, prefix
        $imageData = preg_replace('/^data:image\/\w+;base64,/', '', $imageData);
        $imageData = base64_decode($imageData);
        
        if ($imageData === false) {
            continue;
        }
        
        $filename = sprintf("%s/image_%03d.jpg", $datasetDir, $index + 1);
        if (file_put_contents($filename, $imageData)) {
            $savedCount++;
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => "Saved {$savedCount} images",
        'saved_count' => $savedCount
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
