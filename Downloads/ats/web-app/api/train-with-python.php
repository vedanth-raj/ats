<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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
    $employeeId = $data['employee_id'];
    
    // Paths - dynamically determine project root
    $webRoot = $_SERVER['DOCUMENT_ROOT'] . "/attendance-system";
    // Get project root from current file location (go up 2 levels from api/)
    $projectRoot = dirname(dirname(__DIR__));
    $pythonScript = $projectRoot . "/2-python-scripts/training.py";
    $datasetsPath = $webRoot . "/datasets";
    $modelsPath = $webRoot . "/models";
    
    // Check if Python script exists
    if (!file_exists($pythonScript)) {
        echo json_encode([
            'success' => false,
            'message' => 'Python training script not found at: ' . $pythonScript
        ]);
        exit;
    }
    
    // Check if dataset exists
    $datasetDir = $datasetsPath . "/{$employeeId}";
    if (!is_dir($datasetDir)) {
        echo json_encode([
            'success' => false,
            'message' => 'Dataset not found for employee ' . $employeeId
        ]);
        exit;
    }
    
    // Count images
    $images = glob($datasetDir . "/*.{jpg,jpeg,png}", GLOB_BRACE);
    $imageCount = count($images);
    
    if ($imageCount < 10) {
        echo json_encode([
            'success' => false,
            'message' => "Insufficient images. Found {$imageCount}, need at least 10"
        ]);
        exit;
    }
    
    // Change to python scripts directory
    chdir($projectRoot . "/2-python-scripts");
    
    // Create Datasets and Trained_Models directories if they don't exist
    if (!is_dir("Datasets")) {
        mkdir("Datasets", 0777, true);
    }
    if (!is_dir("Trained_Models")) {
        mkdir("Trained_Models", 0777, true);
    }
    
    // Copy dataset to python scripts directory
    $pythonDatasetDir = "Datasets/{$employeeId}";
    if (!is_dir($pythonDatasetDir)) {
        mkdir($pythonDatasetDir, 0777, true);
    }
    
    // Copy images
    foreach ($images as $image) {
        $filename = basename($image);
        copy($image, "{$pythonDatasetDir}/{$filename}");
    }
    
    // Run Python training script
    $command = "python training.py -e {$employeeId} 2>&1";
    $output = [];
    $returnCode = 0;
    
    exec($command, $output, $returnCode);
    
    if ($returnCode !== 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Python training failed',
            'output' => implode("\n", $output),
            'return_code' => $returnCode
        ]);
        exit;
    }
    
    // Check if model was created
    $pickleModel = "Trained_Models/{$employeeId}/{$employeeId}_(Model).pickle";
    if (!file_exists($pickleModel)) {
        echo json_encode([
            'success' => false,
            'message' => 'Model file not created',
            'output' => implode("\n", $output)
        ]);
        exit;
    }
    
    // Convert pickle to JSON using Python
    $convertScript = $projectRoot . "/web-app/tools/convert-pickle-to-json.py";
    chdir($projectRoot);
    
    $convertCommand = "python web-app/tools/convert-pickle-to-json.py 2>&1";
    $convertOutput = [];
    $convertReturnCode = 0;
    
    exec($convertCommand, $convertOutput, $convertReturnCode);
    
    // Copy model to web-app models directory
    $webModelDir = $modelsPath . "/{$employeeId}";
    if (!is_dir($webModelDir)) {
        mkdir($webModelDir, 0777, true);
    }
    
    $jsonModel = $projectRoot . "/web-app/models/{$employeeId}/model.json";
    if (file_exists($jsonModel)) {
        copy($jsonModel, $webModelDir . "/model.json");
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Model trained successfully',
        'image_count' => $imageCount,
        'training_output' => implode("\n", $output),
        'conversion_output' => implode("\n", $convertOutput)
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}
?>
