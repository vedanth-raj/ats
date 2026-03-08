<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login-fast.php');
    exit();
}

require_once 'config/json-database.php';
$db = new JsonDB();

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['employee_id'])) {
    $employee_id = $_POST['employee_id'];
    $type = $_POST['type'] ?? 'in';
    
    $employees = $db->query('employees');
    $employee_found = false;
    
    foreach ($employees as $emp) {
        if ($emp['employee_id'] == $employee_id) {
            $employee_found = true;
            $employee_name = $emp['first_name'] . ' ' . $emp['last_name'];
            break;
        }
    }
    
    if ($employee_found) {
        $now = new DateTime();
        $currentTime = $now->format('h:i:s A');
        $currentDate = $now->format('d-m-Y');
        
        if ($type == 'in') {
            // Check-in
            $db->insert('attendance', [
                'employee_id' => $employee_id,
                'in_time' => $currentTime,
                'out_time' => '',
                '_date' => $currentDate,
                'face_recognition_entering' => 'True',
                'face_recognition_exiting' => '',
                'face_recognition_entering_img_path' => '',
                'face_recognition_exiting_img_path' => ''
            ]);
            $message = "✅ Check-in successful for $employee_name at $currentTime";
            $message_type = 'success';
        } else {
            // Check-out
            $attendance = $db->query('attendance');
            foreach ($attendance as $key => $record) {
                if ($record['employee_id'] == $employee_id && $record['_date'] == $currentDate && empty($record['out_time'])) {
                    $db->update('attendance', $record['auto_id'], [
                        'out_time' => $currentTime,
                        'face_recognition_exiting' => 'True'
                    ]);
                    $message = "✅ Check-out successful for $employee_name at $currentTime";
                    $message_type = 'success';
                    break;
                }
            }
        }
    } else {
        $message = "❌ Employee ID not found!";
        $message_type = 'error';
    }
}

$employees = $db->query('employees');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Take Attendance - Management Auto Attendance System</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .attendance-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .attendance-card {
            background: #fff;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .employee-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .employee-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .employee-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            border-color: #3498db;
        }
        
        .employee-card.selected {
            background: #3498db;
            color: #fff;
            border-color: #2980b9;
        }
        
        .employee-id {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .employee-name {
            font-size: 0.9rem;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn-large {
            flex: 1;
            padding: 1.5rem;
            font-size: 1.2rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-checkin {
            background: #27ae60;
            color: #fff;
        }
        
        .btn-checkin:hover {
            background: #229954;
        }
        
        .btn-checkout {
            background: #e74c3c;
            color: #fff;
        }
        
        .btn-checkout:hover {
            background: #c0392b;
        }
        
        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            font-weight: 600;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <?php include 'includes/sidebar-fast.php'; ?>
        
        <main class="main-content">
            <div class="attendance-container">
                <h1 style="text-align: center; margin-bottom: 2rem;">📸 Take Attendance</h1>
                
                <?php if (isset($message)): ?>
                    <div class="message <?php echo $message_type; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                
                <div class="attendance-card">
                    <h2>Select Employee</h2>
                    <p style="color: #7f8c8d;">Click on an employee card to select</p>
                    
                    <div class="employee-grid" id="employeeGrid">
                        <?php foreach ($employees as $emp): ?>
                            <div class="employee-card" data-id="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                <div class="employee-id"><?php echo htmlspecialchars($emp['employee_id']); ?></div>
                                <div class="employee-name"><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <form method="POST" id="attendanceForm">
                        <input type="hidden" name="employee_id" id="selectedEmployeeId">
                        <input type="hidden" name="type" id="attendanceType">
                        
                        <div class="action-buttons">
                            <button type="button" class="btn-large btn-checkin" onclick="submitAttendance('in')">
                                ✅ Check-In
                            </button>
                            <button type="button" class="btn-large btn-checkout" onclick="submitAttendance('out')">
                                🚪 Check-Out
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="attendance-card">
                    <h2>📋 Today's Attendance</h2>
                    <table style="width: 100%; margin-top: 1rem;">
                        <thead>
                            <tr>
                                <th>Employee ID</th>
                                <th>Check-In</th>
                                <th>Check-Out</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $today = date('d-m-Y');
                            $attendance = $db->query('attendance');
                            $todayAttendance = array_filter($attendance, function($a) use ($today) {
                                return $a['_date'] == $today;
                            });
                            
                            if (!empty($todayAttendance)) {
                                foreach ($todayAttendance as $record) {
                                    $status = empty($record['out_time']) ? 'In Progress' : 'Complete';
                                    $statusClass = empty($record['out_time']) ? 'status-progress' : 'status-complete';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($record['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['in_time']) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['out_time'] ?: '-') . "</td>";
                                    echo "<td><span class='status-badge $statusClass'>$status</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='4' style='text-align: center;'>No attendance records for today</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedEmployeeId = null;
        
        // Handle employee card selection
        document.querySelectorAll('.employee-card').forEach(card => {
            card.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.employee-card').forEach(c => c.classList.remove('selected'));
                
                // Select this card
                this.classList.add('selected');
                selectedEmployeeId = this.dataset.id;
            });
        });
        
        function submitAttendance(type) {
            if (!selectedEmployeeId) {
                alert('Please select an employee first!');
                return;
            }
            
            document.getElementById('selectedEmployeeId').value = selectedEmployeeId;
            document.getElementById('attendanceType').value = type;
            document.getElementById('attendanceForm').submit();
        }
    </script>
</body>
</html>
