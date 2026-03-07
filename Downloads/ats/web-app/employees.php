<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn = getDBConnection();
    $conn->query("DELETE FROM employees WHERE auto_id = $id");
    closeDBConnection($conn);
    header('Location: employees.php?msg=deleted');
    exit();
}

// Get all employees
$conn = getDBConnection();
$result = $conn->query("SELECT * FROM employees ORDER BY auto_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-users"></i> Employee Management</h1>
                <p>Manage employee records</p>
            </div>

            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php 
                    if ($_GET['msg'] == 'deleted') echo 'Employee deleted successfully';
                    if ($_GET['msg'] == 'added') echo 'Employee added successfully';
                    if ($_GET['msg'] == 'updated') echo 'Employee updated successfully';
                    ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-header">
                    <h2>All Employees</h2>
                    <a href="employee_add.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add New Employee
                    </a>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee ID</th>
                                <th>Name</th>
                                <th>Job Title</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th>Dataset</th>
                                <th>Model</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $fullName = htmlspecialchars($row['first_name'] . ' ' . $row['last_name']);
                                    $datasetStatus = $row['is_dataset_available'] == 'True' ? 
                                        '<span class="status-badge status-complete">Yes</span>' : 
                                        '<span class="status-badge status-progress">No</span>';
                                    $modelStatus = $row['is_model_available'] == 'True' ? 
                                        '<span class="status-badge status-complete">Yes</span>' : 
                                        '<span class="status-badge status-progress">No</span>';
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['auto_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                    echo "<td>" . $fullName . "</td>";
                                    echo "<td>" . htmlspecialchars($row['job_title']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['phone_no']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['email_address']) . "</td>";
                                    echo "<td>" . $datasetStatus . "</td>";
                                    echo "<td>" . $modelStatus . "</td>";
                                    echo "<td class='action-buttons'>";
                                    echo "<a href='employee_edit.php?id=" . $row['auto_id'] . "' class='btn btn-sm btn-primary'><i class='fas fa-edit'></i></a> ";
                                    echo "<a href='employees.php?delete=" . $row['auto_id'] . "' class='btn btn-sm btn-danger' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i></a>";
                                    echo "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No employees found</td></tr>";
                            }
                            
                            closeDBConnection($conn);
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>

<style>
.card {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
}

.card-header {
    padding: 1.5rem;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h2 {
    margin: 0;
    color: #2c3e50;
}

.action-buttons {
    display: flex;
    gap: 0.5rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}
</style>
