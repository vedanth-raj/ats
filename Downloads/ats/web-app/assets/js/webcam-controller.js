/**
 * WebcamController - Manages webcam access and video stream lifecycle
 * Handles camera permissions, device selection, and stream management
 */

class WebcamController {
    constructor(videoElement) {
        this.videoElement = videoElement;
        this.stream = null;
        this.devices = [];
        this.currentDeviceId = null;
    }
    
    /**
     * Start camera with specified constraints
     * @param {Object} constraints MediaStream constraints
     * @returns {Promise<MediaStream>} Video stream
     */
    async startCamera(constraints = { video: true }) {
        try {
            // Check if MediaDevices API is supported
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('MediaDevices API not supported in this browser');
            }
            
            // If specific device is selected, use it
            if (this.currentDeviceId) {
                constraints.video = {
                    deviceId: { exact: this.currentDeviceId }
                };
            }
            
            // Request camera access
            this.stream = await navigator.mediaDevices.getUserMedia(constraints);
            
            // Attach stream to video element
            this.videoElement.srcObject = this.stream;
            this.videoElement.play();
            
            console.log('Camera started successfully');
            return this.stream;
            
        } catch (error) {
            console.error('Error starting camera:', error);
            this.handleCameraError(error);
            throw error;
        }
    }
    
    /**
     * Stop camera and release all tracks
     */
    stopCamera() {
        if (this.stream) {
            this.stream.getTracks().forEach(track => {
                track.stop();
            });
            this.stream = null;
            this.videoElement.srcObject = null;
            console.log('Camera stopped');
        }
    }
    
    /**
     * Capture current frame from video
     * @returns {ImageData} Image data from current frame
     */
    captureFrame() {
        if (!this.stream || !this.videoElement) {
            throw new Error('Camera not active');
        }
        
        const canvas = document.createElement('canvas');
        canvas.width = this.videoElement.videoWidth;
        canvas.height = this.videoElement.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.videoElement, 0, 0, canvas.width, canvas.height);
        
        return ctx.getImageData(0, 0, canvas.width, canvas.height);
    }
    
    /**
     * Capture frame as blob (for upload)
     * @param {string} format Image format (default: 'image/jpeg')
     * @param {number} quality Image quality 0-1 (default: 0.92)
     * @returns {Promise<Blob>} Image blob
     */
    async captureFrameAsBlob(format = 'image/jpeg', quality = 0.92) {
        if (!this.stream || !this.videoElement) {
            throw new Error('Camera not active');
        }
        
        const canvas = document.createElement('canvas');
        canvas.width = this.videoElement.videoWidth;
        canvas.height = this.videoElement.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.videoElement, 0, 0, canvas.width, canvas.height);
        
        return new Promise((resolve) => {
            canvas.toBlob(resolve, format, quality);
        });
    }
    
    /**
     * Check if camera is active
     * @returns {boolean} True if camera is active
     */
    isActive() {
        return this.stream !== null && this.stream.active;
    }
    
    /**
     * Get list of available camera devices
     * @returns {Promise<Array>} List of camera devices
     */
    async getDevices() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            this.devices = devices.filter(device => device.kind === 'videoinput');
            return this.devices;
        } catch (error) {
            console.error('Error enumerating devices:', error);
            return [];
        }
    }
    
    /**
     * Switch to a different camera device
     * @param {string} deviceId Device ID to switch to
     * @returns {Promise<MediaStream>} New video stream
     */
    async switchCamera(deviceId) {
        this.stopCamera();
        this.currentDeviceId = deviceId;
        return await this.startCamera();
    }
    
    /**
     * Handle camera errors with user-friendly messages
     * @param {Error} error Camera error
     */
    handleCameraError(error) {
        let message = 'Camera error: ';
        
        switch (error.name) {
            case 'NotAllowedError':
            case 'PermissionDeniedError':
                message += 'Camera access denied. Please enable camera permissions in your browser settings.';
                break;
                
            case 'NotFoundError':
            case 'DevicesNotFoundError':
                message += 'No camera found. Please connect a webcam and try again.';
                break;
                
            case 'NotReadableError':
            case 'TrackStartError':
                message += 'Camera is in use by another application. Please close other apps and try again.';
                break;
                
            case 'OverconstrainedError':
            case 'ConstraintNotSatisfiedError':
                message += 'Camera does not support the requested settings.';
                break;
                
            case 'TypeError':
                message += 'Invalid camera configuration.';
                break;
                
            default:
                message += error.message || 'Unknown error occurred.';
        }
        
        console.error(message);
        
        // Display error to user if displayError function exists
        if (typeof displayError === 'function') {
            displayError(message);
        }
    }
    
    /**
     * Get current video dimensions
     * @returns {Object} Width and height
     */
    getVideoDimensions() {
        return {
            width: this.videoElement.videoWidth,
            height: this.videoElement.videoHeight
        };
    }
    
    /**
     * Take a snapshot and download it
     * @param {string} filename Filename for download
     */
    downloadSnapshot(filename = 'snapshot.jpg') {
        const canvas = document.createElement('canvas');
        canvas.width = this.videoElement.videoWidth;
        canvas.height = this.videoElement.videoHeight;
        
        const ctx = canvas.getContext('2d');
        ctx.drawImage(this.videoElement, 0, 0, canvas.width, canvas.height);
        
        canvas.toBlob((blob) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = filename;
            a.click();
            URL.revokeObjectURL(url);
        }, 'image/jpeg', 0.95);
    }
}

// Auto-cleanup on page unload
window.addEventListener('beforeunload', () => {
    // Stop all active webcam controllers
    if (window.webcamController && window.webcamController.isActive()) {
        window.webcamController.stopCamera();
    }
});
