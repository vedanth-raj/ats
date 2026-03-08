/**
 * FaceDetectionController - Detects faces in video frames using face-api.js
 * Provides real-time face detection with bounding boxes and landmarks
 */

class FaceDetectionController {
    constructor(modelPath = '/attendance-system/models/face-api') {
        this.modelPath = modelPath;
        this.modelsLoaded = false;
        this.detectionInterval = null;
        this.detectionFPS = 10;
        this.isDetecting = false;
    }
    
    /**
     * Load face-api.js models
     * @returns {Promise<void>}
     */
    async loadModels() {
        if (this.modelsLoaded) {
            console.log('Models already loaded');
            return;
        }
        
        try {
            console.log('Loading face detection models from:', this.modelPath);
            
            // Load required models for face detection
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(this.modelPath),
                faceapi.nets.faceLandmark68Net.loadFromUri(this.modelPath),
                faceapi.nets.faceRecognitionNet.loadFromUri(this.modelPath)
            ]);
            
            this.modelsLoaded = true;
            console.log('Face detection models loaded successfully');
            
        } catch (error) {
            console.error('Error loading face detection models:', error);
            throw new Error('Failed to load face detection models. Please check if models are available at: ' + this.modelPath);
        }
    }
    
    /**
     * Detect face in image or video element
     * @param {HTMLImageElement|HTMLVideoElement|HTMLCanvasElement} input Input element
     * @returns {Promise<Object|null>} Detection result or null if no face detected
     */
    async detectFace(input) {
        if (!this.modelsLoaded) {
            throw new Error('Models not loaded. Call loadModels() first.');
        }
        
        try {
            const detection = await faceapi
                .detectSingleFace(input, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks()
                .withFaceDescriptor();
            
            return detection || null;
            
        } catch (error) {
            console.error('Error detecting face:', error);
            return null;
        }
    }
    
    /**
     * Detect face with landmarks only (faster)
     * @param {HTMLImageElement|HTMLVideoElement|HTMLCanvasElement} input Input element
     * @returns {Promise<Object|null>} Detection result or null
     */
    async detectFaceWithLandmarks(input) {
        if (!this.modelsLoaded) {
            throw new Error('Models not loaded. Call loadModels() first.');
        }
        
        try {
            const detection = await faceapi
                .detectSingleFace(input, new faceapi.TinyFaceDetectorOptions())
                .withFaceLandmarks();
            
            return detection || null;
            
        } catch (error) {
            console.error('Error detecting face with landmarks:', error);
            return null;
        }
    }
    
    /**
     * Start continuous face detection loop
     * @param {HTMLVideoElement} videoElement Video element to detect from
     * @param {Function} callback Callback function called for each detection
     * @param {number} fps Frames per second (default: 10)
     */
    startDetectionLoop(videoElement, callback, fps = 10) {
        if (this.isDetecting) {
            console.warn('Detection loop already running');
            return;
        }
        
        if (!this.modelsLoaded) {
            throw new Error('Models not loaded. Call loadModels() first.');
        }
        
        this.detectionFPS = fps;
        this.isDetecting = true;
        
        const detect = async () => {
            if (!this.isDetecting) return;
            
            try {
                const detection = await this.detectFace(videoElement);
                callback(detection);
            } catch (error) {
                console.error('Error in detection loop:', error);
            }
            
            // Schedule next detection
            if (this.isDetecting) {
                setTimeout(detect, 1000 / this.detectionFPS);
            }
        };
        
        // Start detection loop
        detect();
        console.log(`Face detection loop started at ${fps} FPS`);
    }
    
    /**
     * Stop detection loop
     */
    stopDetectionLoop() {
        this.isDetecting = false;
        console.log('Face detection loop stopped');
    }
    
    /**
     * Draw detection bounding box on canvas
     * @param {HTMLCanvasElement} canvas Canvas element
     * @param {Object} detection Detection result
     * @param {string} color Box color (default: '#00ff00')
     * @param {number} lineWidth Line width (default: 2)
     */
    drawDetection(canvas, detection, color = '#00ff00', lineWidth = 2) {
        if (!detection) return;
        
        const ctx = canvas.getContext('2d');
        const box = detection.detection.box;
        
        // Draw bounding box
        ctx.strokeStyle = color;
        ctx.lineWidth = lineWidth;
        ctx.strokeRect(box.x, box.y, box.width, box.height);
        
        // Draw confidence score
        if (detection.detection.score) {
            const score = (detection.detection.score * 100).toFixed(1);
            ctx.fillStyle = color;
            ctx.font = '16px Arial';
            ctx.fillText(`${score}%`, box.x, box.y - 5);
        }
    }
    
    /**
     * Draw face landmarks on canvas
     * @param {HTMLCanvasElement} canvas Canvas element
     * @param {Object} detection Detection result with landmarks
     * @param {string} color Landmark color (default: '#ff0000')
     */
    drawLandmarks(canvas, detection, color = '#ff0000') {
        if (!detection || !detection.landmarks) return;
        
        const ctx = canvas.getContext('2d');
        const landmarks = detection.landmarks.positions;
        
        ctx.fillStyle = color;
        landmarks.forEach(point => {
            ctx.beginPath();
            ctx.arc(point.x, point.y, 2, 0, 2 * Math.PI);
            ctx.fill();
        });
    }
    
    /**
     * Check if face is centered in frame
     * @param {Object} detection Detection result
     * @param {number} videoWidth Video width
     * @param {number} videoHeight Video height
     * @param {number} tolerance Tolerance percentage (default: 0.3)
     * @returns {boolean} True if face is centered
     */
    isFaceCentered(detection, videoWidth, videoHeight, tolerance = 0.3) {
        if (!detection) return false;
        
        const box = detection.detection.box;
        const faceCenterX = box.x + box.width / 2;
        const faceCenterY = box.y + box.height / 2;
        
        const videoCenterX = videoWidth / 2;
        const videoCenterY = videoHeight / 2;
        
        const toleranceX = videoWidth * tolerance;
        const toleranceY = videoHeight * tolerance;
        
        return Math.abs(faceCenterX - videoCenterX) < toleranceX &&
               Math.abs(faceCenterY - videoCenterY) < toleranceY;
    }
    
    /**
     * Check if face is at appropriate distance (size)
     * @param {Object} detection Detection result
     * @param {number} videoWidth Video width
     * @param {number} videoHeight Video height
     * @param {number} minSize Minimum face size ratio (default: 0.15)
     * @param {number} maxSize Maximum face size ratio (default: 0.6)
     * @returns {boolean} True if face size is appropriate
     */
    isFaceSizeAppropriate(detection, videoWidth, videoHeight, minSize = 0.15, maxSize = 0.6) {
        if (!detection) return false;
        
        const box = detection.detection.box;
        const faceArea = box.width * box.height;
        const videoArea = videoWidth * videoHeight;
        const ratio = faceArea / videoArea;
        
        return ratio >= minSize && ratio <= maxSize;
    }
    
    /**
     * Get face guidance message
     * @param {Object} detection Detection result
     * @param {number} videoWidth Video width
     * @param {number} videoHeight Video height
     * @returns {string} Guidance message
     */
    getFaceGuidance(detection, videoWidth, videoHeight) {
        if (!detection) {
            return 'No face detected. Please position your face in the camera view.';
        }
        
        if (!this.isFaceSizeAppropriate(detection, videoWidth, videoHeight)) {
            const box = detection.detection.box;
            const faceArea = box.width * box.height;
            const videoArea = videoWidth * videoHeight;
            const ratio = faceArea / videoArea;
            
            if (ratio < 0.15) {
                return 'Please move closer to the camera.';
            } else {
                return 'Please move back from the camera.';
            }
        }
        
        if (!this.isFaceCentered(detection, videoWidth, videoHeight)) {
            return 'Please center your face in the frame.';
        }
        
        return 'Face detected. Hold still...';
    }
    
    /**
     * Extract face descriptor for recognition
     * @param {Object} detection Detection result
     * @returns {Float32Array|null} Face descriptor
     */
    extractDescriptor(detection) {
        if (!detection || !detection.descriptor) {
            return null;
        }
        return detection.descriptor;
    }
    
    /**
     * Set detection FPS
     * @param {number} fps Frames per second
     */
    setFPS(fps) {
        this.detectionFPS = fps;
    }
    
    /**
     * Get current FPS
     * @returns {number} Current FPS
     */
    getFPS() {
        return this.detectionFPS;
    }
}
