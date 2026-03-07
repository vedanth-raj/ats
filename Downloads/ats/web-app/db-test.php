<?php
$start = microtime(true);

echo "Testing database connection...<br><br>";

// Test with 127.0.0.1
$conn = new mysqli('127.0.0.1', 'root', 'my_sql', 'management_auto_attendance_system');

if ($conn->connect_error) {
    echo "❌ Connection failed: " . $conn->connect_error;
} else {
    $end = microtime(true);
    $time = round(($end - $start) * 1000, 2);
    
    echo "✅ Connected successfully!<br>";
    echo "⏱️ Connection time: {$time}ms<br><br>";
    
    // Test query
    $result = $conn->query("SELECT COUNT(*) as total FROM employees");
    $row = $result->fetch_assoc();
    echo "📊 Total employees: " . $row['total'] . "<br><br>";
    
    $conn->close();
    
    echo "<a href='login.php'>Go to Login Page</a>";
}
?>
