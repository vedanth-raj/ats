<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login-fast.php');
    exit();
}
require_once 'config/json-database.php';
$db = new JsonDB();
$employees = $db->query('employees');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Webcam Attendance</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .webcam-container { max-width: 1000px; margin: 2rem auto; padding: 2rem; }
        .video-box { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); text-align: center; margin-bottom: 2rem; }
        #video { width: 100%; max-width: 640px; border-radius: 10px; border: 3px solid #3498db; }
        .controls { margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .btn-cam { padding: 1rem 2rem; font-size: 1.1rem; border-radius: 8px; border: none; cursor: pointer; transition: all 0.3s; }
        .btn-green { background: #27ae60; color: #fff; }
        .btn-green:hover { background: #229954; }
        .btn-blue { background: #3498db; color: #fff; }
        .btn-blue:hover { background: #2980b9; }
        .btn-red { background: #e74c3c; color: #fff; }
        .btn-red:hover { background: #c0392b; }
        .status { padding: 1rem; border-radius: 5px; margin-top: 1rem; font-weight: 600; text-align: center; }
        .status-success { background: #d4edda; color: #155724; }
        .status-info { background: #d1ecf1; color: #0c5460; }
        select { padding: 0.75rem; font-size: 1rem; border-radius: 5px; border: 1px solid #ddd; min-width: 250px; }
        canvas { display: none; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php include 'includes/sidebar-fast.php'; ?>
        <main class="main-content">
            <div class="webcam-container">
                <h1 style="text-align: center; margin-bottom: 2rem;">📸 Webcam Attendance System</h1>
                
                <div class="video-box">
                    <h2>Live Camera Feed</h2>
                    <video id="video" autoplay playsinline></video>
                    <canvas id="canvas"></canvas>
                    
                    <div id="status" class="status status-info" style="display: none;">Ready</div>
                    
                    <div class="controls">
                        <select id="employeeSelect">
                            <option value="">-- Select Employee --</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                    <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button id="startBtn" class="btn-cam btn-green" onclick="startCamera()">📷 Start Camera</button>
                        <button id="captureBtn" class="btn-cam btn-blue" onclick="capturePhoto()" style="display: none;">✅ Capture & Mark</button>
                        <button id="stopBtn" class="btn-cam btn-red" onclick="stopCamera()" style="display: none;">⏹️ Stop</button>
                    </div>
                </div>
                
                <div style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2>📋 Today's Attendance</h2>
                    <table style="width: 100%; margin-top: 1rem;">
                        <thead>
                            <tr><th>ID</th><th>Name</th><th>Time</th><th>Status</th></tr>
                        </thead>
                        <tbody id="attendanceBody">
                            <?php
                            $today = date('d-m-Y');
                            $attendance = $db->query('attendance');
                            foreach ($attendance as $record) {
                                if ($record['_date'] == $today) {
                                    $empData = array_filter($employees, function($e) use ($record) {
                                        return $e['employee_id'] == $record['employee_id'];
                                    });
                                    $emp = reset($empData);
                                    $name = $emp ? $emp['first_name'] . ' ' . $emp['last_name'] : 'Unknown';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($record['employee_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($name) . "</td>";
                                    echo "<td>" . htmlspecialchars($record['in_time']) . "</td>";
                                    echo "<td><span class='status-badge status-complete'>✅ Present</span></td>";
                                    echo "</tr>";
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        let stream = null;
        
        async function startCamera() {
            try {
                showStatus('Starting camera...', 'info');
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                document.getElementById('video').srcObject = stream;
                
                document.getElementById('startBtn').style.display = 'none';
                document.getElementById('captureBtn').style.display = 'inline-block';
                document.getElementById('stopBtn').style.display = 'inline-block';
                
                showStatus('✅ Camera ready! Select employee and capture', 'success');
            } catch (error) {
                showStatus('❌ Camera error: ' + error.message, 'info');
            }
        }
        
        async function capturePhoto() {
            const employeeId = document.getElementById('employeeSelect').value;
            if (!employeeId) {
                alert('Please select an employee!');
                return;
            }
            
            showStatus('📸 Capturing...', 'info');
            
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            
            const formData = new FormData();
            formData.append('employee_id', employeeId);
            formData.append('action', 'mark_attendance');
            
            try {
                const response = await fetch('process-attendance.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();
                
                if (result.success) {
                    showStatus('✅ ' + result.message, 'success');
                    setTimeout(() => location.reload(), 2000);
                } else {
                    showStatus('❌ ' + result.message, 'info');
                }
            } catch (error) {
                showStatus('❌ Error: ' + error.message, 'info');
            }
        }
        
        function stopCamera() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                document.getElementById('video').srcObject = null;
            }
            document.getElementById('startBtn').style.display = 'inline-block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('stopBtn').style.display = 'none';
            showStatus('Camera stopped', 'info');
        }
        
        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status status-' + type;
            status.style.display = 'block';
        }
    </script>
</body>
</html>
