<?php
/**
 * Database Initialization Script
 * Creates directory structure and initializes JSON database files
 */

require_once 'config/json-database.php';

// Create directory structure
$directories = [
    'data',
    'datasets',
    'models',
    'models/face-api',
    'photos',
    'logs'
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0777, true);
        echo "Created directory: {$dir}\n";
    }
}

// Initialize database
$db = new JsonDB('data/');

// Initialize users table
$users = $db->query('users');
if (empty($users)) {
    $db->insert('users', [
        'username' => 'admin',
        'password' => password_hash('kuna123', PASSWORD_BCRYPT),
        'role' => 'admin',
        'email' => 'admin@company.com'
    ]);
    echo "Initialized users table with admin user\n";
}

// Initialize employees table
$employees = $db->query('employees');
if (empty($employees)) {
    $db->insert('employees', [
        'employee_id' => '0001',
        'first_name' => 'Gunarakulan',
        'last_name' => 'Gunaretnam',
        'email' => 'guna@company.com',
        'phone' => '12435789',
        'department' => 'Engineering',
        'dob' => '1997-01-11',
        'gender' => 'Male',
        'job_title' => 'Software Developer',
        'nic' => '970110720V',
        'address' => '345768',
        'marital_status' => 'Single',
        'photo_path' => 'photos/0001.jpg',
        'is_dataset_available' => 'False',
        'is_model_available' => 'False',
        'dataset_image_count' => 0,
        'dataset_creation_date' => null,
        'model_training_date' => null
    ]);
    
    $db->insert('employees', [
        'employee_id' => '0002',
        'first_name' => 'David',
        'last_name' => 'Mike',
        'email' => 'david@company.com',
        'phone' => '456543542132',
        'department' => 'Management',
        'dob' => '1990-05-15',
        'gender' => 'Male',
        'job_title' => 'Manager',
        'nic' => '3245476453',
        'address' => '56543542132',
        'marital_status' => 'Single',
        'photo_path' => 'photos/0002.jpg',
        'is_dataset_available' => 'False',
        'is_model_available' => 'False',
        'dataset_image_count' => 0,
        'dataset_creation_date' => null,
        'model_training_date' => null
    ]);
    
    echo "Initialized employees table with sample data\n";
}

// Initialize attendance table
$attendance = $db->query('attendance');
if (empty($attendance)) {
    // Empty table, will be populated when attendance is marked
    file_put_contents('data/attendance.json', json_encode([], JSON_PRETTY_PRINT));
    echo "Initialized empty attendance table\n";
}

// Initialize datasets table
$datasets = $db->query('datasets');
if (empty($datasets)) {
    file_put_contents('data/datasets.json', json_encode([], JSON_PRETTY_PRINT));
    echo "Initialized empty datasets table\n";
}

// Initialize training table
$training = $db->query('training');
if (empty($training)) {
    file_put_contents('data/training.json', json_encode([], JSON_PRETTY_PRINT));
    echo "Initialized empty training table\n";
}

// Initialize settings table
$settings = $db->query('settings');
if (empty($settings)) {
    $defaultSettings = [
        'auto_id' => 1,
        'working_hours_start' => '09:00:00',
        'working_hours_end' => '17:00:00',
        'late_arrival_threshold_minutes' => 15,
        'face_recognition_confidence_threshold' => 0.75,
        'dataset_image_count' => 50,
        'dataset_capture_interval_ms' => 200,
        'session_timeout_hours' => 8,
        'face_detection_fps' => 10,
        'image_width' => 160,
        'image_height' => 160
    ];
    file_put_contents('data/settings.json', json_encode([$defaultSettings], JSON_PRETTY_PRINT));
    echo "Initialized settings table with default values\n";
}

echo "\nDatabase initialization complete!\n";
echo "Admin credentials: username=admin, password=kuna123\n";
?>
