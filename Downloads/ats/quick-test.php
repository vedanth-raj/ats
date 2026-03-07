<?php
$start = microtime(true);
$conn = @new mysqli('127.0.0.1', 'root', 'my_sql', 'management_auto_attendance_system');
$end = microtime(true);

if ($conn->connect_error) {
    echo "Error: " . $conn->connect_error . "\n";
} else {
    echo "Success! Connected in " . round(($end - $start) * 1000) . "ms\n";
    $conn->close();
}
?>
