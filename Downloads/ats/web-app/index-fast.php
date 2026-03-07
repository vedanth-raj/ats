<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login-fast.php');
    exit();
}

require_once 'config/json-database.php';
$db = new JsonDB();

$totalEmployees = $db->count('employees');
$todayAttendance = $db->count('attendance', ['_date' => date('d-m-Y')]);
$withDatasets = $db->count('employees', ['is_dataset_available' => 'True']);
$withModels = $db->count('employees', ['is_model_available' => 'True']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar-fast.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1>📊 Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>

            <div class="alert" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 2rem;">
                ⚡ <strong>Fast Mode Active:</strong> Using JSON file database for instant loading. All features work perfectly!
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $totalEmployees; ?></h3>
                        <p>Total Employees</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $todayAttendance; ?></h3>
                        <p>Today's Attendance</p>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">💾</div>
                    <div class="stat-details">
                        <h3><?php echo $withDatasets; ?></h3>
                        <p>With Datasets</p>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">🧠</div>
                    <div class="stat-details">
                        <h3><?php echo $withModels; ?></h3>
                        <p>Trained Models</p>
                    </div>
                </div>
            </div>

            <div class="quick-actions">
                <h2>⚡ Quick Actions</h2>
                <div class="action-grid">
                    <a href="employees-fast.php" class="action-card">
                        <span style="font-size: 3rem;">👥</span>
                        <span>Manage Employees</span>
                    </a>
                    <a href="attendance-fast.php" class="action-card">
                        <span style="font-size: 3rem;">📋</span>
                        <span>View Attendance</span>
                    </a>
                    <a href="reports.php" class="action-card">
                        <span style="font-size: 3rem;">📊</span>
                        <span>Generate Reports</span>
                    </a>
                    <a href="settings.php" class="action-card">
                        <span style="font-size: 3rem;">⚙️</span>
                        <span>Settings</span>
                    </a>
                </div>
            </div>

            <div class="recent-activity">
                <h2>📋 Recent Attendance</h2>
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
                            $attendance = $db->query('attendance');
                            $attendance = array_reverse($attendance);
                            $attendance = array_slice($attendance, 0, 10);
                            
                            if (!empty($attendance)) {
                                foreach($attendance as $row) {
                                    $status = !empty($row['out_time']) ? 'Complete' : 'In Progress';
                                    $statusClass = !empty($row['out_time']) ? 'status-complete' : 'status-progress';
                                    
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['in_time']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['out_time'] ?? '-') . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>$status</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No attendance records found</td></tr>";
                            }
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
