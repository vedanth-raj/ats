<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Train Face Recognition Models</title>
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
        
        .employee-section {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .employee-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .employee-info h3 {
            color: #333;
            margin-bottom: 5px;
        }
        
        .employee-info p {
            color: #666;
            font-size: 14px;
        }
        
        .train-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .train-btn:hover {
            background: #5568d3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .train-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .progress-container {
            margin-top: 15px;
            display: none;
        }
        
        .progress-bar {
            width: 100%;
            height: 30px;
            background: #f0f0f0;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 10px;
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
        
        .progress-text {
            color: #666;
            font-size: 14px;
        }
        
        .status {
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
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
        
        .hidden-canvas {
            display: none;
        }
        
        .model-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        
        .model-info h4 {
            color: #333;
            margin-bottom: 10px;
        }
        
        .model-info p {
            color: #666;
            font-size: 14px;
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎓 Train Face Recognition Models</h1>
        <p class="subtitle">Train models from existing datasets using face-api.js</p>
        
        <div id="employees-container"></div>
        
        <canvas id="hidden-canvas" class="hidden-canvas"></canvas>
    </div>

    <script>
        const MODEL_URL = 'https://cdn.jsdelivr.net/npm/@vladmandic/face-api/model';
        let modelsLoaded = false;

        // Load face-api.js models
        async function loadModels() {
            try {
                console.log('Loading face-api.js models from CDN...');
                console.log('Model URL:', MODEL_URL);
                
                // Load models with error handling for each
                console.log('Loading SSD MobileNet v1...');
                await faceapi.nets.ssdMobilenetv1.loadFromUri(MODEL_URL);
                console.log('✓ SSD MobileNet v1 loaded');
                
                console.log('Loading Face Landmark 68...');
                await faceapi.nets.faceLandmark68Net.loadFromUri(MODEL_URL);
                console.log('✓ Face Landmark 68 loaded');
                
                console.log('Loading Face Recognition...');
                await faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL);
                console.log('✓ Face Recognition loaded');
                
                modelsLoaded = true;
                console.log('All models loaded successfully');
                return true;
            } catch (error) {
                console.error('Error loading models:', error);
                alert('Failed to load face-api.js models from CDN. Please check your internet connection and try again.');
                return false;
            }
        }

        // Get list of employees with datasets
        async function getEmployeesWithDatasets() {
            try {
                const response = await fetch('../api/get-employees-with-datasets.php');
                const data = await response.json();
                return data.employees || [];
            } catch (error) {
                console.error('Error fetching employees:', error);
                return [];
            }
        }

        // Train model for an employee
        async function trainModel(employeeId) {
            const progressContainer = document.getElementById(`progress-${employeeId}`);
            const progressFill = document.getElementById(`progress-fill-${employeeId}`);
            const progressText = document.getElementById(`progress-text-${employeeId}`);
            const statusDiv = document.getElementById(`status-${employeeId}`);
            const trainBtn = document.getElementById(`train-btn-${employeeId}`);
            
            progressContainer.style.display = 'block';
            statusDiv.style.display = 'none';
            trainBtn.disabled = true;
            
            try {
                // Get list of images in dataset
                const response = await fetch(`../api/get-dataset-images.php?employee_id=${employeeId}`);
                const data = await response.json();
                
                if (!data.success || !data.images || data.images.length === 0) {
                    throw new Error('No images found in dataset');
                }
                
                const images = data.images;
                console.log(`Found ${images.length} images for employee ${employeeId}`);
                
                const descriptors = [];
                const canvas = document.getElementById('hidden-canvas');
                const ctx = canvas.getContext('2d');
                
                // Process each image
                for (let i = 0; i < images.length; i++) {
                    const imagePath = images[i];
                    progressText.textContent = `Processing image ${i + 1}/${images.length}...`;
                    progressFill.style.width = `${((i + 1) / images.length) * 100}%`;
                    progressFill.textContent = `${Math.round(((i + 1) / images.length) * 100)}%`;
                    
                    try {
                        // Load image
                        const img = await loadImage(imagePath);
                        canvas.width = img.width;
                        canvas.height = img.height;
                        ctx.drawImage(img, 0, 0);
                        
                        // Detect face and extract descriptor
                        const detection = await faceapi
                            .detectSingleFace(canvas)
                            .withFaceLandmarks()
                            .withFaceDescriptor();
                        
                        if (detection) {
                            descriptors.push(Array.from(detection.descriptor));
                            console.log(`✓ Extracted descriptor from image ${i + 1}`);
                        } else {
                            console.warn(`⚠ No face detected in image ${i + 1}`);
                        }
                    } catch (error) {
                        console.error(`Error processing image ${i + 1}:`, error);
                    }
                }
                
                if (descriptors.length === 0) {
                    throw new Error('No face descriptors extracted from any image');
                }
                
                console.log(`Successfully extracted ${descriptors.length} descriptors`);
                
                // Compute average descriptor
                const avgDescriptor = computeAverageDescriptor(descriptors);
                
                // Save model
                progressText.textContent = 'Saving model...';
                const saveResponse = await fetch('../api/save-model.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        descriptor: avgDescriptor,
                        num_samples: descriptors.length,
                        all_descriptors: descriptors
                    })
                });
                
                const saveData = await saveResponse.json();
                
                if (!saveData.success) {
                    throw new Error(saveData.message || 'Failed to save model');
                }
                
                // Show success
                statusDiv.className = 'status success';
                statusDiv.textContent = `✓ Model trained successfully! (${descriptors.length} samples)`;
                statusDiv.style.display = 'block';
                
                // Update employee record
                await fetch('../api/update-employee-model-status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        employee_id: employeeId,
                        is_model_available: 'True'
                    })
                });
                
                console.log(`Model trained for employee ${employeeId}`);
                
            } catch (error) {
                console.error('Training error:', error);
                statusDiv.className = 'status error';
                statusDiv.textContent = `✗ Error: ${error.message}`;
                statusDiv.style.display = 'block';
                trainBtn.disabled = false;
            }
        }

        // Helper function to load image
        function loadImage(src) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => resolve(img);
                img.onerror = reject;
                img.src = src;
            });
        }

        // Compute average descriptor
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

        // Render employees
        function renderEmployees(employees) {
            const container = document.getElementById('employees-container');
            
            if (employees.length === 0) {
                container.innerHTML = '<p style="color: #666;">No employees with datasets found.</p>';
                return;
            }
            
            employees.forEach(emp => {
                const section = document.createElement('div');
                section.className = 'employee-section';
                section.innerHTML = `
                    <div class="employee-header">
                        <div class="employee-info">
                            <h3>${emp.name}</h3>
                            <p>Employee ID: ${emp.employee_id} | Dataset: ${emp.dataset_count} images</p>
                        </div>
                        <button class="train-btn" id="train-btn-${emp.employee_id}" onclick="trainModel('${emp.employee_id}')">
                            Train Model
                        </button>
                    </div>
                    <div class="progress-container" id="progress-${emp.employee_id}">
                        <div class="progress-bar">
                            <div class="progress-fill" id="progress-fill-${emp.employee_id}">0%</div>
                        </div>
                        <p class="progress-text" id="progress-text-${emp.employee_id}">Initializing...</p>
                    </div>
                    <div class="status" id="status-${emp.employee_id}"></div>
                `;
                container.appendChild(section);
            });
        }

        // Initialize
        async function init() {
            console.log('Initializing training page...');
            
            // Load face-api.js models
            const loaded = await loadModels();
            if (!loaded) {
                alert('Failed to load face-api.js models. Please refresh the page.');
                return;
            }
            
            // Get employees with datasets
            const employees = await getEmployeesWithDatasets();
            renderEmployees(employees);
        }

        // Start when page loads
        window.addEventListener('load', init);
    </script>
</body>
</html>
