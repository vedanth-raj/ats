/**
 * AttendanceController - Manages attendance marking logic
 * Handles API calls for marking attendance and checking duplicates
 */

class AttendanceController {
    constructor() {
        this.apiBaseUrl = '/attendance-system/api';
    }
    
    /**
     * Mark attendance for an employee
     * @param {string} employeeId Employee ID
     * @returns {Promise<Object>} Result with success status and message
     */
    async markAttendance(employeeId) {
        try {
            const formData = new FormData();
            formData.append('employee_id', employeeId);
            
            const response = await fetch(`${this.apiBaseUrl}/process-attendance.php`, {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                console.log('Attendance marked successfully:', result);
                if (typeof displaySuccess === 'function') {
                    displaySuccess(result.message);
                }
            } else {
                console.error('Failed to mark attendance:', result.message);
                if (typeof displayError === 'function') {
                    displayError(result.message);
                }
            }
            
            return result;
            
        } catch (error) {
            console.error('Error marking attendance:', error);
            const errorResult = {
                success: false,
                message: 'Network error. Please check your connection and try again.'
            };
            
            if (typeof displayError === 'function') {
                displayError(errorResult.message);
            }
            
            return errorResult;
        }
    }
    
    /**
     * Check if attendance already marked for employee today
     * @param {string} employeeId Employee ID
     * @param {string|null} date Date in dd-mm-yyyy format (optional)
     * @returns {Promise<boolean>} True if duplicate exists
     */
    async checkDuplicate(employeeId, date = null) {
        try {
            const url = new URL(`${window.location.origin}${this.apiBaseUrl}/check-duplicate.php`);
            url.searchParams.append('employee_id', employeeId);
            if (date) {
                url.searchParams.append('date', date);
            }
            
            const response = await fetch(url);
            const result = await response.json();
            
            return result.duplicate || false;
            
        } catch (error) {
            console.error('Error checking duplicate:', error);
            return false;
        }
    }
    
    /**
     * Get today's attendance records
     * @returns {Promise<Array>} List of attendance records
     */
    async getTodayAttendance() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/get-attendance.php?type=today`);
            const result = await response.json();
            
            if (result.success) {
                return result.records || [];
            }
            
            return [];
            
        } catch (error) {
            console.error('Error fetching today\'s attendance:', error);
            return [];
        }
    }
    
    /**
     * Format time to hh:mm:ss AM/PM
     * @param {Date} date Date object
     * @returns {string} Formatted time
     */
    formatTime(date) {
        let hours = date.getHours();
        const minutes = date.getMinutes().toString().padStart(2, '0');
        const seconds = date.getSeconds().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM';
        
        hours = hours % 12;
        hours = hours ? hours : 12; // 0 should be 12
        hours = hours.toString().padStart(2, '0');
        
        return `${hours}:${minutes}:${seconds} ${ampm}`;
    }
    
    /**
     * Format date to dd-mm-yyyy
     * @param {Date} date Date object
     * @returns {string} Formatted date
     */
    formatDate(date) {
        const day = date.getDate().toString().padStart(2, '0');
        const month = (date.getMonth() + 1).toString().padStart(2, '0');
        const year = date.getFullYear();
        
        return `${day}-${month}-${year}`;
    }
    
    /**
     * Mark attendance with face recognition
     * @param {string} employeeId Employee ID
     * @param {string} employeeName Employee name
     * @param {number} confidence Recognition confidence
     * @returns {Promise<Object>} Result
     */
    async markAttendanceWithRecognition(employeeId, employeeName, confidence) {
        // Check for duplicate first
        const isDuplicate = await this.checkDuplicate(employeeId);
        
        if (isDuplicate) {
            const message = `Attendance already marked for ${employeeName} today`;
            if (typeof displayWarning === 'function') {
                displayWarning(message);
            }
            return {
                success: false,
                message: message,
                duplicate: true
            };
        }
        
        // Mark attendance
        const result = await this.markAttendance(employeeId);
        
        if (result.success) {
            result.employeeName = employeeName;
            result.confidence = confidence;
        }
        
        return result;
    }
    
    /**
     * Get attendance statistics for today
     * @returns {Promise<Object>} Statistics
     */
    async getTodayStatistics() {
        try {
            const response = await fetch(`${this.apiBaseUrl}/get-statistics.php?type=today`);
            const result = await response.json();
            
            if (result.success) {
                return result.statistics || {
                    total: 0,
                    present: 0,
                    absent: 0,
                    percentage: 0
                };
            }
            
            return {
                total: 0,
                present: 0,
                absent: 0,
                percentage: 0
            };
            
        } catch (error) {
            console.error('Error fetching statistics:', error);
            return {
                total: 0,
                present: 0,
                absent: 0,
                percentage: 0
            };
        }
    }
    
    /**
     * Display attendance confirmation
     * @param {Object} result Attendance result
     * @param {HTMLElement} element Element to display in
     */
    displayConfirmation(result, element) {
        if (!element) return;
        
        const now = new Date();
        const time = this.formatTime(now);
        const date = this.formatDate(now);
        
        element.innerHTML = `
            <div class="attendance-confirmation success">
                <div class="icon">✓</div>
                <div class="message">
                    <h3>Attendance Marked Successfully</h3>
                    <p><strong>${result.employeeName || 'Employee'}</strong></p>
                    <p>Time: ${time}</p>
                    <p>Date: ${date}</p>
                    ${result.confidence ? `<p>Confidence: ${(result.confidence * 100).toFixed(1)}%</p>` : ''}
                </div>
            </div>
        `;
        
        element.style.display = 'block';
        
        // Auto-hide after 3 seconds
        setTimeout(() => {
            element.style.display = 'none';
        }, 3000);
    }
    
    /**
     * Display error message
     * @param {string} message Error message
     * @param {HTMLElement} element Element to display in
     */
    displayError(message, element) {
        if (!element) return;
        
        element.innerHTML = `
            <div class="attendance-confirmation error">
                <div class="icon">✗</div>
                <div class="message">
                    <h3>Error</h3>
                    <p>${message}</p>
                </div>
            </div>
        `;
        
        element.style.display = 'block';
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            element.style.display = 'none';
        }, 5000);
    }
}
