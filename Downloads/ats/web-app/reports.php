<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-chart-bar"></i> Reports</h1>
                <p>Generate attendance reports</p>
            </div>

            <div class="action-grid">
                <div class="action-card">
                    <i class="fas fa-calendar-day"></i>
                    <span>Daily Report</span>
                    <p class="text-muted">View today's attendance</p>
                </div>
                <div class="action-card">
                    <i class="fas fa-calendar-week"></i>
                    <span>Weekly Report</span>
                    <p class="text-muted">View this week's attendance</p>
                </div>
                <div class="action-card">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Monthly Report</span>
                    <p class="text-muted">View this month's attendance</p>
                </div>
                <div class="action-card">
                    <i class="fas fa-file-excel"></i>
                    <span>Export to Excel</span>
                    <p class="text-muted">Download attendance data</p>
                </div>
            </div>
        </main>
    </div>

    <script src="assets/js/script.js"></script>
</body>
</html>
