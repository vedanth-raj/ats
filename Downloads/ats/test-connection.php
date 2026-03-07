<?php
// Test database connection
$host = 'localhost';
$user = 'root';
$pass = 'my_sql';
$db = 'management_auto_attendance_system';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo "❌ Database Connection FAILED: " . $conn->connect_error;
} else {
    echo "✅ Database Connection SUCCESSFUL!\n\n";
    
    // Test queries
    $result = $conn->query("SELECT COUNT(*) as total FROM employees");
    $employees = $result->fetch_assoc()['total'];
    echo "Total Employees: $employees\n";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM attendance");
    $attendance = $result->fetch_assoc()['total'];
    echo "Total Attendance Records: $attendance\n";
    
    $result = $conn->query("SELECT COUNT(*) as total FROM users");
    $users = $result->fetch_assoc()['total'];
    echo "Total Users: $users\n";
    
    echo "\n✅ All database tables are accessible!";
}

$conn->close();
?>
