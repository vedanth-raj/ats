<?php
/**
 * Create Sample Face Models for Testing
 * Generates random face descriptors for testing the face recognition system
 */

require_once __DIR__ . '/../config/json-database.php';

// Initialize database
$db = new JsonDB(__DIR__ . '/../data/');

// Get employees
$employees = $db->query('employees');

echo "Creating sample face models for testing...\n\n";

foreach ($employees as $employee) {
    $employeeId = $employee['employee_id'];
    $employeeName = $employee['first_name'] . ' ' . $employee['last_name'];
    
    echo "Creating model for {$employeeName} ({$employeeId})...\n";
    
    // Create model directory
    $modelDir = __DIR__ . '/../models/' . $employeeId;
    if (!is_dir($modelDir)) {
        mkdir($modelDir, 0777, true);
    }
    
    // Generate random face descriptor (128 dimensions)
    // In production, this would be computed from actual face images
    $descriptor = [];
    for ($i = 0; $i < 128; $i++) {
        // Random values between -1 and 1 (typical for face descriptors)
        $descriptor[] = (mt_rand(-1000, 1000) / 1000);
    }
    
    // Create model data
    $modelData = [
        'employee_id' => $employeeId,
        'descriptor' => $descriptor,
        'descriptor_length' => 128,
        'training_date' => date('Y-m-d'),
        'image_count' => 50,
        'version' => '1.0',
        'note' => 'Sample model for testing - replace with real trained model'
    ];
    
    // Save model as JSON
    $modelFile = $modelDir . '/model.json';
    file_put_contents($modelFile, json_encode($modelData, JSON_PRETTY_PRINT));
    
    // Update employee record
    $db->update('employees', $employee['auto_id'], [
        'is_model_available' => 'True',
        'model_training_date' => date('Y-m-d')
    ]);
    
    echo "  ✓ Model created: {$modelFile}\n";
    echo "  ✓ Employee record updated\n\n";
}

echo "Sample models created successfully!\n";
echo "\nNote: These are random descriptors for testing only.\n";
echo "For production, train real models using actual face images.\n";
?>
