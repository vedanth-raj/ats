<aside class="sidebar" id="sidebar">
    <nav class="sidebar-nav">
        <a href="index-fast.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'index-fast.php' ? 'active' : ''; ?>">
            📊 <span>Dashboard</span>
        </a>
        <a href="employees-fast.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'employees-fast.php' ? 'active' : ''; ?>">
            👥 <span>Employees</span>
        </a>
        <a href="attendance-fast.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'attendance-fast.php' ? 'active' : ''; ?>">
            📋 <span>Attendance</span>
        </a>
        <a href="reports.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : ''; ?>">
            📊 <span>Reports</span>
        </a>
        <a href="datasets.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'datasets.php' ? 'active' : ''; ?>">
            💾 <span>Datasets</span>
        </a>
        <a href="training.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'training.php' ? 'active' : ''; ?>">
            🧠 <span>Training</span>
        </a>
        <a href="settings.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>">
            ⚙️ <span>Settings</span>
        </a>
    </nav>
</aside>
