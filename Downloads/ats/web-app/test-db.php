<?php
require_once 'config/json-database.php';

$db = new JsonDB();
$employees = $db->query('employees');

echo "Number of employees: " . count($employees) . "\n\n";
echo "Employees:\n";
print_r($employees);
?>
