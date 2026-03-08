/**
 * Utility Functions
 * Common functions for displaying messages and handling UI
 */

/**
 * Display success message
 * @param {string} message Success message
 * @param {number} duration Duration in milliseconds (default: 3000)
 */
function displaySuccess(message, duration = 3000) {
    showMessage(message, 'success', duration);
}

/**
 * Display error message
 * @param {string} message Error message
 * @param {number} duration Duration in milliseconds (default: 5000)
 */
function displayError(message, duration = 5000) {
    showMessage(message, 'error', duration);
}

/**
 * Display warning message
 * @param {string} message Warning message
 * @param {number} duration Duration in milliseconds (default: 4000)
 */
function displayWarning(message, duration = 4000) {
    showMessage(message, 'warning', duration);
}

/**
 * Display info message
 * @param {string} message Info message
 * @param {number} duration Duration in milliseconds (default: 3000)
 */
function displayInfo(message, duration = 3000) {
    showMessage(message, 'info', duration);
}

/**
 * Show message with specified type
 * @param {string} message Message text
 * @param {string} type Message type (success, error, warning, info)
 * @param {number} duration Duration in milliseconds
 */
function showMessage(message, type, duration) {
    // Create message container if it doesn't exist
    let container = document.getElementById('message-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'message-container';
        container.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            max-width: 400px;
        `;
        document.body.appendChild(container);
    }
    
    // Create message element
    const messageEl = document.createElement('div');
    messageEl.className = `message message-${type}`;
    
    // Set colors based on type
    const colors = {
        success: { bg: '#d4edda', border: '#c3e6cb', text: '#155724' },
        error: { bg: '#f8d7da', border: '#f5c6cb', text: '#721c24' },
        warning: { bg: '#fff3cd', border: '#ffeaa7', text: '#856404' },
        info: { bg: '#d1ecf1', border: '#bee5eb', text: '#0c5460' }
    };
    
    const color = colors[type] || colors.info;
    
    messageEl.style.cssText = `
        background-color: ${color.bg};
        border: 1px solid ${color.border};
        color: ${color.text};
        padding: 15px 20px;
        margin-bottom: 10px;
        border-radius: 4px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        animation: slideIn 0.3s ease-out;
    `;
    
    // Add icon
    const icons = {
        success: '✓',
        error: '✗',
        warning: '⚠',
        info: 'ℹ'
    };
    
    messageEl.innerHTML = `
        <span style="font-weight: bold; margin-right: 10px;">${icons[type]}</span>
        <span>${message}</span>
    `;
    
    container.appendChild(messageEl);
    
    // Auto-remove after duration
    setTimeout(() => {
        messageEl.style.animation = 'slideOut 0.3s ease-out';
        setTimeout(() => {
            container.removeChild(messageEl);
        }, 300);
    }, duration);
}

/**
 * Show loading indicator
 * @param {string} message Loading message
 * @returns {HTMLElement} Loading element
 */
function showLoading(message = 'Loading...') {
    const loading = document.createElement('div');
    loading.id = 'loading-indicator';
    loading.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 10001;
    `;
    
    loading.innerHTML = `
        <div style="background: white; padding: 30px; border-radius: 8px; text-align: center;">
            <div class="spinner" style="
                border: 4px solid #f3f3f3;
                border-top: 4px solid #3498db;
                border-radius: 50%;
                width: 40px;
                height: 40px;
                animation: spin 1s linear infinite;
                margin: 0 auto 15px;
            "></div>
            <p style="margin: 0; color: #333;">${message}</p>
        </div>
    `;
    
    document.body.appendChild(loading);
    return loading;
}

/**
 * Hide loading indicator
 */
function hideLoading() {
    const loading = document.getElementById('loading-indicator');
    if (loading) {
        loading.remove();
    }
}

/**
 * Add CSS animations
 */
if (!document.getElementById('utils-styles')) {
    const style = document.createElement('style');
    style.id = 'utils-styles';
    style.textContent = `
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
}
