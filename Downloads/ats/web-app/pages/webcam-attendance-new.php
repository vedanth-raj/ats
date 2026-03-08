<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mark Attendance - Face Recognition</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .attendance-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 0 auto;
            background: #000;
            border-radius: 8px;
            overflow: hidden;
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
        
        .status-panel {
            margin-top: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }
        
        .status-message {
            font-size: 18px;
            margin: 10px 0;
            font-weight: 500;
        }
        
        .status-message.detecting {
            color: #007bff;
        }
        
        .status-message.recognized {
            color: #28a745;
        }
        
        .status-message.error {
            color: #dc3545;
        }
        
        .controls {
            margin-top: 20px;
            text-align: center;
        }
        
        .btn {
            padding: 12px 30px;
            font-size: 16px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 10px;
        }
        
        .btn-primary {
            background: #007bff;
            color: white;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin-top: 20px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #007bff;
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="attendance-container">
        <h1>Mark Attendance - Face Recognition</h1>
        
        <div class="video-container">
            <video id="video" autoplay playsinline></video>
            <canvas id="canvas"></canvas>
        </div>
        
        <div class="status-panel">
            <div id="status" class="status-message">Click "Start Camera" to begin</div>
            <div id="guidance" style="margin-top: 10px; color: #666;"></div>
            <div id="confidence" style="margin-top: 10px; font-size: 14px; color: #666;"></div>
        </div>
        
        <div class="controls">
            <button id="startBtn" class="btn btn-primary">Start Camera</button>
            <button id="stopBtn" class="btn btn-danger" disabled>Stop Camera</button>
        </div>
        
        <div class="stats">
            <div class="stat-item">
                <div class="stat-value" id="modelsCount">0</div>
                <div class="stat-label">Models Loaded</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="fpsCount">0</div>
                <div class="stat-label">FPS</div>
            </div>
            <div class="stat-item">
                <div class="stat-value" id="todayCount">0</div>
                <div class="stat-label">Today's Attendance</div>
            </div>
        </div>
    </div>
    
    <!-- Load face-api.js from CDN -->
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    
    <!-- Load our controllers -->
    <script src="../assets/js/utils.js?v=2"></script>
    <script src="../assets/js/webcam-controller.js?v=2"></script>
    <script src="../assets/js/face-detection-controller.js?v=2"></script>
    <script src="../assets/js/face-recognition-controller.js?v=2"></script>
    <script src="../assets/js/attendance-controller.js?v=2"></script>
    
    <script>
        // Initialize controllers
        const video = document.getElementById('video');
        const canvas = document.getElementById('canvas');
        const statusEl = document.getElementById('status');
        const guidanceEl = document.getElementById('guidance');
        const confidenceEl = document.getElementById('confidence');
        const startBtn = document.getElementById('startBtn');
        const stopBtn = document.getElementById('stopBtn');
        
        const webcamController = new WebcamController(video);
        const faceDetector = new FaceDetectionController();
        const faceRecognizer = new FaceRecognitionController(0.6);
        const attendanceController = new AttendanceController();
        
        let isProcessing = false;
        let lastRecognitionTime = 0;
        let fpsCounter = 0;
        let fpsInterval = null;
        
        // Initialize
        async function initialize() {
            try {
                showLoading('Loading face recognition models...');
                
                // Load face detection models
                await faceDetector.loadModels();
                
                // Load employee models
                const modelCount = await faceRecognizer.loadEmployeeModels();
                document.getElementById('modelsCount').textContent = modelCount;
                
                hideLoading();
                displaySuccess(`Loaded ${modelCount} employee models`);
                
                // Load today's attendance count
                updateTodayCount();
                
            } catch (error) {
                hideLoading();
                displayError('Failed to load models: ' + error.message);
                console.error(error);
            }
        }
        
        // Start camera and detection
        async function startCamera() {
            try {
                statusEl.textContent = 'Starting camera...';
                statusEl.className = 'status-message detecting';
                
                await webcamController.startCamera();
                
                // Resize canvas to match video
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                
                // Start detection loop
                faceDetector.startDetectionLoop(video, handleDetection, 10);
                
                // Start FPS counter
                startFPSCounter();
                
                startBtn.disabled = true;
                stopBtn.disabled = false;
                
                statusEl.textContent = 'Camera active - Looking for faces...';
                
            } catch (error) {
                displayError('Failed to start camera: ' + error.message);
                statusEl.textContent = 'Camera error';
                statusEl.className = 'status-message error';
            }
        }
        
        // Stop camera and detection
        function stopCamera() {
            webcamController.stopCamera();
            faceDetector.stopDetectionLoop();
            stopFPSCounter();
            
            // Clear canvas
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            startBtn.disabled = false;
            stopBtn.disabled = true;
            
            statusEl.textContent = 'Camera stopped';
            statusEl.className = 'status-message';
            guidanceEl.textContent = '';
            confidenceEl.textContent = '';
        }
        
        // Handle face detection
        async function handleDetection(detection) {
            fpsCounter++;
            
            const ctx = canvas.getContext('2d');
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            
            if (!detection) {
                statusEl.textContent = 'No face detected';
                statusEl.className = 'status-message';
                guidanceEl.textContent = 'Please position your face in the camera view';
                confidenceEl.textContent = '';
                return;
            }
            
            // Draw detection box
            faceDetector.drawDetection(canvas, detection, '#00ff00', 3);
            
            // Get guidance
            const guidance = faceDetector.getFaceGuidance(detection, video.videoWidth, video.videoHeight);
            guidanceEl.textContent = guidance;
            
            // Check if face is properly positioned
            const isCentered = faceDetector.isFaceCentered(detection, video.videoWidth, video.videoHeight);
            const isSizeOk = faceDetector.isFaceSizeAppropriate(detection, video.videoWidth, video.videoHeight);
            
            if (!isCentered || !isSizeOk) {
                statusEl.textContent = 'Adjust position';
                statusEl.className = 'status-message';
                return;
            }
            
            // Prevent multiple simultaneous recognitions
            if (isProcessing) {
                return;
            }
            
            // Throttle recognition (max once per 2 seconds)
            const now = Date.now();
            if (now - lastRecognitionTime < 2000) {
                return;
            }
            
            // Recognize face
            isProcessing = true;
            lastRecognitionTime = now;
            
            try {
                statusEl.textContent = 'Recognizing...';
                statusEl.className = 'status-message detecting';
                
                const descriptor = faceDetector.extractDescriptor(detection);
                if (!descriptor) {
                    throw new Error('Failed to extract face descriptor');
                }
                
                const match = faceRecognizer.recognizeFace(descriptor);
                
                if (match) {
                    statusEl.textContent = `Recognized: ${match.employeeName}`;
                    statusEl.className = 'status-message recognized';
                    confidenceEl.textContent = `Confidence: ${(match.confidence * 100).toFixed(1)}%`;
                    
                    // Mark attendance
                    const result = await attendanceController.markAttendanceWithRecognition(
                        match.employeeId,
                        match.employeeName,
                        match.confidence
                    );
                    
                    if (result.success) {
                        // Update today's count
                        updateTodayCount();
                        
                        // Stop camera after successful attendance
                        setTimeout(() => {
                            stopCamera();
                        }, 2000);
                    }
                    
                } else {
                    statusEl.textContent = 'Face not recognized';
                    statusEl.className = 'status-message error';
                    confidenceEl.textContent = 'Please try again or contact administrator';
                }
                
            } catch (error) {
                console.error('Recognition error:', error);
                statusEl.textContent = 'Recognition error';
                statusEl.className = 'status-message error';
            } finally {
                isProcessing = false;
            }
        }
        
        // FPS counter
        function startFPSCounter() {
            fpsCounter = 0;
            fpsInterval = setInterval(() => {
                document.getElementById('fpsCount').textContent = fpsCounter;
                fpsCounter = 0;
            }, 1000);
        }
        
        function stopFPSCounter() {
            if (fpsInterval) {
                clearInterval(fpsInterval);
                fpsInterval = null;
            }
            document.getElementById('fpsCount').textContent = '0';
        }
        
        // Update today's attendance count
        async function updateTodayCount() {
            try {
                const records = await attendanceController.getTodayAttendance();
                document.getElementById('todayCount').textContent = records.length;
            } catch (error) {
                console.error('Error fetching today\'s count:', error);
            }
        }
        
        // Event listeners
        startBtn.addEventListener('click', startCamera);
        stopBtn.addEventListener('click', stopCamera);
        
        // Initialize on page load
        initialize();
    </script>
</body>
</html>
