<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Face Recognition Attendance - Enhanced</title>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
        }
        
        h1 {
            text-align: center;
            color: #1e3c72;
            margin-bottom: 10px;
            font-size: 32px;
        }
        
        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto 20px;
            background: #000;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        #video {
            width: 100%;
            height: auto;
            display: block;
        }
        
        #canvas {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
        }
        
        /* Face alignment guide overlay */
        .face-guide {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            height: 400px;
            border: 3px dashed rgba(0, 255, 0, 0.5);
            border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
            pointer-events: none;
            z-index: 10;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% {
                border-color: rgba(0, 255, 0, 0.5);
                transform: translate(-50%, -50%) scale(1);
            }
            50% {
                border-color: rgba(0, 255, 0, 0.8);
                transform: translate(-50%, -50%) scale(1.02);
            }
        }
        
        .guide-text {
            position: absolute;
            top: -40px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(0, 255, 0, 0.9);
            color: white;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 600;
            white-space: nowrap;
        }
        
        .status-panel {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .status-message {
            font-size: 18px;
            font-weight: 600;
            text-align: center;
            margin: 10px 0;
            padding: 15px;
            border-radius: 8px;
        }
        
        .status-message.detecting {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-message.recognized {
            background: #d4edda;
            color: #155724;
        }
        
        .status-message.error {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        
        .stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .stat-label {
            font-size: 12px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .stat-value {
            font-size: 24px;
            font-weight: bold;
            color: #1e3c72;
        }
        
        .controls {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }
        
        .btn {
            padding: 15px 40px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .recognition-result {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
            display: none;
        }
        
        .recognition-result h3 {
            margin-bottom: 10px;
        }
        
        .recognition-result p {
            font-size: 18px;
            margin: 5px 0;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Face Recognition Attendance</h1>
        <p class="subtitle">Position your face within the guide for accurate recognition</p>
        
        <div class="video-container">
            <video id="video" autoplay muted playsinline></video>
            <canvas id="canvas"></canvas>
            <div class="face-guide" id="faceGuide">
                <div class="guide-text" id="guideText">Position your face here</div>
            </div>
        </div>
        
        <div class="status-panel">
            <div class="status-message detecting" id="status">
                Ready to start
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Models Loaded</div>
                    <div class="stat-value" id="modelsCount">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">FPS</div>
                    <div class="stat-value" id="fps">0</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Face Detected</div>
                    <div class="stat-value" id="faceDetected">No</div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Confidence</div>
                    <div class="stat-value" id="confidence">0%</div>
                </div>
            </div>
        </div>
        
        <div class="recognition-result" id="recognitionResult">
            <h3>✓ Attendance Marked Successfully!</h3>
            <p id="employeeName"></p>
            <p id="employeeId"></p>
            <p id="timestamp"></p>
        </div>
        
        <div class="controls">
            <button class="btn btn-primary" id="startBtn" onclick="startCamera()">
                Start Camera
            </button>
            <button class="btn btn-danger" id="stopBtn" onclick="stopCamera()" disabled>
                Stop Camera
            </button>
        </div>
        
        <div class="loading" id="loading" style="display:none;">
            <div class="spinner"></div>
            <p>Loading face recognition models...</p>
        </div>
    </div>

    <script src="../assets/js/utils.js?v=4"></script>
    <script src="../assets/js/webcam-controller.js?v=4"></script>
    <script>
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
        let modelsLoaded = false;
        let webcamController = null;
        let isProcessing = false;
        let employeeModels = [];
        let fpsCounter = 0;
        let lastFpsUpdate = Date.now();
        let attendanceMarked = false;
        
        // Load face-api models
        async function loadModels() {
            if (modelsLoaded) return;
            
            try {
                document.getElementById('loading').style.display = 'block';
                console.log('Loading face-api.js models...');
                
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                
                modelsLoaded = true;
                console.log('Models loaded successfully');
                
                // Load employee models
                await loadEmployeeModels();
                
                document.getElementById('loading').style.display = 'none';
            } catch (error) {
                console.error('Error loading models:', error);
                document.getElementById('status').className = 'status-message error';
                document.getElementById('status').textContent = 'Error loading models: ' + error.message;
                document.getElementById('loading').style.display = 'none';
            }
        }
        
        async function loadEmployeeModels() {
            try {
                const response = await fetch('../api/get-models.php');
                const data = await response.json();
                employeeModels = data.models || [];
                document.getElementById('modelsCount').textContent = employeeModels.length;
                console.log(`Loaded ${employeeModels.length} employee models`);
            } catch (error) {
                console.error('Error loading employee models:', error);
            }
        }
        
        async function startCamera() {
            try {
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                const startBtn = document.getElementById('startBtn');
                const stopBtn = document.getElementById('stopBtn');
                const statusEl = document.getElementById('status');
                
                statusEl.className = 'status-message detecting';
                statusEl.textContent = 'Starting camera...';
                
                if (!webcamController) {
                    webcamController = new WebcamController(video);
                }
                
                await webcamController.startCamera();
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                startBtn.disabled = true;
                stopBtn.disabled = false;
                isProcessing = true;
                attendanceMarked = false;
                
                statusEl.textContent = 'Camera active - Position your face';
                
                // Start detection loop
                detectFaces();
                
            } catch (error) {
                console.error('Camera error:', error);
                document.getElementById('status').className = 'status-message error';
                document.getElementById('status').textContent = 'Camera error: ' + error.message;
            }
        }
        
        function stopCamera() {
            isProcessing = false;
            if (webcamController) {
                webcamController.stopCamera();
            }
            
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            document.getElementById('startBtn').disabled = false;
            document.getElementById('stopBtn').disabled = true;
            document.getElementById('status').className = 'status-message detecting';
            document.getElementById('status').textContent = 'Camera stopped';
            document.getElementById('faceDetected').textContent = 'No';
            document.getElementById('confidence').textContent = '0%';
        }
        
        async function detectFaces() {
            if (!isProcessing) return;
            
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const ctx = canvas.getContext('2d');
            
            try {
                // Detect face with landmarks
                const detection = await faceapi
                    .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                    .withFaceLandmarks()
                    .withFaceDescriptor();
                
                ctx.clearRect(0, 0, canvas.width, canvas.height);
                
                // Update FPS
                fpsCounter++;
                const now = Date.now();
                if (now - lastFpsUpdate >= 1000) {
                    document.getElementById('fps').textContent = fpsCounter;
                    fpsCounter = 0;
                    lastFpsUpdate = now;
                }
                
                if (detection) {
                    document.getElementById('faceDetected').textContent = 'Yes';
                    
                    // Draw face mesh (landmarks)
                    drawFaceMesh(ctx, detection.landmarks);
                    
                    // Draw bounding box
                    const box = detection.detection.box;
                    ctx.strokeStyle = '#00ff00';
                    ctx.lineWidth = 3;
                    ctx.strokeRect(box.x, box.y, box.width, box.height);
                    
                    // Check if face is centered and appropriate size
                    const isCentered = checkFaceCentered(box, canvas.width, canvas.height);
                    const isGoodSize = checkFaceSize(box, canvas.width, canvas.height);
                    
                    if (isCentered && isGoodSize) {
                        document.getElementById('guideText').textContent = 'Perfect! Hold still...';
                        document.getElementById('faceGuide').style.borderColor = 'rgba(0, 255, 0, 0.8)';
                        
                        // Try to recognize
                        if (!attendanceMarked) {
                            await recognizeFace(detection);
                        }
                    } else if (!isCentered) {
                        document.getElementById('guideText').textContent = 'Center your face';
                        document.getElementById('faceGuide').style.borderColor = 'rgba(255, 165, 0, 0.8)';
                    } else if (!isGoodSize) {
                        const faceArea = box.width * box.height;
                        const videoArea = canvas.width * canvas.height;
                        const ratio = faceArea / videoArea;
                        
                        if (ratio < 0.15) {
                            document.getElementById('guideText').textContent = 'Move closer';
                        } else {
                            document.getElementById('guideText').textContent = 'Move back';
                        }
                        document.getElementById('faceGuide').style.borderColor = 'rgba(255, 165, 0, 0.8)';
                    }
                } else {
                    document.getElementById('faceDetected').textContent = 'No';
                    document.getElementById('guideText').textContent = 'Position your face here';
                    document.getElementById('faceGuide').style.borderColor = 'rgba(0, 255, 0, 0.5)';
                }
                
            } catch (error) {
                console.error('Detection error:', error);
            }
            
            // Continue loop
            setTimeout(detectFaces, 100);
        }
        
        function drawFaceMesh(ctx, landmarks) {
            const points = landmarks.positions;
            
            // Draw face mesh with cool cyan color
            ctx.strokeStyle = '#00ffff';
            ctx.lineWidth = 1;
            ctx.fillStyle = '#00ffff';
            
            // Draw landmark points
            points.forEach(point => {
                ctx.beginPath();
                ctx.arc(point.x, point.y, 2, 0, 2 * Math.PI);
                ctx.fill();
            });
            
            // Draw face outline connections
            const jawOutline = points.slice(0, 17);
            const leftEyebrow = points.slice(17, 22);
            const rightEyebrow = points.slice(22, 27);
            const noseBridge = points.slice(27, 31);
            const noseBottom = points.slice(31, 36);
            const leftEye = points.slice(36, 42);
            const rightEye = points.slice(42, 48);
            const outerLip = points.slice(48, 60);
            const innerLip = points.slice(60, 68);
            
            // Draw lines
            drawLine(ctx, jawOutline);
            drawLine(ctx, leftEyebrow);
            drawLine(ctx, rightEyebrow);
            drawLine(ctx, noseBridge);
            drawLine(ctx, noseBottom);
            drawClosedLine(ctx, leftEye);
            drawClosedLine(ctx, rightEye);
            drawClosedLine(ctx, outerLip);
            drawClosedLine(ctx, innerLip);
        }
        
        function drawLine(ctx, points) {
            if (points.length < 2) return;
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.stroke();
        }
        
        function drawClosedLine(ctx, points) {
            if (points.length < 2) return;
            ctx.beginPath();
            ctx.moveTo(points[0].x, points[0].y);
            for (let i = 1; i < points.length; i++) {
                ctx.lineTo(points[i].x, points[i].y);
            }
            ctx.closePath();
            ctx.stroke();
        }
        
        function checkFaceCentered(box, videoWidth, videoHeight) {
            const faceCenterX = box.x + box.width / 2;
            const faceCenterY = box.y + box.height / 2;
            const videoCenterX = videoWidth / 2;
            const videoCenterY = videoHeight / 2;
            const toleranceX = videoWidth * 0.25;
            const toleranceY = videoHeight * 0.25;
            
            return Math.abs(faceCenterX - videoCenterX) < toleranceX &&
                   Math.abs(faceCenterY - videoCenterY) < toleranceY;
        }
        
        function checkFaceSize(box, videoWidth, videoHeight) {
            const faceArea = box.width * box.height;
            const videoArea = videoWidth * videoHeight;
            const ratio = faceArea / videoArea;
            return ratio >= 0.15 && ratio <= 0.6;
        }
        
        async function recognizeFace(detection) {
            try {
                const descriptor = Array.from(detection.descriptor);
                let bestMatch = null;
                let bestDistance = Infinity;
                
                for (const model of employeeModels) {
                    const distance = euclideanDistance(descriptor, model.descriptor);
                    if (distance < bestDistance) {
                        bestDistance = distance;
                        bestMatch = model;
                    }
                }
                
                const threshold = 0.6;
                if (bestMatch && bestDistance < threshold) {
                    const confidence = Math.max(0, (1 - bestDistance) * 100);
                    document.getElementById('confidence').textContent = confidence.toFixed(1) + '%';
                    
                    if (confidence > 60) {
                        // Mark attendance
                        await markAttendance(bestMatch.employee_id, bestMatch.name, confidence);
                    }
                } else {
                    document.getElementById('confidence').textContent = '0%';
                }
                
            } catch (error) {
                console.error('Recognition error:', error);
            }
        }
        
        async function markAttendance(employeeId, name, confidence) {
            try {
                const response = await fetch('../api/process-attendance.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: `employee_id=${employeeId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    attendanceMarked = true;
                    
                    document.getElementById('status').className = 'status-message recognized';
                    document.getElementById('status').textContent = '✓ Attendance Marked!';
                    
                    const resultDiv = document.getElementById('recognitionResult');
                    resultDiv.style.display = 'block';
                    document.getElementById('employeeName').textContent = `Name: ${name}`;
                    document.getElementById('employeeId').textContent = `Employee ID: ${employeeId}`;
                    document.getElementById('timestamp').textContent = `Time: ${new Date().toLocaleString()}`;
                    
                    // Stop camera after 3 seconds
                    setTimeout(() => {
                        stopCamera();
                    }, 3000);
                } else {
                    document.getElementById('status').className = 'status-message error';
                    document.getElementById('status').textContent = data.message || 'Failed to mark attendance';
                }
                
            } catch (error) {
                console.error('Attendance error:', error);
            }
        }
        
        function euclideanDistance(a, b) {
            let sum = 0;
            for (let i = 0; i < a.length; i++) {
                sum += Math.pow(a[i] - b[i], 2);
            }
            return Math.sqrt(sum);
        }
        
        // Initialize on load
        window.addEventListener('load', loadModels);
    </script>
</body>
</html>
