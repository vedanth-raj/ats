<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

$conn = getDBConnection();
$result = $conn->query("SELECT * FROM training ORDER BY auto_id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Training - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="main-content">
            <div class="page-header">
                <h1><i class="fas fa-brain"></i> Model Training</h1>
                <p>Face recognition model training history</p>
            </div>

            <div class="card">
                <div class="card-header">
                    <h2>Training History</h2>
                </div>
                
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Employee ID</th>
                                <th>Full Name</th>
                                <th>Trained Images</th>
                                <th>Trained Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . $row['auto_id'] . "</td>";
                                    echo "<td>" . htmlspecialchars($row['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['number_of_trained_images']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['trained_date']) . "</td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center'>No training records found</td></tr>";
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
