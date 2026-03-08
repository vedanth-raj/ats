/**
 * FaceRecognitionController - Identifies employees by comparing face descriptors
 * Uses Euclidean distance to match detected faces against trained models
 */

class FaceRecognitionController {
    constructor(threshold = 0.6) {
        this.threshold = threshold;
        this.employeeModels = new Map();
        this.modelsLoaded = false;
    }
    
    /**
     * Load all employee face models from server
     * @returns {Promise<number>} Number of models loaded
     */
    async loadEmployeeModels() {
        try {
            console.log('Loading employee face models...');
            
            // Fetch list of available models
            const response = await fetch('/attendance-system/api/get-models.php');
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to fetch model list');
            }
            
            this.employeeModels.clear();
            
            // Load each model
            for (const modelInfo of result.models) {
                try {
                    const modelResponse = await fetch(modelInfo.path);
                    const modelData = await modelResponse.json();
                    
                    // Convert descriptor array to Float32Array
                    const descriptor = new Float32Array(modelData.descriptor);
                    
                    this.employeeModels.set(modelInfo.employee_id, {
                        employeeId: modelInfo.employee_id,
                        employeeName: modelInfo.employee_name,
                        descriptor: descriptor,
                        trainingDate: modelData.training_date,
                        imageCount: modelData.image_count
                    });
                    
                    console.log(`Loaded model for ${modelInfo.employee_name} (${modelInfo.employee_id})`);
                    
                } catch (error) {
                    console.error(`Error loading model for ${modelInfo.employee_id}:`, error);
                }
            }
            
            this.modelsLoaded = true;
            console.log(`Loaded ${this.employeeModels.size} employee models`);
            
            return this.employeeModels.size;
            
        } catch (error) {
            console.error('Error loading employee models:', error);
            throw error;
        }
    }
    
    /**
     * Recognize face by comparing descriptor against all employee models
     * @param {Float32Array} descriptor Face descriptor from detection
     * @returns {Object|null} Match result with employeeId, name, and confidence, or null if no match
     */
    recognizeFace(descriptor) {
        if (!this.modelsLoaded || this.employeeModels.size === 0) {
            console.warn('No employee models loaded');
            return null;
        }
        
        if (!descriptor || descriptor.length === 0) {
            return null;
        }
        
        let bestMatch = null;
        let bestDistance = Infinity;
        
        // Compare against all employee models
        for (const [employeeId, model] of this.employeeModels) {
            const distance = this.computeDistance(descriptor, model.descriptor);
            
            if (distance < bestDistance) {
                bestDistance = distance;
                bestMatch = {
                    employeeId: employeeId,
                    employeeName: model.employeeName,
                    distance: distance,
                    confidence: this.distanceToConfidence(distance)
                };
            }
        }
        
        // Check if best match meets threshold
        if (bestMatch && bestMatch.confidence >= this.threshold) {
            console.log(`Face recognized: ${bestMatch.employeeName} (confidence: ${(bestMatch.confidence * 100).toFixed(1)}%)`);
            return bestMatch;
        }
        
        console.log(`No match found above threshold (best confidence: ${bestMatch ? (bestMatch.confidence * 100).toFixed(1) : 0}%)`);
        return null;
    }
    
    /**
     * Compute Euclidean distance between two descriptors
     * @param {Float32Array} desc1 First descriptor
     * @param {Float32Array} desc2 Second descriptor
     * @returns {number} Euclidean distance
     */
    computeDistance(desc1, desc2) {
        if (desc1.length !== desc2.length) {
            throw new Error('Descriptor lengths do not match');
        }
        
        let sum = 0;
        for (let i = 0; i < desc1.length; i++) {
            const diff = desc1[i] - desc2[i];
            sum += diff * diff;
        }
        
        return Math.sqrt(sum);
    }
    
    /**
     * Convert distance to confidence score (0-1)
     * @param {number} distance Euclidean distance
     * @returns {number} Confidence score
     */
    distanceToConfidence(distance) {
        // Typical face recognition distance ranges from 0 to 1
        // Lower distance = higher confidence
        // Using exponential decay for confidence calculation
        return Math.exp(-distance * 2);
    }
    
    /**
     * Set recognition threshold
     * @param {number} threshold Confidence threshold (0-1)
     */
    setThreshold(threshold) {
        if (threshold < 0 || threshold > 1) {
            throw new Error('Threshold must be between 0 and 1');
        }
        this.threshold = threshold;
        console.log(`Recognition threshold set to ${threshold}`);
    }
    
    /**
     * Get current threshold
     * @returns {number} Current threshold
     */
    getThreshold() {
        return this.threshold;
    }
    
    /**
     * Get number of loaded models
     * @returns {number} Number of models
     */
    getModelCount() {
        return this.employeeModels.size;
    }
    
    /**
     * Get list of loaded employee IDs
     * @returns {Array<string>} Employee IDs
     */
    getLoadedEmployees() {
        return Array.from(this.employeeModels.keys());
    }
    
    /**
     * Check if model exists for employee
     * @param {string} employeeId Employee ID
     * @returns {boolean} True if model exists
     */
    hasModel(employeeId) {
        return this.employeeModels.has(employeeId);
    }
    
    /**
     * Get model info for employee
     * @param {string} employeeId Employee ID
     * @returns {Object|null} Model info or null
     */
    getModelInfo(employeeId) {
        const model = this.employeeModels.get(employeeId);
        if (!model) return null;
        
        return {
            employeeId: model.employeeId,
            employeeName: model.employeeName,
            trainingDate: model.trainingDate,
            imageCount: model.imageCount,
            descriptorLength: model.descriptor.length
        };
    }
    
    /**
     * Reload models from server
     * @returns {Promise<number>} Number of models loaded
     */
    async reloadModels() {
        this.employeeModels.clear();
        this.modelsLoaded = false;
        return await this.loadEmployeeModels();
    }
    
    /**
     * Recognize face with detailed results
     * @param {Float32Array} descriptor Face descriptor
     * @param {number} topN Number of top matches to return
     * @returns {Array<Object>} Top N matches sorted by confidence
     */
    recognizeFaceDetailed(descriptor, topN = 3) {
        if (!this.modelsLoaded || this.employeeModels.size === 0) {
            return [];
        }
        
        if (!descriptor || descriptor.length === 0) {
            return [];
        }
        
        const matches = [];
        
        // Compare against all employee models
        for (const [employeeId, model] of this.employeeModels) {
            const distance = this.computeDistance(descriptor, model.descriptor);
            const confidence = this.distanceToConfidence(distance);
            
            matches.push({
                employeeId: employeeId,
                employeeName: model.employeeName,
                distance: distance,
                confidence: confidence,
                meetsThreshold: confidence >= this.threshold
            });
        }
        
        // Sort by confidence (descending)
        matches.sort((a, b) => b.confidence - a.confidence);
        
        // Return top N matches
        return matches.slice(0, topN);
    }
    
    /**
     * Batch recognize multiple faces
     * @param {Array<Float32Array>} descriptors Array of face descriptors
     * @returns {Array<Object|null>} Array of recognition results
     */
    batchRecognize(descriptors) {
        return descriptors.map(descriptor => this.recognizeFace(descriptor));
    }
    
    /**
     * Clear all loaded models
     */
    clearModels() {
        this.employeeModels.clear();
        this.modelsLoaded = false;
        console.log('All employee models cleared');
    }
}
