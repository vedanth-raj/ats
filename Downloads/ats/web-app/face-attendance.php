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
    <title>Face Recognition Attendance</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow-models/blazeface"></script>
    <style>
        .face-container { max-width: 1200px; margin: 2rem auto; padding: 2rem; }
        .video-section { background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); margin-bottom: 2rem; text-align: center; }
        #webcam { width: 100%; max-width: 640px; border-radius: 10px; transform: scaleX(-1); }
        .controls { margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .btn-webcam { padding: 1rem 2rem; font-size: 1.1rem; border-radius: 8px; border: none; cursor: pointer; transition: all 0.3s; }
        .btn-start { background: #27ae60; color: #fff; }
        .btn-start:hover { background: #229954; }
        .btn-capture { background: #3498db; color: #fff; }
        .btn-capture:hover { background: #2980b9; }
        .btn-stop { background: #e74c3c; color: #fff; }
        .btn-stop:hover { background: #c0392b; }
        .status-box { padding: 1rem; border-radius: 5px; margin-top: 1rem; font-weight: 600; }
        .status-success { background: #d4edda; color: #155724; }
        .status-info { background: #d1ecf1; color: #0c5460; }
        .status-warning { background: #fff3cd; color: #856404; }
        .employee-select { padding: 0.75rem; font-size: 1rem; border-radius: 5px; border: 1px solid #ddd; min-width: 200px; }
        canvas { display: none; }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <?php include 'includes/sidebar-fast.php'; ?>
        <main class="main-content">
            <div class="face-container">
                <h1 style="text-align: center; margin-bottom: 2rem;">📸 Face Recognition Attendance</h1>
                
                <div class="video-section">
                    <h2>Webcam Feed</h2>
                    <video id="webcam" autoplay playsinline></video>
                    <canvas id="canvas"></canvas>
                    
                    <div id="status" class="status-box status-info" style="display: none;">
                        Ready to start
                    </div>
                    
                    <div class="controls">
                        <select id="employeeSelect" class="employee-select">
                            <option value="">Select Employee</option>
                            <?php foreach ($employees as $emp): ?>
                                <option value="<?php echo htmlspecialchars($emp['employee_id']); ?>">
                                    <?php echo htmlspecialchars($emp['employee_id'] . ' - ' . $emp['first_name'] . ' ' . $emp['last_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        
                        <button id="startBtn" class="btn-webcam btn-start" onclick="startWebcam()">
                            📷 Start Webcam
                        </button>
                        <button id="captureBtn" class="btn-webcam btn-capture" onclick="captureAttendance()" style="display: none;">
                            ✅ Mark Attendance
                        </button>
                        <button id="stopBtn" class="btn-webcam btn-stop" onclick="stopWebcam()" style="display: none;">
                            ⏹️ Stop
                        </button>
                    </div>
                </div>
                <div class="attendance-card" style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
                    <h2>📋 Today's Attendance</h2>
                    <div id="attendanceList">
                        <table style="width: 100%; margin-top: 1rem;">
                            <thead>
                                <tr><th>Employee ID</th><th>Name</th><th>Time</th><th>Status</th></tr>
                            </thead>
                            <tbody id="attendanceBody">
                                <?php
                                $today = date('d-m-Y');
                                $attendance = $db->query('attendance');
                                $todayAttendance = array_filter($attendance, function($a) use ($today) {
                                    return $a['_date'] == $today;
                                });
                                
                                if (!empty($todayAttendance)) {
                                    foreach ($todayAttendance as $record) {
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
                                } else {
                                    echo "<tr><td colspan='4' style='text-align: center;'>No attendance yet</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        let stream = null;
        let model = null;
        let faceDetected = false;
        
        async function startWebcam() {
            try {
                showStatus('Starting webcam...', 'info');
                stream = await navigator.mediaDevices.getUserMedia({ 
                    video: { width: 640, height: 480 } 
                });
                document.getElementById('webcam').srcObject = stream;
                
                document.getElementById('startBtn').style.display = 'none';
                document.getElementById('captureBtn').style.display = 'inline-block';
                document.getElementById('stopBtn').style.display = 'inline-block';
                
                showStatus('✅ Webcam started! Loading face detection...', 'info');
                model = await blazeface.load();
                showStatus('✅ Ready! Position your face in the camera', 'success');
                detectFace();
            } catch (error) {
                showStatus('❌ Error: ' + error.message, 'warning');
            }
        }
        
        async function detectFace() {
            if (!stream) return;
            
            const video = document.getElementById('webcam');
            const predictions = await model.estimateFaces(video, false);
            
            if (predictions.length > 0) {
                faceDetected = true;
                showStatus('👤 Face detected! Ready to mark attendance', 'success');
            } else {
                faceDetected = false;
                showStatus('⚠️ No face detected. Please position your face in camera', 'warning');
            }
            
            setTimeout(detectFace, 500);
        }
        
        async function captureAttendance() {
            const employeeId = document.getElementById('employeeSelect').value;
            
            if (!employeeId) {
                alert('Please select an employee first!');
                return;
            }
            
            if (!faceDetected) {
                alert('No face detected! Please position your face in the camera.');
                return;
            }
            
            showStatus('📸 Capturing attendance...', 'info');
            
            const video = document.getElementById('webcam');
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
                    showStatus('❌ ' + result.message, 'warning');
                }
            } catch (error) {
                showStatus('❌ Error: ' + error.message, 'warning');
            }
        }
        
        function stopWebcam() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
                stream = null;
                document.getElementById('webcam').srcObject = null;
            }
            
            document.getElementById('startBtn').style.display = 'inline-block';
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('stopBtn').style.display = 'none';
            showStatus('Webcam stopped', 'info');
        }
        
        function showStatus(message, type) {
            const statusBox = document.getElementById('status');
            statusBox.textContent = message;
            statusBox.className = 'status-box status-' + type;
            statusBox.style.display = 'block';
        }
    </script>
</body>
</html>
