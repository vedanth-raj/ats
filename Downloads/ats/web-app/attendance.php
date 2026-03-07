<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Filter parameters
$date_filter = $_GET['date'] ?? date('d-m-Y');
$employee_filter = $_GET['employee'] ?? '';

// Build query
$conn = getDBConnection();
$query = "SELECT a.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name 
          FROM attendance a 
          LEFT JOIN employees e ON a.employee_id = e.employee_id 
          WHERE 1=1";

if (!empty($date_filter)) {
    $query .= " AND a._date = '" . $conn->real_escape_string($date_filter) . "'";
}

if (!empty($employee_filter)) {
    $query .= " AND a.employee_id LIKE '%" . $conn->real_escape_string($employee_filter) . "%'";
}

$query .= " ORDER BY a.auto_id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-clipboard-check"></i> Attendance Records</h1>
                <p>View and manage attendance</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Filter Attendance</h2>
                </div>
                <div class="card-body">
                    <form method="GET" class="filter-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label>Date</label>
                                <input type="text" name="date" value="<?php echo htmlspecialchars($date_filter); ?>" placeholder="dd-mm-yyyy">
                            </div>
                            <div class="form-group">
                                <label>Employee ID</label>
                                <input type="text" name="employee" value="<?php echo htmlspecialchars($employee_filter); ?>" placeholder="Search by ID">
                            </div>
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2>Attendance Records</h2>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee ID</th>
                                <th>Employee Name</th>
                                <th>Date</th>
                                <th>In Time</th>
                                <th>Out Time</th>
                                <th>Face Recognition (In)</th>
                                <th>Face Recognition (Out)</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $status = !empty($row['out_time']) ? 'Complete' : 'In Progress';
                                    $statusClass = !empty($row['out_time']) ? 'status-complete' : 'status-progress';
                                    $faceInStatus = $row['face_recognition_entering'] == 'True' ? 
                                        '<span class="status-badge status-complete">Verified</span>' : 
                                        '<span class="status-badge status-progress">Not Verified</span>';
                                    $faceOutStatus = $row['face_recognition_exiting'] == 'True' ? 
                                        '<span class="status-badge status-complete">Verified</span>' : 
                                        '<span class="status-badge status-progress">Not Verified</span>';
                                    
                                    echo "<tr>";
                                    echo "<td>" . $row['auto_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['employee_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['_date']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['in_time']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['out_time'] ?: '-') . "</td>";
                                    echo "<td>" . $faceInStatus . "</td>";
                                    echo "<td>" . ($row['out_time'] ? $faceOutStatus : '-') . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>$status</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No attendance records found</td></tr>";
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
.card-body {
    padding: 1.5rem;
}

.filter-form {
    width: 100%;
}

.form-row {
    display: flex;
    gap: 1rem;
    align-items: end;
}

.form-row .form-group {
    flex: 1;
}

@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }
}
</style>
