<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <a href="index.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="employees.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'employees.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Employees</span>
        </a>
        <a href="attendance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance.php' ? 'active' : ''; ?>">
            <i class="fas fa-clipboard-check"></i>
            <span>Attendance</span>
        </a>
        <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i>
            <span>Reports</span>
        </a>
        <a href="datasets.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'datasets.php' ? 'active' : ''; ?>">
            <i class="fas fa-database"></i>
            <span>Datasets</span>
        </a>
        <a href="training.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'training.php' ? 'active' : ''; ?>">
            <i class="fas fa-brain"></i>
            <span>Training</span>
        </a>
        <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Settings</span>
        </a>
    </nav>
</aside>
