<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login-fast.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Quick Attendance</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f5f6fa; padding: 2rem; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { text-align: center; color: #2c3e50; margin-bottom: 2rem; }
        video { width: 100%; max-width: 640px; border: 3px solid #3498db; border-radius: 10px; display: block; margin: 0 auto; }
        .controls { text-align: center; margin: 1.5rem 0; }
        input, select, button { padding: 0.75rem 1.5rem; font-size: 1rem; margin: 0.5rem; border-radius: 5px; border: 1px solid #ddd; }
        button { background: #3498db; color: #fff; border: none; cursor: pointer; }
        button:hover { background: #2980b9; }
        .btn-green { background: #27ae60; }
        .btn-green:hover { background: #229954; }
        .btn-red { background: #e74c3c; }
        .btn-red:hover { background: #c0392b; }
        .status { padding: 1rem; margin: 1rem 0; border-radius: 5px; text-align: center; font-weight: bold; }
        .success { background: #d4edda; color: #155724; }
        .info { background: #d1ecf1; color: #0c5460; }
        canvas { display: none; }
    </style>
</head>
<body>
    <div class="container">
        <h1>📸 Quick Attendance System</h1>
        
        <video id="video" autoplay playsinline></video>
        <canvas id="canvas"></canvas>
        
        <div id="status" class="status info" style="display: none;">Ready</div>
        
        <div class="controls">
            <input type="text" id="employeeId" placeholder="Enter Employee ID (e.g., 0001)" style="width: 250px;">
            <br>
            <button class="btn-green" onclick="startCamera()">📷 Start Camera</button>
            <button onclick="capture()" style="display: none;" id="captureBtn">✅ Mark Attendance</button>
            <button class="btn-red" onclick="stopCamera()" style="display: none;" id="stopBtn">⏹️ Stop</button>
        </div>
        
        <div style="text-align: center; margin-top: 2rem;">
            <a href="index-fast.php" style="color: #3498db; text-decoration: none;">← Back to Dashboard</a>
        </div>
    </div>
    
    <script>
        let stream = null;
        
        async function startCamera() {
            try {
                showStatus('Starting camera...', 'info');
                stream = await navigator.mediaDevices.getUserMedia({ video: true });
                document.getElementById('video').srcObject = stream;
                document.getElementById('captureBtn').style.display = 'inline-block';
                document.getElementById('stopBtn').style.display = 'inline-block';
                showStatus('✅ Camera ready! Enter Employee ID and capture', 'success');
            } catch (error) {
                showStatus('❌ Error: ' + error.message, 'info');
            }
        }
        
        async function capture() {
            const employeeId = document.getElementById('employeeId').value.trim();
            if (!employeeId) {
                alert('Please enter Employee ID!');
                return;
            }
            
            showStatus('📸 Marking attendance...', 'info');
            
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
                    document.getElementById('employeeId').value = '';
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
            document.getElementById('captureBtn').style.display = 'none';
            document.getElementById('stopBtn').style.display = 'none';
            showStatus('Camera stopped', 'info');
        }
        
        function showStatus(message, type) {
            const status = document.getElementById('status');
            status.textContent = message;
            status.className = 'status ' + type;
            status.style.display = 'block';
        }
    </script>
</body>
</html>
