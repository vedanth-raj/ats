<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Get JSON input
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['employee_id']) || !isset($data['descriptor'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid input data'
    ]);
    exit;
}

try {
    $employeeId = $data['employee_id'];
    $descriptor = $data['descriptor'];
    $numSamples = $data['num_samples'] ?? 0;
    $allDescriptors = $data['all_descriptors'] ?? [];
    
    // Create model directory
    $modelDir = $_SERVER['DOCUMENT_ROOT'] . "/attendance-system/models/{$employeeId}";
    if (!is_dir($modelDir)) {
        mkdir($modelDir, 0777, true);
    }
    
    // Prepare model data
    $modelData = [
        'employee_id' => $employeeId,
        'descriptor' => $descriptor,
        'num_samples' => $numSamples,
        'descriptor_length' => count($descriptor),
        'all_descriptors' => $allDescriptors,
        'trained_at' => date('Y-m-d H:i:s')
    ];
    
    // Save model as JSON
    $modelPath = "{$modelDir}/model.json";
    $result = file_put_contents($modelPath, json_encode($modelData, JSON_PRETTY_PRINT));
    
    if ($result === false) {
        throw new Exception('Failed to save model file');
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Model saved successfully',
        'model_path' => $modelPath,
        'num_samples' => $numSamples
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
