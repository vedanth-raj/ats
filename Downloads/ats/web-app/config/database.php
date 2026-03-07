<?php
// Database configuration
define('DB_HOST', '127.0.0.1');  // Use IP instead of localhost
define('DB_USER', 'root');
define('DB_PASS', 'my_sql');
define('DB_NAME', 'management_auto_attendance_system');

// Create database connection
function getDBConnection() {
    // Disable DNS lookup by using IP
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    try {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $conn->options(MYSQLI_OPT_CONNECT_TIMEOUT, 5);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        return $conn;
    } catch (Exception $e) {
        die("Database connection error: " . $e->getMessage());
    }
}

// Close database connection
function closeDBConnection($conn) {
    if ($conn) {
        $conn->close();
    }
}
?>
