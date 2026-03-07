<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login-static.php');
    exit();
}
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
    <header class="header">
        <div class="header-left">
            <h2 class="header-title">Management Auto Attendance System</h2>
        </div>
        <div class="header-right">
            <div class="user-menu">
                <span>👤 <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="logout.php" class="btn btn-sm btn-danger">Logout</a>
            </div>
        </div>
    </header>
    
    <div class="container">
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <a href="index-static.php" class="nav-item active">📊 Dashboard</a>
                <a href="#" class="nav-item">👥 Employees</a>
                <a href="#" class="nav-item">📋 Attendance</a>
                <a href="#" class="nav-item">📈 Reports</a>
                <a href="#" class="nav-item">💾 Datasets</a>
                <a href="#" class="nav-item">🧠 Training</a>
                <a href="#" class="nav-item">⚙️ Settings</a>
            </nav>
        </aside>
        
        <main class="main-content">
            <div class="page-header">
                <h1>📊 Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</p>
            </div>

            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3>2</h3>
                        <p>Total Employees</p>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3>1</h3>
                        <p>Today's Attendance</p>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-icon">💾</div>
                    <div class="stat-details">
                        <h3>1</h3>
                        <p>With Datasets</p>
                    </div>
                </div>

                <div class="stat-card purple">
                    <div class="stat-icon">🧠</div>
                    <div class="stat-details">
                        <h3>1</h3>
                        <p>Trained Models</p>
                    </div>
                </div>
            </div>

            <div class="alert" style="background: #fff3cd; color: #856404; padding: 1rem; border-radius: 5px; margin: 2rem 0;">
                ⚡ <strong>Fast Mode Active:</strong> This is a static demo version without database connection for instant loading. 
                To enable full database features, we need to fix the MySQL connection timeout issue.
            </div>

            <div class="quick-actions">
                <h2>⚡ Quick Actions</h2>
                <div class="action-grid">
                    <div class="action-card">
                        <span style="font-size: 3rem;">👥</span>
                        <span>Manage Employees</span>
                    </div>
                    <div class="action-card">
                        <span style="font-size: 3rem;">📋</span>
                        <span>View Attendance</span>
                    </div>
                    <div class="action-card">
                        <span style="font-size: 3rem;">📊</span>
                        <span>Generate Reports</span>
                    </div>
                    <div class="action-card">
                        <span style="font-size: 3rem;">⚙️</span>
                        <span>Settings</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
