/**
 * AI Assistant JavaScript
 * HR Leave Assistant - PHP/MySQL Version
 */

class AIAssistant {
    constructor(config) {
        this.config = config;
        this.currentRequest = null;
        this.lastInput = '';
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadLastConversation();
    }
    
    bindEvents() {
        // Form submission
        const form = document.getElementById(this.config.formId);
        if (form) {
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                this.generateResponse();
            });
        }
        
        // Clear button
        const clearBtn = document.getElementById(this.config.clearBtnId);
        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                this.clearForm();
            });
        }
        
        // Copy button
        const copyBtn = document.getElementById(this.config.copyBtnId);
        if (copyBtn) {
            copyBtn.addEventListener('click', () => {
                this.copyResponse();
            });
        }
        
        // Print button
        const printBtn = document.getElementById(this.config.printBtnId);
        if (printBtn) {
            printBtn.addEventListener('click', () => {
                this.printResponse();
            });
        }
        
        // Regenerate button
        const regenerateBtn = document.getElementById(this.config.regenerateBtnId);
        if (regenerateBtn) {
            regenerateBtn.addEventListener('click', () => {
                this.regenerateResponse();
            });
        }
        
        // Retry button
        const retryBtn = document.getElementById(this.config.retryBtnId);
        if (retryBtn) {
            retryBtn.addEventListener('click', () => {
                this.generateResponse();
            });
        }
        
        // Auto-save input
        const input = document.getElementById(this.config.inputId);
        if (input) {
            input.addEventListener('input', () => {
                this.saveInput();
            });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.ctrlKey || e.metaKey) {
                switch (e.key) {
                    case 'Enter':
                        e.preventDefault();
                        this.generateResponse();
                        break;
                    case 'k':
                        e.preventDefault();
                        this.clearForm();
                        break;
                    case 'c':
                        if (this.isResponseVisible()) {
                            e.preventDefault();
                            this.copyResponse();
                        }
                        break;
                    case 'p':
                        if (this.isResponseVisible()) {
                            e.preventDefault();
                            this.printResponse();
                        }
                        break;
                }
            }
        });
    }
    
    async generateResponse() {
        const input = document.getElementById(this.config.inputId);
        const inputText = input.value.trim();
        
        if (!inputText) {
            this.showError('Please enter a leave request or situation description.');
            return;
        }
        
        // Abort previous request if still running
        if (this.currentRequest) {
            this.currentRequest.abort();
        }
        
        this.showLoading();
        this.hideError();
        this.hideResponse();
        
        try {
            // Create abort controller for this request
            const controller = new AbortController();
            this.currentRequest = controller;
            
            const response = await fetch('/api/ai.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    tool_name: this.config.toolName,
                    input_text: inputText
                }),
                signal: controller.signal
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.error || `HTTP ${response.status}`);
            }
            
            if (data.success) {
                this.showResponse(data.response, inputText);
                this.saveConversation(inputText, data.response);
            } else {
                throw new Error(data.error || 'Failed to generate response');
            }
            
        } catch (error) {
            if (error.name === 'AbortError') {
                console.log('Request aborted');
                return;
            }
            
            console.error('AI request failed:', error);
            this.showError(this.getErrorMessage(error));
        } finally {
            this.hideLoading();
            this.currentRequest = null;
        }
    }
    
    regenerateResponse() {
        const input = document.getElementById(this.config.inputId);
        if (input.value.trim()) {
            this.generateResponse();
        }
    }
    
    clearForm() {
        const input = document.getElementById(this.config.inputId);
        input.value = '';
        input.focus();
        
        this.hideResponse();
        this.hideError();
        this.clearSavedInput();
    }
    
    async copyResponse() {
        const responseContent = document.getElementById(this.config.responseContent);
        const copyBtn = document.getElementById(this.config.copyBtnId);
        
        if (!responseContent) return;
        
        try {
            // Get text content without HTML
            const textContent = responseContent.innerText || responseContent.textContent;
            
            await navigator.clipboard.writeText(textContent);
            
            // Visual feedback
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '✅ Copied!';
            copyBtn.classList.add('copy-success');
            
            setTimeout(() => {
                copyBtn.innerHTML = originalText;
                copyBtn.classList.remove('copy-success');
            }, 2000);
            
        } catch (error) {
            console.error('Failed to copy:', error);
            
            // Fallback: select text
            const range = document.createRange();
            range.selectNodeContents(responseContent);
            const selection = window.getSelection();
            selection.removeAllRanges();
            selection.addRange(range);
            
            // Show fallback message
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '📋 Text Selected';
            setTimeout(() => {
                copyBtn.innerHTML = originalText;
            }, 2000);
        }
    }
    
    printResponse() {
        // Focus on response content for printing
        const responseContent = document.getElementById(this.config.responseContent);
        if (responseContent) {
            responseContent.focus();
        }
        
        window.print();
    }
    
    showResponse(responseText, inputText) {
        const responseSection = document.getElementById(this.config.responseSection);
        const responseContent = document.getElementById(this.config.responseContent);
        
        if (responseSection && responseContent) {
            // Format the response
            responseContent.innerHTML = this.formatResponse(responseText);
            
            // Show the section
            responseSection.style.display = 'block';
            
            // Scroll to response
            responseSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            
            // Store for regeneration
            this.lastInput = inputText;
        }
    }
    
    hideResponse() {
        const responseSection = document.getElementById(this.config.responseSection);
        if (responseSection) {
            responseSection.style.display = 'none';
        }
    }
    
    showError(message) {
        const errorSection = document.getElementById(this.config.errorSection);
        const errorMessage = document.getElementById(this.config.errorMessage);
        
        if (errorSection && errorMessage) {
            errorMessage.textContent = message;
            errorSection.style.display = 'block';
            
            // Scroll to error
            errorSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
        }
    }
    
    hideError() {
        const errorSection = document.getElementById(this.config.errorSection);
        if (errorSection) {
            errorSection.style.display = 'none';
        }
    }
    
    showLoading() {
        const generateBtn = document.getElementById(this.config.generateBtnId);
        const loadingOverlay = document.getElementById(this.config.loadingOverlay);
        
        if (generateBtn) {
            generateBtn.classList.add('generating');
            generateBtn.disabled = true;
        }
        
        if (loadingOverlay) {
            loadingOverlay.style.display = 'flex';
        }
    }
    
    hideLoading() {
        const generateBtn = document.getElementById(this.config.generateBtnId);
        const loadingOverlay = document.getElementById(this.config.loadingOverlay);
        
        if (generateBtn) {
            generateBtn.classList.remove('generating');
            generateBtn.disabled = false;
        }
        
        if (loadingOverlay) {
            loadingOverlay.style.display = 'none';
        }
    }
    
    formatResponse(text) {
        // Convert markdown-like formatting to HTML
        let formatted = text
            // Headers
            .replace(/^### (.*$)/gm, '<h3>$1</h3>')
            .replace(/^## (.*$)/gm, '<h3>$1</h3>')
            .replace(/^# (.*$)/gm, '<h3>$1</h3>')
            
            // Bold text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/__(.*?)__/g, '<strong>$1</strong>')
            
            // Italic text
            .replace(/\*(.*?)\*/g, '<em>$1</em>')
            .replace(/_(.*?)_/g, '<em>$1</em>')
            
            // Line breaks
            .replace(/\n\n/g, '</p><p>')
            .replace(/\n/g, '<br>')
            
            // Lists
            .replace(/^\* (.*$)/gm, '<li>$1</li>')
            .replace(/^- (.*$)/gm, '<li>$1</li>')
            .replace(/^\d+\. (.*$)/gm, '<li>$1</li>');
        
        // Wrap in paragraphs
        if (!formatted.startsWith('<h3>') && !formatted.startsWith('<li>')) {
            formatted = '<p>' + formatted + '</p>';
        }
        
        // Wrap lists in ul tags
        formatted = formatted.replace(/(<li>.*?<\/li>)/gs, (match) => {
            return '<ul>' + match + '</ul>';
        });
        
        // Clean up empty paragraphs
        formatted = formatted.replace(/<p><\/p>/g, '');
        
        return formatted;
    }
    
    getErrorMessage(error) {
        if (error.message.includes('API key')) {
            return 'AI service is not configured. Please contact the administrator.';
        } else if (error.message.includes('rate limit')) {
            return 'Too many requests. Please wait a moment and try again.';
        } else if (error.message.includes('network') || error.message.includes('fetch')) {
            return 'Network error. Please check your connection and try again.';
        } else if (error.message.includes('timeout')) {
            return 'Request timed out. Please try again.';
        } else {
            return error.message || 'An unexpected error occurred. Please try again.';
        }
    }
    
    isResponseVisible() {
        const responseSection = document.getElementById(this.config.responseSection);
        return responseSection && responseSection.style.display !== 'none';
    }
    
    saveInput() {
        const input = document.getElementById(this.config.inputId);
        if (input) {
            localStorage.setItem(`hrla_${this.config.toolName}_input`, input.value);
        }
    }
    
    loadSavedInput() {
        const input = document.getElementById(this.config.inputId);
        const saved = localStorage.getItem(`hrla_${this.config.toolName}_input`);
        
        if (input && saved) {
            input.value = saved;
        }
    }
    
    clearSavedInput() {
        localStorage.removeItem(`hrla_${this.config.toolName}_input`);
    }
    
    saveConversation(input, response) {
        const conversation = {
            input: input,
            response: response,
            timestamp: new Date().toISOString(),
            tool: this.config.toolName
        };
        
        localStorage.setItem(`hrla_${this.config.toolName}_last`, JSON.stringify(conversation));
    }
    
    loadLastConversation() {
        // Load saved input
        this.loadSavedInput();
        
        // Optionally load last conversation (for now just input)
        // Could be extended to show last response as well
    }
}

// Utility functions
function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="this.parentElement.parentElement.remove()">&times;</button>
        </div>
    `;
    
    // Add to page
    document.body.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.remove();
        }
    }, 5000);
}

// Add toast styles if not already present
if (!document.querySelector('#toast-styles')) {
    const toastStyles = document.createElement('style');
    toastStyles.id = 'toast-styles';
    toastStyles.textContent = `
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            z-index: 10000;
            min-width: 300px;
            max-width: 500px;
            animation: slideIn 0.3s ease-out;
        }
        
        .toast-success { border-left: 4px solid #28a745; }
        .toast-error { border-left: 4px solid #dc3545; }
        .toast-warning { border-left: 4px solid #ffc107; }
        .toast-info { border-left: 4px solid #17a2b8; }
        
        .toast-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem;
        }
        
        .toast-message {
            flex: 1;
            margin-right: 1rem;
        }
        
        .toast-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
            color: #999;
            padding: 0;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .toast-close:hover {
            color: #333;
        }
        
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
        
        @media (max-width: 480px) {
            .toast {
                left: 10px;
                right: 10px;
                min-width: auto;
            }
        }
    `;
    document.head.appendChild(toastStyles);
}

// Export for global use
window.AIAssistant = AIAssistant;
window.showToast = showToast;