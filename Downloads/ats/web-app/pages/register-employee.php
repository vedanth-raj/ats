<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register Employee - Face Recognition</title>
    <script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
        }
        
        .steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            position: relative;
        }
        
        .step {
            flex: 1;
            text-align: center;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 8px;
            margin: 0 5px;
            position: relative;
        }
        
        .step.active {
            background: #667eea;
            color: white;
        }
        
        .step.completed {
            background: #28a745;
            color: white;
        }
        
        .step-number {
            display: inline-block;
            width: 30px;
            height: 30px;
            line-height: 30px;
            border-radius: 50%;
            background: white;
            color: #333;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .step.active .step-number {
            background: white;
            color: #667eea;
        }
        
        .step.completed .step-number {
            background: white;
            color: #28a745;
        }
        
        .section {
            display: none;
        }
        
        .section.active {
            display: block;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
        }
        
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .video-container {
            position: relative;
            width: 100%;
            max-width: 640px;
            margin: 20px auto;
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
        
        .progress-container {
            margin: 20px 0;
        }
        
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .btn {
            padding: 12px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin: 5px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .status {
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            display: none;
        }
        
        .status.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .status.info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
        }
        
        .captured-images {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin: 20px 0;
            max-height: 300px;
            overflow-y: auto;
        }
        
        .captured-image {
            width: 100%;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        
        .controls {
            text-align: center;
            margin: 20px 0;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        
        .info-box h3 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .info-box p {
            color: #666;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>👤 Register New Employee</h1>
        <p class="subtitle">Complete face recognition registration in 4 easy steps</p>
        
        <div class="steps">
            <div class="step active" id="step1-indicator">
                <div class="step-number">1</div>
                <div>Employee Info</div>
            </div>
            <div class="step" id="step2-indicator">
                <div class="step-number">2</div>
                <div>Capture Dataset</div>
            </div>
            <div class="step" id="step3-indicator">
                <div class="step-number">3</div>
                <div>Train Model</div>
            </div>
            <div class="step" id="step4-indicator">
                <div class="step-number">4</div>
                <div>Test Recognition</div>
            </div>
        </div>
        
        <!-- Step 1: Employee Information -->
        <div class="section active" id="step1">
            <h2>Step 1: Employee Information</h2>
            <form id="employeeForm">
                <div class="form-group">
                    <label>Employee ID *</label>
                    <input type="text" id="employeeId" required placeholder="e.g., 0003">
                </div>
                <div class="form-group">
                    <label>First Name *</label>
                    <input type="text" id="firstName" required>
                </div>
                <div class="form-group">
                    <label>Last Name *</label>
                    <input type="text" id="lastName" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="email" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select id="department">
                        <option>Engineering</option>
                        <option>Management</option>
                        <option>Sales</option>
                        <option>HR</option>
                        <option>Finance</option>
                    </select>
                </div>
                <div class="controls">
                    <button type="button" class="btn btn-primary" onclick="goToStep2()">Next: Capture Dataset →</button>
                </div>
            </form>
        </div>
        
        <!-- Step 2: Capture Dataset -->
        <div class="section" id="step2">
            <h2>Step 2: Capture Face Dataset</h2>
            <div class="info-box">
                <h3>Instructions:</h3>
                <p>• Position your face in the center of the frame</p>
                <p>• Keep your face well-lit and clearly visible</p>
                <p>• Slowly move your head slightly (left, right, up, down)</p>
                <p>• We'll capture 50 images automatically</p>
            </div>
            
            <div class="video-container">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="canvas"></canvas>
            </div>
            
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="captureProgress">0/50</div>
                </div>
            </div>
            
            <div class="status" id="captureStatus"></div>
            
            <div class="captured-images" id="capturedImages"></div>
            
            <div class="controls">
                <button class="btn btn-secondary" onclick="goToStep1()">← Back</button>
                <button class="btn btn-primary" id="startCaptureBtn" onclick="startCapture()">Start Capture</button>
                <button class="btn btn-success" id="nextToTrainBtn" onclick="goToStep3()" style="display:none;">Next: Train Model →</button>
            </div>
        </div>
        
        <!-- Step 3: Train Model -->
        <div class="section" id="step3">
            <h2>Step 3: Train Face Recognition Model</h2>
            <div class="info-box">
                <h3>Training Process:</h3>
                <p>• Loading captured images</p>
                <p>• Extracting face descriptors</p>
                <p>• Computing average descriptor</p>
                <p>• Saving model</p>
            </div>
            
            <div class="progress-container">
                <div class="progress-bar">
                    <div class="progress-fill" id="trainProgress">0%</div>
                </div>
            </div>
            
            <div class="status" id="trainStatus"></div>
            
            <div class="controls">
                <button class="btn btn-secondary" onclick="goToStep2()">← Back</button>
                <button class="btn btn-primary" id="startTrainBtn" onclick="startTraining()">Start Training</button>
                <button class="btn btn-success" id="nextToTestBtn" onclick="goToStep4()" style="display:none;">Next: Test Recognition →</button>
            </div>
        </div>
        
        <!-- Step 4: Test Recognition -->
        <div class="section" id="step4">
            <h2>Step 4: Test Face Recognition</h2>
            <div class="info-box">
                <h3>Testing:</h3>
                <p>• Position your face in the camera</p>
                <p>• System will detect and recognize your face</p>
                <p>• Green box = Face detected</p>
                <p>• Recognition result will show your name and confidence</p>
            </div>
            
            <div class="video-container">
                <video id="testVideo" autoplay muted playsinline></video>
                <canvas id="testCanvas"></canvas>
            </div>
            
            <div class="status" id="testStatus"></div>
            
            <div class="info-box" id="recognitionResult" style="display:none;">
                <h3>Recognition Result:</h3>
                <p id="recognizedName"></p>
                <p id="recognizedConfidence"></p>
            </div>
            
            <div class="controls">
                <button class="btn btn-secondary" onclick="goToStep3()">← Back</button>
                <button class="btn btn-primary" id="startTestBtn" onclick="startTest()">Start Test</button>
                <button class="btn btn-success" onclick="finishRegistration()">Finish Registration ✓</button>
            </div>
        </div>
        
        <canvas id="hiddenCanvas" style="display:none;"></canvas>
    </div>

    <script src="../assets/js/utils.js?v=3"></script>
    <script src="../assets/js/webcam-controller.js?v=3"></script>
    <script>
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
        let modelsLoaded = false;
        let employeeData = {};
        let capturedImages = [];
        let webcamController = null;
        let testWebcamController = null;
        let isCapturing = false;
        let isTesting = false;
        
        // Load face-api models
        async function loadModels() {
            if (modelsLoaded) return;
            
            try {
                console.log('Loading face-api.js models...');
                await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                modelsLoaded = true;
                console.log('Models loaded successfully');
            } catch (error) {
                console.error('Error loading models:', error);
                throw error;
            }
        }
        
        // Initialize
        window.addEventListener('load', async () => {
            await loadModels();
        });
        
        // Step navigation
        function goToStep1() {
            showStep(1);
        }
        
        function goToStep2() {
            // Validate form
            const form = document.getElementById('employeeForm');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }
            
            // Save employee data
            employeeData = {
                employee_id: document.getElementById('employeeId').value,
                first_name: document.getElementById('firstName').value,
                last_name: document.getElementById('lastName').value,
                email: document.getElementById('email').value,
                department: document.getElementById('department').value
            };
            
            showStep(2);
        }
        
        function goToStep3() {
            showStep(3);
        }
        
        function goToStep4() {
            showStep(4);
        }
        
        function showStep(step) {
            // Hide all sections
            document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
            document.querySelectorAll('.step').forEach(s => {
                s.classList.remove('active');
                s.classList.remove('completed');
            });
            
            // Show current step
            document.getElementById(`step${step}`).classList.add('active');
            document.getElementById(`step${step}-indicator`).classList.add('active');
            
            // Mark previous steps as completed
            for (let i = 1; i < step; i++) {
                document.getElementById(`step${i}-indicator`).classList.add('completed');
            }
        }
        
        // Step 2: Capture Dataset
        async function startCapture() {
            try {
                const video = document.getElementById('video');
                const canvas = document.getElementById('canvas');
                const hiddenCanvas = document.getElementById('hiddenCanvas');
                const progressFill = document.getElementById('captureProgress');
                const capturedImagesDiv = document.getElementById('capturedImages');
                const statusDiv = document.getElementById('captureStatus');
                const startBtn = document.getElementById('startCaptureBtn');
                const nextBtn = document.getElementById('nextToTrainBtn');
                
                startBtn.disabled = true;
                capturedImages = [];
                capturedImagesDiv.innerHTML = '';
                
                // Start webcam
                if (!webcamController) {
                    webcamController = new WebcamController(video);
                }
                await webcamController.startCamera();
                
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                hiddenCanvas.width = 160;
                hiddenCanvas.height = 160;
                
                isCapturing = true;
                let captureCount = 0;
                const totalCaptures = 50;
                const captureInterval = 200; // ms
                
                statusDiv.className = 'status info';
                statusDiv.style.display = 'block';
                statusDiv.textContent = 'Capturing images... Please move your head slightly';
                
                const captureLoop = setInterval(async () => {
                    if (!isCapturing || captureCount >= totalCaptures) {
                        clearInterval(captureLoop);
                        if (captureCount >= totalCaptures) {
                            await saveDataset();
                            statusDiv.className = 'status success';
                            statusDiv.textContent = `✓ Successfully captured ${totalCaptures} images!`;
                            nextBtn.style.display = 'inline-block';
                        }
                        return;
                    }
                    
                    // Detect face
                    const detection = await faceapi
                        .detectSingleFace(video, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks();
                    
                    if (detection) {
                        // Capture image
                        const ctx = hiddenCanvas.getContext('2d');
                        ctx.drawImage(video, 0, 0, 160, 160);
                        const imageData = hiddenCanvas.toDataURL('image/jpeg', 0.8);
                        
                        capturedImages.push(imageData);
                        captureCount++;
                        
                        // Update progress
                        const progress = (captureCount / totalCaptures) * 100;
                        progressFill.style.width = `${progress}%`;
                        progressFill.textContent = `${captureCount}/${totalCaptures}`;
                        
                        // Show thumbnail
                        const img = document.createElement('img');
                        img.src = imageData;
                        img.className = 'captured-image';
                        capturedImagesDiv.appendChild(img);
                        
                        // Draw detection box
                        const canvasCtx = canvas.getContext('2d');
                        canvasCtx.clearRect(0, 0, canvas.width, canvas.height);
                        canvasCtx.strokeStyle = '#00ff00';
                        canvasCtx.lineWidth = 3;
                        const box = detection.detection.box;
                        canvasCtx.strokeRect(box.x, box.y, box.width, box.height);
                    }
                }, captureInterval);
                
            } catch (error) {
                console.error('Capture error:', error);
                document.getElementById('captureStatus').className = 'status error';
                document.getElementById('captureStatus').style.display = 'block';
                document.getElementById('captureStatus').textContent = 'Error: ' + error.message;
            }
        }
        
        async function saveDataset() {
            try {
                const response = await fetch('../api/save-dataset.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeData.employee_id,
                        images: capturedImages
                    })
                });
                
                const data = await response.json();
                if (!data.success) {
                    throw new Error(data.message);
                }
                
                console.log('Dataset saved successfully');
            } catch (error) {
                console.error('Error saving dataset:', error);
                throw error;
            }
        }
        
        // Step 3: Train Model
        async function startTraining() {
            try {
                const progressFill = document.getElementById('trainProgress');
                const statusDiv = document.getElementById('trainStatus');
                const startBtn = document.getElementById('startTrainBtn');
                const nextBtn = document.getElementById('nextToTestBtn');
                
                startBtn.disabled = true;
                statusDiv.className = 'status info';
                statusDiv.style.display = 'block';
                statusDiv.textContent = 'Training model with face-api.js... Loading images';
                
                progressFill.style.width = '10%';
                progressFill.textContent = '10%';
                
                // Load dataset images
                const datasetResponse = await fetch('../api/get-dataset-images.php?employee_id=' + employeeData.employee_id);
                const datasetData = await datasetResponse.json();
                
                if (!datasetData.success || !datasetData.images || datasetData.images.length === 0) {
                    throw new Error('No dataset images found');
                }
                
                statusDiv.textContent = `Processing ${datasetData.images.length} images...`;
                progressFill.style.width = '30%';
                progressFill.textContent = '30%';
                
                // Extract face descriptors from all images
                const descriptors = [];
                for (let i = 0; i < datasetData.images.length; i++) {
                    const imagePath = datasetData.images[i];
                    const img = await loadImage(imagePath);
                    
                    const detection = await faceapi
                        .detectSingleFace(img, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    
                    if (detection) {
                        descriptors.push(Array.from(detection.descriptor));
                    }
                    
                    const progress = 30 + (i / datasetData.images.length) * 50;
                    progressFill.style.width = `${progress}%`;
                    progressFill.textContent = `${Math.round(progress)}%`;
                }
                
                if (descriptors.length === 0) {
                    throw new Error('No faces detected in dataset images');
                }
                
                statusDiv.textContent = `Computing average descriptor from ${descriptors.length} faces...`;
                progressFill.style.width = '85%';
                progressFill.textContent = '85%';
                
                // Compute average descriptor
                const avgDescriptor = computeAverageDescriptor(descriptors);
                
                // Save model
                statusDiv.textContent = 'Saving model...';
                const saveResponse = await fetch('../api/save-model.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeData.employee_id,
                        descriptor: avgDescriptor,
                        num_samples: descriptors.length
                    })
                });
                
                const saveData = await saveResponse.json();
                if (!saveData.success) {
                    throw new Error(saveData.message || 'Failed to save model');
                }
                
                progressFill.style.width = '95%';
                progressFill.textContent = '95%';
                
                // Create employee record
                await createEmployee();
                
                progressFill.style.width = '100%';
                progressFill.textContent = '100%';
                
                statusDiv.className = 'status success';
                statusDiv.textContent = `✓ Model trained successfully with ${descriptors.length} face samples!`;
                nextBtn.style.display = 'inline-block';
                
            } catch (error) {
                console.error('Training error:', error);
                document.getElementById('trainStatus').className = 'status error';
                document.getElementById('trainStatus').textContent = 'Error: ' + error.message;
                document.getElementById('startTrainBtn').disabled = false;
            }
        }
        
        async function createEmployee() {
            const response = await fetch('../api/create-employee.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    ...employeeData,
                    is_model_available: 'True',
                    is_dataset_available: 'True',
                    dataset_image_count: capturedImages.length
                })
            });
            
            const data = await response.json();
            if (!data.success) {
                throw new Error(data.message);
            }
        }
        
        // Step 4: Test Recognition
        async function startTest() {
            try {
                const testVideo = document.getElementById('testVideo');
                const testCanvas = document.getElementById('testCanvas');
                const statusDiv = document.getElementById('testStatus');
                const resultDiv = document.getElementById('recognitionResult');
                const startBtn = document.getElementById('startTestBtn');
                
                startBtn.disabled = true;
                isTesting = true;
                
                if (!testWebcamController) {
                    testWebcamController = new WebcamController(testVideo);
                }
                await testWebcamController.startCamera();
                
                testCanvas.width = testVideo.videoWidth;
                testCanvas.height = testVideo.videoHeight;
                
                // Load employee models
                const modelsResponse = await fetch('../api/get-models.php');
                const modelsData = await modelsResponse.json();
                const employeeModels = modelsData.models || [];
                
                statusDiv.className = 'status info';
                statusDiv.style.display = 'block';
                statusDiv.textContent = 'Testing recognition... Position your face';
                
                const testLoop = async () => {
                    if (!isTesting) return;
                    
                    const detection = await faceapi
                        .detectSingleFace(testVideo, new faceapi.TinyFaceDetectorOptions())
                        .withFaceLandmarks()
                        .withFaceDescriptor();
                    
                    const ctx = testCanvas.getContext('2d');
                    ctx.clearRect(0, 0, testCanvas.width, testCanvas.height);
                    
                    if (detection) {
                        // Draw detection box
                        ctx.strokeStyle = '#00ff00';
                        ctx.lineWidth = 3;
                        const box = detection.detection.box;
                        ctx.strokeRect(box.x, box.y, box.width, box.height);
                        
                        // Try to recognize
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
                        
                        if (bestMatch && bestDistance < 0.6) {
                            const confidence = Math.max(0, (1 - bestDistance) * 100);
                            
                            resultDiv.style.display = 'block';
                            document.getElementById('recognizedName').textContent = `Name: ${bestMatch.name}`;
                            document.getElementById('recognizedConfidence').textContent = `Confidence: ${confidence.toFixed(1)}%`;
                            
                            statusDiv.className = 'status success';
                            statusDiv.textContent = `✓ Face recognized as ${bestMatch.name}!`;
                            
                            // Draw name on canvas
                            ctx.fillStyle = '#00ff00';
                            ctx.font = '20px Arial';
                            ctx.fillText(bestMatch.name, box.x, box.y - 10);
                        }
                    }
                    
                    setTimeout(testLoop, 100);
                };
                
                testLoop();
                
            } catch (error) {
                console.error('Test error:', error);
                document.getElementById('testStatus').className = 'status error';
                document.getElementById('testStatus').textContent = 'Error: ' + error.message;
            }
        }
        
        function finishRegistration() {
            if (confirm('Registration complete! Go to attendance page?')) {
                window.location.href = 'webcam-attendance-new.php';
            }
        }
        
        // Helper functions
        function loadImage(src) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = src;
            });
        }
        
        function computeAverageDescriptor(descriptors) {
            const numDescriptors = descriptors.length;
            const descriptorLength = descriptors[0].length;
            const avgDescriptor = new Array(descriptorLength).fill(0);
            
            for (let i = 0; i < numDescriptors; i++) {
                for (let j = 0; j < descriptorLength; j++) {
                    avgDescriptor[j] += descriptors[i][j];
                }
            }
            
            for (let j = 0; j < descriptorLength; j++) {
                avgDescriptor[j] /= numDescriptors;
            }
            
            return avgDescriptor;
        }
        
        function euclideanDistance(a, b) {
            let sum = 0;
            for (let i = 0; i < a.length; i++) {
                sum += Math.pow(a[i] - b[i], 2);
            }
            return Math.sqrt(sum);
        }
    </script>
</body>
</html>
