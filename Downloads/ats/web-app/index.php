<?php
session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Get statistics
$conn = getDBConnection();

// Total employees
$result = $conn->query("SELECT COUNT(*) as total FROM employees");
$totalEmployees = $result->fetch_assoc()['total'];

// Today's attendance
$today = date('d-m-Y');
$result = $conn->query("SELECT COUNT(*) as total FROM attendance WHERE _date = '$today'");
$todayAttendance = $result->fetch_assoc()['total'];

// Employees with datasets
$result = $conn->query("SELECT COUNT(*) as total FROM employees WHERE is_dataset_available = 'True'");
$withDatasets = $result->fetch_assoc()['total'];

// Employees with trained models
$result = $conn->query("SELECT COUNT(*) as total FROM employees WHERE is_model_available = 'True'");
$withModels = $result->fetch_assoc()['total'];

closeDBConnection($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Management Auto Attendance System - Dashboard</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $totalEmployees; ?></h3>
                        <p>Total Employees</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $todayAttendance; ?></h3>
                        <p>Today's Attendance</p>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $withDatasets; ?></h3>
                        <p>With Datasets</p>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">
                        <i class="fas fa-brain"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $withModels; ?></h3>
                        <p>Trained Models</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                <div class="action-grid">
                    <a href="employees.php" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <span>Manage Employees</span>
                    </a>
                    <a href="attendance.php" class="action-card">
                        <i class="fas fa-clipboard-check"></i>
                        <span>View Attendance</span>
                    </a>
                    <a href="reports.php" class="action-card">
                        <i class="fas fa-chart-bar"></i>
                        <span>Generate Reports</span>
                    </a>
                    <a href="settings.php" class="action-card">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </div>
            </div>

            <div class="recent-activity">
                <h2><i class="fas fa-history"></i> Recent Attendance</h2>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Date</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $conn = getDBConnection();
                            $result = $conn->query("SELECT * FROM attendance ORDER BY auto_id DESC LIMIT 10");
                            
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $status = !empty($row['out_time']) ? 'Complete' : 'In Progress';
                                    $statusClass = !empty($row['out_time']) ? 'status-complete' : 'status-progress';
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['in_time']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['out_time'] ?: '-') . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>$status</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No attendance records found</td></tr>";
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
