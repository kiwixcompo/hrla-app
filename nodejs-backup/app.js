// ==========================================
// DATABASE CLIENT MANAGER
// ==========================================

class DatabaseClient {
    constructor(app) {
        this.app = app;
        this.baseUrl = app.getApiUrl('db');
    }

    async getApiConfig() {
        try {
            // Check if we have a valid session token
            if (!this.app.sessionToken) {
                console.warn('⚠️ No session token available for database API config');
                return null;
            }
            
            const response = await fetch(`${this.baseUrl}/db/config`, {
                headers: {
                    'Authorization': `Bearer ${this.app.sessionToken}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.config;
            } else if (response.status === 401) {
                console.warn('⚠️ Unauthorized access to database API config - session may be invalid');
                return null;
            } else {
                console.warn('⚠️ Database API config request failed:', response.status, response.statusText);
                return null;
            }
        } catch (error) {
            console.error('Error getting API config from database:', error);
            return null;
        }
    }

    async updateApiConfig(apiKey) {
        try {
            const response = await fetch(`${this.baseUrl}/db/config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.app.sessionToken}`
                },
                body: JSON.stringify({ openaiKey: apiKey })
            });
            
            return response.ok;
        } catch (error) {
            console.error('Error updating API config in database:', error);
            return false;
        }
    }

    async getAllUsers() {
        try {
            const response = await fetch(`${this.baseUrl}/users`, {
                headers: {
                    'Authorization': `Bearer ${this.app.sessionToken}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.users;
            }
            return [];
        } catch (error) {
            console.error('Error getting users from database:', error);
            return [];
        }
    }

    async createUser(userData) {
        try {
            const response = await fetch(`${this.baseUrl}/users`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.app.sessionToken}`
                },
                body: JSON.stringify(userData)
            });
            
            return response.ok;
        } catch (error) {
            console.error('Error creating user in database:', error);
            return false;
        }
    }

    async updateUser(id, userData) {
        try {
            const response = await fetch(`${this.baseUrl}/users/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${this.app.sessionToken}`
                },
                body: JSON.stringify(userData)
            });
            
            return response.ok;
        } catch (error) {
            console.error('Error updating user in database:', error);
            return false;
        }
    }

    async deleteUser(id) {
        try {
            const response = await fetch(`${this.baseUrl}/users/${id}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${this.app.sessionToken}`
                }
            });
            
            return response.ok;
        } catch (error) {
            console.error('Error deleting user from database:', error);
            return false;
        }
    }

    async exportData() {
        try {
            const response = await fetch(`${this.baseUrl}/export`, {
                headers: {
                    'Authorization': `Bearer ${this.app.sessionToken}`
                }
            });
            
            if (response.ok) {
                const blob = await response.blob();
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `hrla_database_export_${new Date().toISOString().split('T')[0]}.json`;
                document.body.appendChild(a);
                a.click();
                document.body.removeChild(a);
                window.URL.revokeObjectURL(url);
                return true;
            }
            return false;
        } catch (error) {
            console.error('Error exporting data:', error);
            return false;
        }
    }

    async getStats() {
        try {
            const response = await fetch(`${this.baseUrl}/stats`, {
                headers: {
                    'Authorization': `Bearer ${this.app.sessionToken}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.stats;
            }
            return null;
        } catch (error) {
            console.error('Error getting database stats:', error);
            return null;
        }
    }

    async getAccessCodes() {
        try {
            const response = await fetch(`${this.baseUrl}/access-codes`, {
                headers: {
                    'Authorization': `Bearer ${this.app.sessionToken}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.codes;
            }
            return [];
        } catch (error) {
            console.error('Error getting access codes from database:', error);
            return [];
        }
    }

    async validateAccessCode(code) {
        try {
            const accessCodes = await this.getAccessCodes();
            return accessCodes.find(c => c.code === code && c.isActive);
        } catch (error) {
            console.error('Error validating access code:', error);
            return null;
        }
    }
}

// ==========================================
// PERMANENT STORAGE MANAGER
// ==========================================

class PermanentStorageManager {
    constructor() {
        this.storagePrefix = 'hrla_';
        this.backupPrefix = 'hrla_backup_';
        this.initializeStorage();
    }

    initializeStorage() {
        console.log('💾 Initializing permanent storage system...');
        
        // Check if we need to migrate old data
        this.migrateOldData();
        
        // Initialize default data if needed
        this.initializeDefaults();
        
        // Set up periodic backups
        this.setupPeriodicBackups();
        
        console.log('✅ Permanent storage system initialized');
    }

    // Core storage methods with redundancy
    set(key, value) {
        const fullKey = this.storagePrefix + key;
        const backupKey = this.backupPrefix + key;
        const data = {
            value: value,
            timestamp: Date.now(),
            version: '1.0'
        };
        
        try {
            // Store in primary location
            localStorage.setItem(fullKey, JSON.stringify(data));
            
            // Store backup copy
            localStorage.setItem(backupKey, JSON.stringify(data));
            
            // Store in sessionStorage as additional backup
            sessionStorage.setItem(fullKey, JSON.stringify(data));
            
            console.log(`💾 Stored: ${key}`);
            return true;
        } catch (error) {
            console.error(`❌ Storage error for ${key}:`, error);
            return false;
        }
    }

    get(key) {
        const fullKey = this.storagePrefix + key;
        const backupKey = this.backupPrefix + key;
        
        try {
            // Try primary storage first
            let data = localStorage.getItem(fullKey);
            
            // If primary fails, try backup
            if (!data) {
                data = localStorage.getItem(backupKey);
                if (data) {
                    console.log(`🔄 Restored ${key} from backup`);
                    // Restore to primary
                    localStorage.setItem(fullKey, data);
                }
            }
            
            // If both fail, try sessionStorage
            if (!data) {
                data = sessionStorage.getItem(fullKey);
                if (data) {
                    console.log(`🔄 Restored ${key} from session backup`);
                    // Restore to both primary and backup
                    localStorage.setItem(fullKey, data);
                    localStorage.setItem(backupKey, data);
                }
            }
            
            if (data) {
                const parsed = JSON.parse(data);
                return parsed.value;
            }
            
            return null;
        } catch (error) {
            console.error(`❌ Retrieval error for ${key}:`, error);
            return null;
        }
    }

    // Remove data (admin only)
    remove(key) {
        const fullKey = this.storagePrefix + key;
        const backupKey = this.backupPrefix + key;
        
        try {
            localStorage.removeItem(fullKey);
            localStorage.removeItem(backupKey);
            sessionStorage.removeItem(fullKey);
            console.log(`🗑️ Removed: ${key}`);
            return true;
        } catch (error) {
            console.error(`❌ Removal error for ${key}:`, error);
            return false;
        }
    }

    // Get all stored keys
    getAllKeys() {
        const keys = [];
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith(this.storagePrefix)) {
                keys.push(key.replace(this.storagePrefix, ''));
            }
        }
        return keys;
    }

    // Backup all data
    createFullBackup() {
        const backup = {
            timestamp: Date.now(),
            version: '1.0',
            data: {}
        };
        
        const keys = this.getAllKeys();
        keys.forEach(key => {
            backup.data[key] = this.get(key);
        });
        
        const backupString = JSON.stringify(backup);
        localStorage.setItem('hrla_full_backup', backupString);
        
        console.log('💾 Full backup created with', keys.length, 'items');
        return backup;
    }

    // Restore from backup
    restoreFromBackup() {
        try {
            const backupString = localStorage.getItem('hrla_full_backup');
            if (!backupString) {
                console.log('❌ No backup found');
                return false;
            }
            
            const backup = JSON.parse(backupString);
            let restored = 0;
            
            Object.keys(backup.data).forEach(key => {
                this.set(key, backup.data[key]);
                restored++;
            });
            
            console.log('✅ Restored', restored, 'items from backup');
            return true;
        } catch (error) {
            console.error('❌ Backup restoration failed:', error);
            return false;
        }
    }

    // Migrate old localStorage data
    migrateOldData() {
        console.log('🔄 Checking for data migration...');
        
        const oldKeys = ['users', 'apiConfig', 'accessCodes', 'sessionToken', 'currentUser'];
        let migrated = 0;
        
        oldKeys.forEach(key => {
            const oldData = localStorage.getItem(key);
            if (oldData && !this.get(key)) {
                try {
                    const parsed = JSON.parse(oldData);
                    this.set(key, parsed);
                    migrated++;
                    console.log(`🔄 Migrated: ${key}`);
                } catch (error) {
                    // If it's not JSON, store as string
                    this.set(key, oldData);
                    migrated++;
                    console.log(`🔄 Migrated (string): ${key}`);
                }
            }
        });
        
        if (migrated > 0) {
            console.log(`✅ Migrated ${migrated} items to permanent storage`);
        }
    }

    // Initialize default data
    initializeDefaults() {
        // Initialize users with default admin if none exist
        if (!this.get('users')) {
            const defaultUsers = [{
                id: 'admin-001',
                firstName: 'Admin',
                lastName: 'User',
                email: 'talk2char@gmail.com',
                password: 'Password@123',
                isAdmin: true,
                emailVerified: true,
                createdAt: Date.now(),
                subscriptionExpiry: null
            }];
            this.set('users', defaultUsers);
            console.log('👤 Default admin user created');
        }

        // Initialize API config if none exists
        if (!this.get('apiConfig')) {
            const defaultApiConfig = {
                openaiKey: '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',
                updatedAt: Date.now(),
                totalRequests: 0,
                openaiRequests: 0
            };
            this.set('apiConfig', defaultApiConfig);
            console.log('🔑 Default API config created');
        }

        // Initialize access codes if none exist
        if (!this.get('accessCodes')) {
            this.set('accessCodes', []);
            console.log('🎫 Access codes initialized');
        }
    }

    // Set up periodic backups (every 5 minutes)
    setupPeriodicBackups() {
        setInterval(() => {
            this.createFullBackup();
        }, 5 * 60 * 1000); // 5 minutes
        
        console.log('⏰ Periodic backups enabled (every 5 minutes)');
    }

    // Admin method to clear all data
    adminClearAllData() {
        console.log('🚨 Admin clearing all data...');
        
        const keys = this.getAllKeys();
        keys.forEach(key => {
            this.remove(key);
        });
        
        // Also clear old localStorage items
        localStorage.removeItem('hrla_full_backup');
        
        console.log('✅ All data cleared by admin');
        
        // Reinitialize defaults
        this.initializeDefaults();
    }

    // Get storage statistics
    getStorageStats() {
        const keys = this.getAllKeys();
        const stats = {
            totalKeys: keys.length,
            storageUsed: 0,
            items: {}
        };
        
        keys.forEach(key => {
            const data = this.get(key);
            const size = JSON.stringify(data).length;
            stats.storageUsed += size;
            stats.items[key] = {
                size: size,
                type: Array.isArray(data) ? 'array' : typeof data,
                length: Array.isArray(data) ? data.length : null
            };
        });
        
        return stats;
    }
}

// ==========================================
// MAIN APPLICATION CLASS
// ==========================================

class LeaveAssistantApp {
    constructor() {
        try {
            console.log('🚀 Initializing Leave Assistant App (Pro Version)...');
            
            // Initialize storage manager first
            this.storage = new PermanentStorageManager();
            
            // Initialize database client
            this.dbClient = new DatabaseClient(this);
            
            // Initialize basic properties
            console.log('📝 Setting up basic properties...');
            this.currentUser = null;
            this.sessionToken = null;
            this.idleTimer = null;
            this.idleTimeout = 30 * 60 * 1000; // 30 minutes in milliseconds
            this.trialTimerInterval = null; // For countdown timer
            this.serverRunning = false;
            this.lastConversation = {}; // Store conversation context for regeneration and follow-ups
            
            // Initialize users from storage
            console.log('💾 Loading user data...');
            this.users = this.loadUsers();
            
            console.log('⚙️ Loading payment config...');
            this.paymentConfig = this.loadPaymentConfig();
            
            // Check for stored session
            console.log('🔐 Checking stored session...');
            this.sessionToken = this.storage.get('sessionToken');
            
            // Update footer year
            console.log('📅 Updating footer year...');
            this.updateFooterYear();
            
            // Start App
            console.log('🚀 Starting app initialization...');
            this.init();
            
            console.log('⏰ Setting up idle timer...');
            this.setupIdleTimer();
            
            console.log('✅ Constructor completed successfully');
        } catch (error) {
            console.error('❌ Critical Init Error:', error);
            console.error('❌ Error stack:', error.stack);
            this.logError(error, 'Constructor', { phase: 'initialization' });
            setTimeout(() => this.showPage('loginPage'), 100);
        }
    }

    // ==========================================
    // API CONNECTION HELPER (The Fix)
    // ==========================================
    getApiUrl(endpoint) {
        // For production/Netlify deployment, use client-side storage for admin functions
        if (window.location.hostname !== 'localhost' && window.location.hostname !== '127.0.0.1') {
            console.warn('⚠️ Production environment detected. Using client-side admin functionality.');
            
            // Return null for server-dependent endpoints, but allow admin functions to work client-side
            const clientSideEndpoints = [
                'admin/generate-access-code',
                'admin/access-codes', 
                'admin/api-settings',
                'admin/test-api-key',
                'config'
            ];
            
            if (clientSideEndpoints.some(ep => endpoint.includes(ep))) {
                return 'client-side'; // Special flag for client-side handling
            }
            
            return null; // This will force fallback to client-only mode for other endpoints
        }
        
        // If the app is loaded from port 3001, use relative path
        if (window.location.port === '3001') {
            return `/api/${endpoint}`;
        }
        
        // Otherwise, force connection to the backend port 3001
        // This fixes the "http://localhost/api/..." 404 error
        const hostname = window.location.hostname || 'localhost';
        return `http://${hostname}:3001/api/${endpoint}`;
    }

    // ==========================================
    // ERROR LOGGING SYSTEM
    // ==========================================
    
    async logError(error, context = 'Unknown', additionalInfo = {}) {
        try {
            const timestamp = new Date().toISOString();
            const errorData = {
                timestamp,
                context,
                message: error.message || error.toString(),
                stack: error.stack || 'No stack trace available',
                url: window.location.href,
                userAgent: navigator.userAgent,
                user: this.currentUser ? {
                    id: this.currentUser.id,
                    email: this.currentUser.email,
                    isAdmin: this.currentUser.isAdmin
                } : null,
                additionalInfo
            };
            
            // Log to console for immediate debugging
            console.error(`❌ [${context}] Error logged:`, errorData);
            
            // Store in localStorage as backup
            this.storeErrorInLocalStorage(errorData);
            
            // Try to send to server for file logging
            await this.sendErrorToServer(errorData);
            
        } catch (loggingError) {
            console.error('❌ Failed to log error:', loggingError);
            // Fallback: at least store in localStorage
            try {
                this.storeErrorInLocalStorage({
                    timestamp: new Date().toISOString(),
                    context: 'Error Logging Failed',
                    message: `Original: ${error.message || error.toString()}, Logging Error: ${loggingError.message}`,
                    url: window.location.href
                });
            } catch (fallbackError) {
                console.error('❌ Complete error logging failure:', fallbackError);
            }
        }
    }
    
    storeErrorInLocalStorage(errorData) {
        try {
            const existingErrors = JSON.parse(localStorage.getItem('errorLog') || '[]');
            existingErrors.push(errorData);
            
            // Keep only last 100 errors to prevent localStorage bloat
            if (existingErrors.length > 100) {
                existingErrors.splice(0, existingErrors.length - 100);
            }
            
            localStorage.setItem('errorLog', JSON.stringify(existingErrors));
        } catch (storageError) {
            console.error('❌ Failed to store error in localStorage:', storageError);
        }
    }
    
    async sendErrorToServer(errorData) {
        try {
            const apiUrl = this.getApiUrl('log-error');
            
            // Only try server logging if we have a server connection
            if (apiUrl && apiUrl !== 'client-side' && this.serverRunning) {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': this.sessionToken ? `Bearer ${this.sessionToken}` : ''
                    },
                    body: JSON.stringify(errorData)
                });
                
                if (!response.ok) {
                    throw new Error(`Server error logging failed: ${response.status}`);
                }
            } else {
                // In production/client-only mode, we can't write to server files
                // The localStorage backup is our primary logging method
                console.log('📝 Error logged to localStorage (client-only mode)');
            }
        } catch (serverError) {
            console.warn('⚠️ Server error logging failed, using localStorage only:', serverError.message);
        }
    }
    
    // Method to retrieve error logs (useful for debugging)
    getErrorLogs() {
        try {
            return JSON.parse(localStorage.getItem('errorLog') || '[]');
        } catch (error) {
            console.error('❌ Failed to retrieve error logs:', error);
            return [];
        }
    }
    
    // Method to clear error logs
    clearErrorLogs() {
        try {
            localStorage.removeItem('errorLog');
            console.log('✅ Error logs cleared');
        } catch (error) {
            console.error('❌ Failed to clear error logs:', error);
        }
    }
    
    // Method to display error logs in console (useful for debugging)
    showErrorLogs() {
        try {
            const logs = this.getErrorLogs();
            if (logs.length === 0) {
                console.log('📝 No error logs found');
                return;
            }
            
            console.log(`📝 Found ${logs.length} error log(s):`);
            logs.forEach((log, index) => {
                console.group(`Error ${index + 1} - ${log.timestamp}`);
                console.log('Context:', log.context);
                console.log('Message:', log.message);
                console.log('URL:', log.url);
                console.log('User:', log.user || 'Anonymous');
                if (log.stack) console.log('Stack:', log.stack);
                if (log.additionalInfo && Object.keys(log.additionalInfo).length > 0) {
                    console.log('Additional Info:', log.additionalInfo);
                }
                console.groupEnd();
            });
        } catch (error) {
            console.error('❌ Failed to display error logs:', error);
        }
    }
    
    // Test method for error logging (can be called from browser console)
    testErrorLogging() {
        console.log('🧪 Testing error logging system...');
        const testError = new Error('This is a test error for logging verification');
        this.logError(testError, 'Error Logging Test', { 
            testData: 'This is test data',
            timestamp: Date.now()
        });
        console.log('✅ Test error logged. Check localStorage or server logs.');
    }

    // Debug method to check localStorage users (can be called from browser console)
    debugUsers() {
        console.log('🔍 Debugging user storage...');
        
        const storedUsers = localStorage.getItem('users');
        console.log('📦 Raw localStorage users:', storedUsers);
        
        if (storedUsers) {
            try {
                const parsedUsers = JSON.parse(storedUsers);
                console.log('👥 Parsed users:', parsedUsers.length);
                parsedUsers.forEach((user, index) => {
                    console.log(`User ${index + 1}:`, {
                        id: user.id,
                        name: `${user.firstName} ${user.lastName}`,
                        email: user.email,
                        isAdmin: user.isAdmin,
                        emailVerified: user.emailVerified
                    });
                });
                return parsedUsers;
            } catch (error) {
                console.error('❌ Error parsing users:', error);
            }
        } else {
            console.log('❌ No users found in localStorage');
        }
        
        return [];
    }

    // Method to manually add a test user (can be called from browser console)
    addTestUser() {
        console.log('🧪 Adding test user...');
        
        // Load current users from localStorage directly
        let users = [];
        const storedUsers = localStorage.getItem('users');
        if (storedUsers) {
            try {
                users = JSON.parse(storedUsers);
            } catch (error) {
                console.error('Error parsing users:', error);
                users = [];
            }
        }
        
        // If no users, create admin first
        if (users.length === 0) {
            users = [{
                id: 'admin-001',
                firstName: 'Admin',
                lastName: 'User',
                email: 'talk2char@gmail.com',
                password: 'Password@123',
                isAdmin: true,
                emailVerified: true,
                createdAt: Date.now(),
                subscriptionExpiry: null
            }];
        }
        
        const testUser = {
            id: 'user_james_drake_' + Date.now(),
            firstName: 'James',
            lastName: 'Drake',
            email: 'james@drake.com',
            password: 'password123',
            isAdmin: false,
            emailVerified: true,
            createdAt: Date.now(),
            subscriptionExpiry: Date.now() + (365 * 24 * 60 * 60 * 1000), // 1 year
            trialStarted: Date.now(),
            trialExpiry: Date.now() + (24 * 60 * 60 * 1000),
            accessLevel: 'subscribed'
        };
        
        // Check if user already exists
        const existingUser = users.find(u => u.email.toLowerCase() === testUser.email.toLowerCase());
        if (existingUser) {
            console.log('⚠️ User already exists:', existingUser.email);
            return existingUser;
        }
        
        users.push(testUser);
        
        // Save directly to localStorage
        localStorage.setItem('users', JSON.stringify(users));
        
        // Update class property
        this.users = users;
        
        console.log('✅ Test user added:', testUser.email);
        console.log('📊 Total users now:', users.length);
        console.log('👥 All users:', users.map(u => u.email));
        
        // Refresh admin dashboard if visible
        if (document.getElementById('adminDashboard') && !document.getElementById('adminDashboard').classList.contains('hidden')) {
            this.loadUsersTab();
            this.loadAdminStats();
        }
        
        return testUser;
    }

    // Method to create James Drake user specifically
    createJamesDrakeUser() {
        console.log('👤 Creating James Drake user...');
        
        // Get current users
        let users = [];
        const storedUsers = localStorage.getItem('users');
        if (storedUsers) {
            try {
                users = JSON.parse(storedUsers);
            } catch (error) {
                users = [];
            }
        }
        
        // Ensure admin exists
        if (!users.find(u => u.email === 'talk2char@gmail.com')) {
            users.push({
                id: 'admin-001',
                firstName: 'Admin',
                lastName: 'User',
                email: 'talk2char@gmail.com',
                password: 'Password@123',
                isAdmin: true,
                emailVerified: true,
                createdAt: Date.now(),
                subscriptionExpiry: null
            });
        }
        
        // Check if James Drake already exists
        const existingJames = users.find(u => u.email.toLowerCase() === 'james@drake.com');
        if (existingJames) {
            console.log('✅ James Drake already exists');
            return existingJames;
        }
        
        // Create James Drake user
        const jamesUser = {
            id: 'user_james_drake_001',
            firstName: 'James',
            lastName: 'Drake',
            email: 'james@drake.com',
            password: 'password123',
            isAdmin: false,
            emailVerified: true,
            createdAt: Date.now() - (7 * 24 * 60 * 60 * 1000), // Created 7 days ago
            subscriptionExpiry: Date.now() + (365 * 24 * 60 * 60 * 1000), // 1 year subscription
            trialStarted: Date.now() - (7 * 24 * 60 * 60 * 1000),
            trialExpiry: Date.now() - (6 * 24 * 60 * 60 * 1000), // Trial expired (has subscription)
            accessLevel: 'subscribed'
        };
        
        users.push(jamesUser);
        
        // Save to localStorage
        localStorage.setItem('users', JSON.stringify(users));
        this.users = users;
        
        console.log('✅ James Drake user created successfully');
        console.log('📧 Email: james@drake.com');
        console.log('🔑 Password: password123');
        console.log('📊 Total users:', users.length);
        
        // Refresh displays
        if (document.getElementById('adminDashboard') && !document.getElementById('adminDashboard').classList.contains('hidden')) {
            this.loadUsersTab();
            this.loadAdminStats();
        }
        
        return jamesUser;
    }

    // Method to repair user data integrity
    repairUserData() {
        console.log('🔧 Repairing user data integrity...');
        
        // Ensure admin user exists
        let users = this.loadUsers();
        
        // Check if admin exists
        const adminExists = users.find(u => u.email === 'talk2char@gmail.com');
        if (!adminExists) {
            console.log('🔧 Adding missing admin user...');
            users.push({
                id: 'admin-001',
                firstName: 'Admin',
                lastName: 'User',
                email: 'talk2char@gmail.com',
                password: 'Password@123',
                isAdmin: true,
                emailVerified: true,
                createdAt: Date.now(),
                subscriptionExpiry: null
            });
        }
        
        // Check if James Drake exists
        const jamesExists = users.find(u => u.email === 'james@drake.com');
        if (!jamesExists) {
            console.log('🔧 Adding James Drake user...');
            users.push({
                id: 'user_james_drake_001',
                firstName: 'James',
                lastName: 'Drake',
                email: 'james@drake.com',
                password: 'password123',
                isAdmin: false,
                emailVerified: true,
                createdAt: Date.now(),
                subscriptionExpiry: Date.now() + (365 * 24 * 60 * 60 * 1000),
                trialStarted: Date.now(),
                trialExpiry: Date.now() + (24 * 60 * 60 * 1000),
                accessLevel: 'subscribed'
            });
        }
        
        // Save repaired data
        this.saveUsers(users);
        this.users = users;
        
        console.log('✅ User data repaired. Total users:', users.length);
        return users;
    }

    // Debug method to check API configuration
    debugApiConfig() {
        console.log('🔍 Debugging API Configuration...');
        
        // Check permanent storage
        const storageConfig = this.storage.get('apiConfig');
        console.log('📦 Permanent Storage API Config:', storageConfig);
        
        // Check localStorage directly
        const localConfig = localStorage.getItem('apiConfig');
        console.log('📦 Direct localStorage API Config:', localConfig);
        
        // Check if server is running
        console.log('🌐 Server Running:', this.serverRunning);
        console.log('🔑 Session Token:', this.sessionToken ? 'Present' : 'Missing');
        
        // Try to get from database if available
        if (this.serverRunning && this.sessionToken) {
            this.dbClient.getApiConfig().then(dbConfig => {
                console.log('🗄️ Database API Config:', dbConfig);
            }).catch(error => {
                console.log('❌ Database API Config Error:', error);
            });
        }
        
        return {
            storage: storageConfig,
            localStorage: localConfig,
            serverRunning: this.serverRunning,
            sessionToken: !!this.sessionToken
        };
    }
    repairUserData() {
        console.log('🔧 Repairing user data integrity...');
        
        const storedUsers = localStorage.getItem('users');
        if (!storedUsers) {
            console.log('❌ No user data found in localStorage');
            return false;
        }
        
        try {
            const users = JSON.parse(storedUsers);
            console.log('📊 Found', users.length, 'users in localStorage');
            
            // Ensure all users have required fields
            let repaired = false;
            users.forEach(user => {
                if (!user.id) {
                    user.id = 'user_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    repaired = true;
                }
                if (!user.emailVerified && user.emailVerified !== false) {
                    user.emailVerified = true;
                    repaired = true;
                }
                if (!user.createdAt) {
                    user.createdAt = Date.now();
                    repaired = true;
                }
                if (!user.accessLevel) {
                    user.accessLevel = user.isAdmin ? 'admin' : 'trial';
                    repaired = true;
                }
            });
            
            if (repaired) {
                this.saveUsers(users);
                console.log('✅ User data repaired and saved');
            } else {
                console.log('✅ User data is already valid');
            }
            
            // Update class property
            this.users = users;
            
            // Refresh displays
            if (document.getElementById('adminDashboard') && !document.getElementById('adminDashboard').classList.contains('hidden')) {
                this.loadUsersTab();
                this.loadAdminStats();
            }
            
            return true;
            
        } catch (error) {
            console.error('❌ Error repairing user data:', error);
            return false;
        }
    }

    // Method to clear phantom users and reset display
    clearPhantomUsers() {
        console.log('👻 Clearing phantom users...');
        
        // Get actual users from localStorage
        const storedUsers = localStorage.getItem('users');
        let actualUsers = [];
        
        if (storedUsers) {
            try {
                actualUsers = JSON.parse(storedUsers);
            } catch (error) {
                console.error('Error parsing users:', error);
            }
        }
        
        console.log('📊 Actual users in localStorage:', actualUsers.length);
        console.log('👥 Actual user emails:', actualUsers.map(u => u.email));
        
        // Update class property to match localStorage
        this.users = actualUsers;
        
        // Force refresh admin dashboard
        if (document.getElementById('adminDashboard') && !document.getElementById('adminDashboard').classList.contains('hidden')) {
            console.log('🔄 Refreshing admin dashboard...');
            this.loadUsersTab();
            this.loadAdminStats();
        }
        
        return actualUsers;
    }

    // Debug method to check API configuration
    debugApiConfig() {
        console.log('🔍 Debugging API configuration...');
        
        const apiConfig = this.storage.get('apiConfig');
        console.log('📦 API config from permanent storage:', apiConfig);
        
        if (apiConfig) {
            console.log('📊 API config details:', {
                hasOpenaiKey: !!apiConfig.openaiKey,
                keyLength: apiConfig.openaiKey ? apiConfig.openaiKey.length : 0,
                keyPrefix: apiConfig.openaiKey ? apiConfig.openaiKey.substring(0, 8) + '...' : 'none',
                updatedAt: apiConfig.updatedAt ? new Date(apiConfig.updatedAt).toLocaleString() : 'never'
            });
            return apiConfig;
        } else {
            console.log('❌ No apiConfig found in permanent storage');
        }
        
        return null;
    }

    // Method to manually set API key for testing
    setTestApiKey() {
        console.log('🧪 Setting test API key...');
        
        const testKey = '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';
        
        const config = {
            openaiKey: testKey,
            updatedAt: Date.now(),
            totalRequests: 0,
            openaiRequests: 0
        };
        
        this.storage.set('apiConfig', config);
        
        console.log('✅ Test API key set in permanent storage');
        console.log('🔑 Key length:', testKey.length);
        
        // Update the admin form if visible
        const apiKeyField = document.getElementById('systemOpenaiKey');
        if (apiKeyField) {
            apiKeyField.value = testKey;
        }
        
        // Refresh API settings tab if visible
        if (document.getElementById('adminDashboard') && !document.getElementById('adminDashboard').classList.contains('hidden')) {
            this.loadApiSettingsTab();
        }
        
        return config;
    }

    // Get storage statistics
    getStorageStats() {
        return this.storage.getStorageStats();
    }

    // Load storage management tab
    loadStorageTab() {
        console.log('🔧 Loading storage management tab...');
        
        const stats = this.getStorageStats();
        
        // Update statistics display
        document.getElementById('storageKeysCount').textContent = stats.totalKeys;
        document.getElementById('storageUsedSize').textContent = Math.round(stats.storageUsed / 1024) + ' KB';
        document.getElementById('usersCount').textContent = (stats.items.users?.length || 0);
        document.getElementById('backupStatus').textContent = localStorage.getItem('hrla_full_backup') ? 'Available' : 'None';
        
        // Update storage details
        const detailsContent = document.getElementById('storageDetailsContent');
        if (detailsContent) {
            let html = '<div class="storage-items">';
            
            Object.keys(stats.items).forEach(key => {
                const item = stats.items[key];
                html += `
                    <div class="storage-item">
                        <strong>${key}</strong>: ${item.type}
                        ${item.length !== null ? ` (${item.length} items)` : ''}
                        - ${Math.round(item.size / 1024)} KB
                    </div>
                `;
            });
            
            html += '</div>';
            detailsContent.innerHTML = html;
        }
        
        console.log('✅ Storage tab loaded');
    }

    // Debug storage data
    debugStorageData() {
        console.log('🔍 Debugging storage data...');
        
        const stats = this.getStorageStats();
        console.log('📊 Storage Statistics:', stats);
        
        // Check each storage item
        Object.keys(stats.items).forEach(key => {
            const data = this.storage.get(key);
            console.log(`📦 ${key}:`, data);
        });
        
        // Check API configuration specifically
        console.log('🔑 API Configuration Debug:');
        this.debugApiConfig();
        
        // Check users
        console.log('👥 Users Debug:');
        this.debugUsers();
        
        this.showSuccess('Storage debug information logged to console');
    }

    // Export database data
    async exportDatabaseData() {
        console.log('📊 Exporting database data...');
        
        if (!this.serverRunning || !this.sessionToken) {
            this.showError('Database export requires server connection. Using storage backup instead.');
            this.createBackup();
            return;
        }
        
        try {
            const success = await this.dbClient.exportData();
            if (success) {
                this.showSuccess('Database exported successfully');
            } else {
                this.showError('Database export failed');
            }
        } catch (error) {
            console.error('Database export error:', error);
            this.showError('Database export failed');
        }
    }

    // Admin method to clear all data
    adminClearAllData() {
        if (confirm('⚠️ This will permanently delete ALL data including users, API keys, and settings. Are you sure?')) {
            this.storage.adminClearAllData();
            this.showSuccess('All data cleared. Page will reload.');
            setTimeout(() => window.location.reload(), 2000);
        }
    }

    // Create backup
    createBackup() {
        const backup = this.storage.createFullBackup();
        this.showSuccess('Backup created successfully');
        return backup;
    }

    // Restore from backup
    restoreBackup() {
        if (confirm('⚠️ This will restore data from the last backup. Continue?')) {
            const success = this.storage.restoreFromBackup();
            if (success) {
                this.showSuccess('Data restored from backup. Page will reload.');
                setTimeout(() => window.location.reload(), 2000);
            } else {
                this.showError('Backup restoration failed');
            }
        }
    }

    // ==========================================
    // LANDING PAGE FUNCTIONALITY
    // ==========================================

    showDemo() {
        // Show a demo of the tool functionality
        this.showSuccess('Demo feature coming soon! Sign up for free trial to experience the full functionality.');
    }

    toggleMobileMenu() {
        // Mobile menu toggle functionality
        const navMenu = document.querySelector('.nav-menu');
        const toggleButton = document.getElementById('mobileMenuToggle');
        
        if (navMenu && toggleButton) {
            const isOpen = navMenu.classList.contains('mobile-open');
            navMenu.classList.toggle('mobile-open');
            toggleButton.setAttribute('aria-expanded', !isOpen);
            
            // Update icon
            const icon = toggleButton.querySelector('i');
            if (icon) {
                icon.className = isOpen ? 'fas fa-bars' : 'fas fa-times';
            }
        }
    }

    copyOutput(tool) {
        const outputElement = document.getElementById(`${tool}Output`);
        if (outputElement && outputElement.textContent) {
            navigator.clipboard.writeText(outputElement.textContent).then(() => {
                this.showSuccess('Response copied to clipboard!');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = outputElement.textContent;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                this.showSuccess('Response copied to clipboard!');
            });
        } else {
            this.showError('No response to copy');
        }
    }

    // ==========================================
    // INITIALIZATION & AUTH
    // ==========================================

    async init() {
        try {
            console.log('🔄 Starting initialization...');
            
            // Hide pages
            document.querySelectorAll('.page').forEach(p => p.classList.add('hidden'));
            document.querySelectorAll('.modal').forEach(m => m.classList.add('hidden'));
            
            this.bindEvents();
            console.log('✅ Events bound successfully');
            
            // Check server status
            await this.checkServerStatus();
            
            // URL Parameter Checks
            const urlParams = new URLSearchParams(window.location.search);
            
            // Email verification
            if (urlParams.get('verify')) {
                console.log('🔗 Email verification token detected');
                await this.verifyEmailToken(urlParams.get('verify'));
                window.history.replaceState({}, document.title, window.location.pathname);
                return;
            }
            
            // Payment success
            if (urlParams.get('session_id') || window.location.pathname.includes('payment-success')) {
                console.log('💳 Payment success detected');
                this.showPage('paymentSuccessPage');
                this.showSuccess('Payment completed successfully!');
                window.history.replaceState({}, document.title, window.location.pathname);
                return;
            }
            
            // Payment cancelled
            if (window.location.pathname.includes('payment-cancelled')) {
                console.log('❌ Payment cancelled detected');
                this.showPage('paymentCancelledPage');
                window.history.replaceState({}, document.title, window.location.pathname);
                return;
            }
            
            // Session Check
            if (this.sessionToken) {
                console.log('👤 Existing session found, validating...');
                const isValid = await this.validateSession();
                if (isValid) {
                    console.log('✅ Session valid, checking subscription');
                    this.checkSubscriptionAndRedirect();
                } else {
                    console.log('❌ Session invalid, showing landing page');
                    this.sessionToken = null;
                    localStorage.removeItem('sessionToken');
                    this.showPage('landingPage');
                }
            } else {
                console.log('🆕 No existing session, showing landing page');
                this.showPage('landingPage');
            }
            
            this.hideLoading();
            console.log('✅ Initialization complete');
            
        } catch (error) {
            console.error('❌ Initialization Error:', error);
            this.logError(error, 'Initialization', { phase: 'app_init' });
            this.hideLoading();
            this.showPage('loginPage');
            this.showError('Application failed to initialize. Please refresh the page.');
        }
    }

    // UI Helpers
    showPage(id) { 
        console.log(`📄 Showing page: ${id}`);
        
        // Hide all pages
        const pages = document.querySelectorAll('.page');
        console.log(`📄 Found ${pages.length} pages to hide`);
        
        pages.forEach(page => {
            page.classList.add('hidden');
        });
        
        // Show target page
        const targetPage = document.getElementById(id);
        if (targetPage) {
            targetPage.classList.remove('hidden');
            console.log(`✅ Successfully showed page: ${id}`);
            
            // Initialize page-specific functionality
            if (id === 'adminDashboard') {
                this.initializeAdminDashboard();
            } else if (id === 'dashboard') {
                this.initializeDashboard();
            }
        } else {
            console.error(`❌ Page not found: ${id}`);
        }
    }

    hideSettings() { document.getElementById('settingsModal').classList.add('hidden'); }
    showLoading() { document.getElementById('loading').classList.remove('hidden'); }
    hideLoading() { document.getElementById('loading').classList.add('hidden'); }
    
    showSuccess(msg) { this.showToast(msg, 'success'); }
    showError(msg) { this.showToast(msg, 'error'); }
    
    showToast(message, type) {
        try {
            const toast = document.createElement('div');
            toast.className = `toast toast-${type}`;
            toast.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i>
                <span>${message}</span>
            `;
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    if (toast.parentNode) {
                        document.body.removeChild(toast);
                    }
                }, 300);
            }, type === 'error' ? 4000 : 3000);
        } catch (error) {
            console.error('❌ Toast display error:', error);
            this.logError(error, 'Toast Display', { message, type });
            // Fallback: use browser alert
            alert(`${type.toUpperCase()}: ${message}`);
        }
    }

    bindEvents() {
        console.log('🔗 Binding events...');
        
        // Landing page navigation
        const loginBtn = document.getElementById('loginBtn');
        const getStartedBtn = document.getElementById('getStartedBtn');
        const startTrialBtn = document.getElementById('startTrialBtn');
        
        console.log('🔍 Button elements found:', {
            loginBtn: !!loginBtn,
            getStartedBtn: !!getStartedBtn,
            startTrialBtn: !!startTrialBtn
        });
        
        if (loginBtn) {
            loginBtn.addEventListener('click', () => {
                console.log('🔘 Login button clicked');
                this.showPage('loginPage');
            });
        }
        
        if (getStartedBtn) {
            getStartedBtn.addEventListener('click', () => {
                console.log('🔘 Get Started button clicked');
                this.showPage('registerPage');
            });
        }
        
        if (startTrialBtn) {
            startTrialBtn.addEventListener('click', () => {
                console.log('🔘 Start Trial button clicked');
                this.showPage('registerPage');
            });
        }
        
        document.getElementById('watchDemoBtn')?.addEventListener('click', () => this.showDemo());
        document.getElementById('finalCtaBtn')?.addEventListener('click', () => this.showPage('registerPage'));
        
        // Mobile menu toggle
        document.getElementById('mobileMenuToggle')?.addEventListener('click', () => this.toggleMobileMenu());
        
        // Navigation between pages
        const showRegisterEl = document.getElementById('showRegister');
        const showLoginEl = document.getElementById('showLogin');
        if (showRegisterEl) showRegisterEl.onclick = () => this.showPage('registerPage');
        if (showLoginEl) showLoginEl.onclick = () => this.showPage('loginPage');
        
        // Form submissions
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleLogin(e);
            });
        }
        
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', (e) => {
                e.preventDefault();
                this.handleRegister(e);
            });
        }
        
        // Back to homepage buttons
        document.getElementById('backToHomepageFromLogin')?.addEventListener('click', () => {
            console.log('🔘 Back to homepage from login clicked');
            this.showPage('landingPage');
        });
        document.getElementById('backToHomepageFromRegister')?.addEventListener('click', () => {
            console.log('🔘 Back to homepage from register clicked');
            this.showPage('landingPage');
        });
        document.getElementById('backToHomepage')?.addEventListener('click', () => {
            console.log('🔘 Back to homepage clicked');
            this.showPage('landingPage');
        });
        
        // Verification page buttons
        document.getElementById('resendVerification')?.addEventListener('click', () => {
            console.log('🔘 Resend verification clicked');
            this.resendVerificationEmail();
        });
        document.getElementById('backToLogin')?.addEventListener('click', () => {
            console.log('🔘 Back to login from verification clicked');
            this.showPage('loginPage');
        });
        
        // Footer modal links
        document.getElementById('termsLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 Terms link clicked');
            document.getElementById('termsModal')?.classList.remove('hidden');
        });
        document.getElementById('refundPolicyLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 Refund policy link clicked');
            document.getElementById('refundPolicyModal')?.classList.remove('hidden');
        });
        document.getElementById('whatItDoesLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 What it does link clicked');
            document.getElementById('whatItDoesModal')?.classList.remove('hidden');
        });
        document.getElementById('quickStartLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 Quick start link clicked');
            document.getElementById('quickStartModal')?.classList.remove('hidden');
        });
        document.getElementById('accessLicensingLink')?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 Access licensing link clicked');
            document.getElementById('accessLicensingModal')?.classList.remove('hidden');
        });
        
        // Inline terms link in registration form
        document.getElementById('termsLinkInline')?.addEventListener('click', (e) => {
            e.preventDefault();
            console.log('🔘 Inline terms link clicked');
            document.getElementById('termsModal')?.classList.remove('hidden');
        });
        
        // Smooth scrolling for navigation links (but prevent default for modal links)
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            // Skip modal links - they're handled above
            if (anchor.id && (anchor.id.includes('Link') || anchor.id.includes('Modal'))) {
                return;
            }
            
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                // Only do smooth scrolling for actual page sections, not modal triggers
                if (href.length > 1) { // More than just "#"
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                }
            });
        });
        
        // Admin dashboard event handlers
        this.bindAdminEvents();
        
        // User dashboard event handlers
        this.bindDashboardEvents();
        
        console.log('✅ All events bound successfully');
    }

    // Data Loaders
    // Data Loaders - Updated to use permanent storage
    loadUsers() { 
        let users = this.storage.get('users');
        
        if (!users || !Array.isArray(users)) {
            console.log('📊 loadUsers: No users found, creating default admin');
            users = [{ 
                id: 'admin-001',
                firstName: 'Admin',
                lastName: 'User',
                email: 'talk2char@gmail.com',
                password: 'Password@123',
                isAdmin: true,
                emailVerified: true,
                createdAt: Date.now(),
                subscriptionExpiry: null
            }];
            this.storage.set('users', users);
        }
        
        console.log('📊 loadUsers: Loaded', users.length, 'users from permanent storage');
        
        // Update class property to maintain consistency
        this.users = users;
        
        return users;
    }
    
    saveUsers(users) { 
        const success = this.storage.set('users', users);
        if (success) {
            this.users = users;
            console.log('💾 Users saved to permanent storage:', users.length);
        }
        return success;
    }
    
    loadPaymentConfig() { 
        const config = this.storage.get('paymentConfig') || {};
        return config;
    }

    savePaymentConfig(config) {
        return this.storage.set('paymentConfig', config);
    }
    
    loadPaymentConfig() { 
        const c = localStorage.getItem('paymentConfig'); 
        const config = c ? JSON.parse(c) : {};
        return config;
    }

    updateFooterYear() {
        const currentYear = new Date().getFullYear();
        const yearElement = document.getElementById('currentYear');
        if (yearElement) {
            yearElement.textContent = currentYear;
        }
    }

    async checkServerStatus() {
        try {
            const url = this.getApiUrl('health');
            
            // If no URL (production environment), skip server check
            if (!url) {
                console.log('🌐 Production environment - running in client-only mode');
                this.serverRunning = false;
                return;
            }
            
            console.log(`📡 Connecting to backend at: ${url}`);
            
            // Use AbortController for timeout instead of timeout option (not supported in fetch)
            const controller = new AbortController();
            const timeoutId = setTimeout(() => controller.abort(), 5000);
            
            try {
                const response = await fetch(url, { 
                    method: 'GET',
                    signal: controller.signal
                });
                
                clearTimeout(timeoutId);
                
                if (response.ok) {
                    console.log('✅ Server connection established');
                    this.serverRunning = true;
                } else {
                    console.warn('⚠️ Server responded with error:', response.status);
                    this.serverRunning = false;
                }
            } catch (fetchError) {
                clearTimeout(timeoutId);
                if (fetchError.name === 'AbortError') {
                    console.warn('⚠️ Server check timed out after 5 seconds');
                } else {
                    throw fetchError;
                }
            }
        } catch (error) {
            console.warn('⚠️ Server check failed. Running in client-only mode.');
            console.warn('Error details:', error.message);
            this.logError(error, 'Server Check', { phase: 'connection_test' });
            this.serverRunning = false;
        }
    }

    setupIdleTimer() {
        // Events that reset the idle timer
        const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
        
        events.forEach(event => {
            document.addEventListener(event, () => this.resetIdleTimer(), true);
        });
        
        this.resetIdleTimer();
    }

    resetIdleTimer() {
        // Only track idle time if user is logged in
        if (!this.currentUser) return;
        
        // Clear existing timer
        if (this.idleTimer) {
            clearTimeout(this.idleTimer);
        }
        
        // Set new timer
        this.idleTimer = setTimeout(() => {
            this.showError('Session expired due to inactivity. Please log in again.');
            setTimeout(() => this.logout(), 2000);
        }, this.idleTimeout);
    }

    async validateSession() {
        if (!this.sessionToken) return false;
        
        // Check if it's a client-side token
        if (this.sessionToken.startsWith('client-side-token-')) {
            // Validate client-side session
            const storedUser = localStorage.getItem('currentUser');
            if (storedUser) {
                try {
                    this.currentUser = JSON.parse(storedUser);
                    // Verify user still exists in local storage
                    const user = this.findUser(this.currentUser.email);
                    if (user) {
                        this.currentUser = { ...user }; // Refresh user data
                        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
                        return true;
                    }
                } catch (error) {
                    console.error('Client-side session validation error:', error);
                }
            }
            return false;
        }
        
        // Server-side session validation
        const apiUrl = this.getApiUrl('user/profile');
        if (!apiUrl || !this.serverRunning) {
            // Server not available, try client-side validation
            const storedUser = localStorage.getItem('currentUser');
            if (storedUser) {
                try {
                    this.currentUser = JSON.parse(storedUser);
                    const user = this.findUser(this.currentUser.email);
                    if (user) {
                        this.currentUser = { ...user };
                        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
                        return true;
                    }
                } catch (error) {
                    console.error('Client-side session validation error:', error);
                }
            }
            return false;
        }
        
        try {
            const response = await fetch(apiUrl, {
                headers: {
                    'Authorization': `Bearer ${this.sessionToken}`
                }
            });
            
            if (response.ok) {
                const data = await response.json();
                this.currentUser = data.user;
                return true;
            }
            return false;
        } catch (error) {
            console.error('Session validation error:', error);
            this.logError(error, 'Session Validation', { type: 'server_side' });
            // Fall back to client-side validation
            const storedUser = localStorage.getItem('currentUser');
            if (storedUser) {
                try {
                    this.currentUser = JSON.parse(storedUser);
                    const user = this.findUser(this.currentUser.email);
                    if (user) {
                        this.currentUser = { ...user };
                        localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
                        return true;
                    }
                } catch (err) {
                    console.error('Client-side session validation error:', err);
                    this.logError(err, 'Session Validation', { type: 'client_side_fallback' });
                }
            }
            return false;
        }
    }

    checkSubscriptionAndRedirect() {
        if (this.currentUser.isAdmin) {
            this.showPage('adminDashboard');
            this.resetIdleTimer(); // Start idle timer for admin
            return;
        }

        // Ensure user has required properties (migration for existing users)
        if (!this.currentUser.createdAt) {
            this.currentUser.createdAt = Date.now();
            this.updateUserRecord(this.currentUser);
            console.log('✅ Set createdAt for existing user');
        }

        const status = this.getSubscriptionStatus(this.currentUser);
        document.getElementById('userWelcomeName').textContent = this.currentUser.firstName;
        
        if (status.active) {
            this.showPage('dashboard');
            this.resetIdleTimer(); // Start idle timer for regular users
        } else {
            this.showPage('subscriptionPage');
            this.resetIdleTimer(); // Start idle timer even on subscription page
        }
    }

    getSubscriptionStatus(user) {
        const now = Date.now();
        const created = user.createdAt || now;
        
        if (user.subscriptionExpiry && new Date(user.subscriptionExpiry).getTime() > now) {
            return { active: true, type: 'subscription', expiry: new Date(user.subscriptionExpiry).getTime() };
        }

        const trialDuration = 24 * 60 * 60 * 1000; // 24 hours
        const trialEnd = created + trialDuration;
        
        if (now < trialEnd) {
            return { active: true, type: 'trial', expiry: trialEnd };
        }

        return { active: false, type: 'expired' };
    }

    findUser(email) { 
        const user = this.users.find(u => u.email === email);
        
        // Migrate existing users to have required properties if they don't have them
        if (user && !user.createdAt) {
            user.createdAt = Date.now();
            this.saveUsers(this.users);
            console.log(`✅ Migrated user ${email} to have createdAt`);
        }
        
        return user;
    }
    
    updateUserRecord(user) {
        const idx = this.users.findIndex(u => u.id === user.id);
        if (idx !== -1) {
            this.users[idx] = user;
            this.saveUsers(this.users);
            localStorage.setItem('currentUser', JSON.stringify(user));
        }
    }

    async logout() {
        try {
            if (this.sessionToken) {
                // Clear session on server if available
                const apiUrl = this.getApiUrl('auth/logout');
                if (apiUrl && this.serverRunning) {
                    try {
                        await fetch(apiUrl, {
                            method: 'POST',
                            headers: { 'Authorization': `Bearer ${this.sessionToken}` }
                        });
                    } catch (error) {
                        console.warn('Server logout failed:', error);
                    }
                }
            }
            
            // Clear local session using permanent storage
            this.currentUser = null;
            this.sessionToken = null;
            this.storage.remove('sessionToken');
            this.storage.remove('currentUser');
            
            // Clear timers
            if (this.idleTimer) {
                clearTimeout(this.idleTimer);
                this.idleTimer = null;
            }
            
            if (this.trialTimerInterval) {
                clearInterval(this.trialTimerInterval);
                this.trialTimerInterval = null;
            }
            
            // Redirect to landing page
            this.showPage('landingPage');
            this.showSuccess('Logged out successfully');
            
        } catch (error) {
            console.error('Logout error:', error);
            this.logError(error, 'Logout', { user: this.currentUser?.email });
            // Force logout even if there's an error
            this.currentUser = null;
            this.sessionToken = null;
            this.storage.remove('sessionToken');
            this.storage.remove('currentUser');
            this.showPage('landingPage');
        }
    }

    async handleLogin(event) {
        event.preventDefault();
        
        try {
            this.showLoading();
            
            const email = document.getElementById('loginEmail').value;
            const password = document.getElementById('loginPassword').value;
            
            if (!email || !password) {
                this.showError('Please enter both email and password');
                return;
            }
            
            // Try server login first
            const apiUrl = this.getApiUrl('auth/login');
            if (apiUrl && this.serverRunning) {
                try {
                    console.log('🔐 Attempting server login to:', apiUrl);
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email, password })
                    });
                    
                    console.log('📡 Server login response status:', response.status);
                    const data = await response.json();
                    console.log('📡 Server login response data:', data);
                    
                    if (data.success) {
                        this.sessionToken = data.sessionToken;
                        this.currentUser = data.user;
                        
                        // Store session using permanent storage
                        this.storage.set('sessionToken', this.sessionToken);
                        this.storage.set('currentUser', this.currentUser);
                        
                        console.log('✅ Server login successful for:', email);
                        
                        // Redirect based on user type
                        if (this.currentUser.isAdmin) {
                            this.showPage('adminDashboard');
                        } else {
                            this.showPage('dashboard');
                        }
                        
                        this.showSuccess('Login successful!');
                        return;
                    } else {
                        console.warn('❌ Server login failed:', data.error || data.message);
                        this.showError(data.error || data.message || 'Login failed');
                        return;
                    }
                } catch (error) {
                    console.warn('❌ Server login request failed:', error);
                    console.warn('🔄 Falling back to client-side authentication');
                }
            } else {
                console.log('🔄 Server not available, using client-side authentication');
            }
            
            // Fallback to client-side login
            console.log('🔄 Attempting client-side authentication');
            
            // Load users from localStorage - this should include all registered users
            let users = [];
            const storedUsers = localStorage.getItem('users');
            console.log('🔍 Raw localStorage check:', storedUsers ? 'Data exists' : 'No data');
            
            if (storedUsers) {
                try {
                    users = JSON.parse(storedUsers);
                    console.log('💾 Loaded users from localStorage:', users.length);
                    console.log('👥 Available emails:', users.map(u => u.email));
                } catch (error) {
                    console.error('❌ Error parsing stored users:', error);
                    users = [];
                }
            }
            
            // If no users exist, create default admin
            if (users.length === 0) {
                users = [{
                    id: 'admin-001',
                    firstName: 'Admin',
                    lastName: 'User',
                    email: 'talk2char@gmail.com',
                    password: 'Password@123',
                    isAdmin: true,
                    emailVerified: true,
                    createdAt: Date.now(),
                    subscriptionExpiry: null
                }];
                console.log('💾 Created default admin user');
                this.saveUsers(users);
            }
            
            // Update the class property to ensure consistency
            this.users = users;
            
            console.log('👥 Final user list:', users.map(u => ({ email: u.email, isAdmin: u.isAdmin })));
            const user = users.find(u => u.email.toLowerCase() === email.toLowerCase());
            console.log('🔍 Looking for user with email:', email);
            console.log('👤 Found user:', user ? { email: user.email, isAdmin: user.isAdmin } : 'Not found');
            
            if (!user) {
                this.showError('User not found');
                return;
            }
            
            if (user.password !== password) {
                this.showError('Invalid password');
                return;
            }
            
            if (!user.emailVerified) {
                this.showError('Please verify your email before logging in');
                return;
            }
            
            // Set session using permanent storage
            this.currentUser = user;
            this.sessionToken = 'client_' + Date.now();
            this.storage.set('sessionToken', this.sessionToken);
            this.storage.set('currentUser', this.currentUser);
            
            console.log('✅ Client-side login successful for:', email);
            
            // Redirect based on user type
            if (user.isAdmin) {
                this.showPage('adminDashboard');
            } else {
                this.showPage('dashboard');
            }
            
            this.showSuccess('Login successful!');
            
        } catch (error) {
            console.error('Login error:', error);
            this.logError(error, 'Login', { email });
            this.showError('Login failed. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    async handleRegister(event) {
        event.preventDefault();
        
        try {
            this.showLoading();
            
            const firstName = document.getElementById('firstName').value;
            const lastName = document.getElementById('lastName').value;
            const email = document.getElementById('registerEmail').value;
            const password = document.getElementById('registerPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const accessCode = document.getElementById('accessCode').value;
            const termsAccepted = document.getElementById('termsAccepted').checked;
            
            // Validation
            if (!firstName || !lastName || !email || !password || !confirmPassword) {
                this.showError('Please fill in all required fields');
                return;
            }
            
            if (password !== confirmPassword) {
                this.showError('Passwords do not match');
                return;
            }
            
            if (password.length < 8) {
                this.showError('Password must be at least 8 characters long');
                return;
            }
            
            if (!termsAccepted) {
                this.showError('Please accept the Terms of Use');
                return;
            }
            
            // Check if email already exists
            const users = this.loadUsers();
            if (users.find(u => u.email === email)) {
                this.showError('An account with this email already exists');
                return;
            }
            
            // Validate access code if provided
            let accessCodeData = null;
            if (accessCode) {
                // Try database first if server is running
                if (this.serverRunning) {
                    try {
                        // For access code validation, we don't need authentication
                        // Let's create a simple validation endpoint
                        const response = await fetch(this.getApiUrl('validate-access-code'), {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ code: accessCode })
                        });
                        
                        if (response.ok) {
                            const data = await response.json();
                            accessCodeData = data.accessCode;
                        }
                    } catch (error) {
                        console.warn('Database access code validation failed, trying storage:', error);
                    }
                }
                
                // Fallback to permanent storage
                if (!accessCodeData) {
                    const accessCodes = this.storage.get('accessCodes') || [];
                    accessCodeData = accessCodes.find(code => code.code === accessCode);
                }
                
                if (!accessCodeData) {
                    this.showError('Invalid access code');
                    return;
                }
            }
            
            // Try server registration first
            const apiUrl = this.getApiUrl('auth/register');
            if (apiUrl && this.serverRunning) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            firstName,
                            lastName,
                            email,
                            password,
                            accessCode
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        // Store email for verification purposes
                        localStorage.setItem('pendingVerificationEmail', email);
                        
                        this.showSuccess('Registration successful! Please check your email for verification.');
                        this.showPage('verificationPage');
                        
                        // Update verification page with email
                        const verificationEmailElement = document.getElementById('verificationEmail');
                        if (verificationEmailElement) {
                            verificationEmailElement.textContent = email;
                        }
                        
                        return;
                    } else {
                        // Handle both error formats and provide specific error messages
                        let errorMessage = data.error || data.message || 'Registration failed';
                        
                        // Provide more specific error messages
                        if (errorMessage.includes('already registered')) {
                            errorMessage = 'This email address is already registered. Please use a different email or try logging in.';
                        } else if (errorMessage.includes('Invalid access code')) {
                            errorMessage = 'The access code you entered is invalid or has expired. Please check the code and try again.';
                        } else if (errorMessage.includes('maximum uses')) {
                            errorMessage = 'This access code has reached its maximum number of uses. Please contact support for a new code.';
                        }
                        
                        this.showError(errorMessage);
                        console.error('❌ Registration failed:', errorMessage);
                        return;
                    }
                } catch (error) {
                    console.warn('Server registration failed, trying client-side:', error);
                }
            }
            
            // Fallback to client-side registration
            console.log('🔄 Falling back to client-side registration');
            
            // Create new user
            const newUser = {
                id: 'user_' + Date.now(),
                firstName,
                lastName,
                email,
                password, // In production, this should be hashed
                isAdmin: false,
                emailVerified: true, // Auto-verify in client mode
                createdAt: Date.now(),
                subscriptionExpiry: null,
                trialStarted: Date.now(),
                trialExpiry: Date.now() + (24 * 60 * 60 * 1000), // 24 hours
                accessLevel: 'trial'
            };
            
            // Apply access code benefits if provided
            if (accessCodeData) {
                const durationMs = accessCodeData.durationType === 'months' 
                    ? accessCodeData.duration * 30 * 24 * 60 * 60 * 1000
                    : accessCodeData.duration * 24 * 60 * 60 * 1000;
                
                newUser.trialExpiry = Date.now() + durationMs;
                newUser.accessLevel = 'extended';
                
                // Update access code usage
                accessCodeData.uses = (accessCodeData.uses || 0) + 1;
                const accessCodes = this.storage.get('accessCodes') || [];
                const updatedCodes = accessCodes.map(code => 
                    code.id === accessCodeData.id ? accessCodeData : code
                );
                this.storage.set('accessCodes', updatedCodes);
            }
            
            // Add user to users array
            users.push(newUser);
            console.log('👤 Created new user:', { email: newUser.email, id: newUser.id });
            console.log('👥 Total users before save:', users.length);
            
            this.saveUsers(users);
            
            // Verify the user was saved
            const savedUsers = this.loadUsers();
            console.log('👥 Total users after save:', savedUsers.length);
            console.log('✅ User saved successfully:', savedUsers.find(u => u.email === email) ? 'Yes' : 'No');
            
            // Auto-login the new user using permanent storage
            this.currentUser = newUser;
            this.sessionToken = 'client_' + Date.now();
            this.storage.set('sessionToken', this.sessionToken);
            this.storage.set('currentUser', this.currentUser);
            
            // Check if user came from pricing selection
            const selectedPlan = JSON.parse(localStorage.getItem('selectedPlan') || 'null');
            if (selectedPlan) {
                localStorage.removeItem('selectedPlan');
                this.showSuccess('Registration successful! Please complete your payment.');
                this.showPaymentModal(selectedPlan.plan, selectedPlan.amount);
            } else {
                // Show success and redirect to dashboard
                this.showSuccess('Registration successful! Welcome to HRLA!');
                this.showPage('dashboard');
            }
            
        } catch (error) {
            console.error('Registration error:', error);
            this.logError(error, 'Registration', { email });
            this.showError('Registration failed. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    async resendVerificationEmail() {
        console.log('📧 Resending verification email...');
        
        try {
            this.showLoading();
            
            // Get the email from the verification page or stored data
            const email = document.getElementById('verificationEmail')?.textContent || 
                         localStorage.getItem('pendingVerificationEmail');
            
            if (!email) {
                this.showError('No email found for verification. Please register again.');
                return;
            }
            
            // Try server resend first
            const apiUrl = this.getApiUrl('auth/resend-verification');
            if (apiUrl && this.serverRunning) {
                try {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ email })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.showSuccess('Verification email sent! Please check your inbox.');
                        return;
                    } else {
                        this.showError(data.message || 'Failed to resend verification email');
                        return;
                    }
                } catch (error) {
                    console.warn('Server resend failed, using client-side:', error);
                }
            }
            
            // Client-side fallback - just show success message
            this.showSuccess('Verification email would be sent in production. For testing, check the server console for the verification link.');
            
        } catch (error) {
            console.error('Resend verification error:', error);
            this.logError(error, 'Resend Verification', { email });
            this.showError('Failed to resend verification email. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    bindAdminEvents() {
        console.log('🔧 Binding admin dashboard events...');
        
        // Admin tab switching
        const tabButtons = document.querySelectorAll('.tab-btn[data-tab]');
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                const tabName = e.target.closest('.tab-btn').getAttribute('data-tab');
                this.switchAdminTab(tabName);
            });
        });
        
        // Admin navigation buttons
        document.getElementById('adminSettingsBtn')?.addEventListener('click', () => {
            console.log('🔘 Admin settings clicked');
            this.showPage('adminProfilePage');
        });
        
        document.getElementById('adminLogoutBtn')?.addEventListener('click', () => {
            console.log('🔘 Admin logout clicked');
            this.logout();
        });
        
        document.getElementById('adminLogoutBtn2')?.addEventListener('click', () => {
            console.log('🔘 Admin logout 2 clicked');
            this.logout();
        });
        
        document.getElementById('backToAdminDashboard')?.addEventListener('click', () => {
            console.log('🔘 Back to admin dashboard clicked');
            this.showPage('adminDashboard');
        });
        
        // Statistics cards (clickable filters)
        const statCards = document.querySelectorAll('.stat-card.clickable-card');
        statCards.forEach(card => {
            card.addEventListener('click', (e) => {
                const filter = e.target.closest('.stat-card').getAttribute('data-filter');
                if (filter) {
                    console.log('📊 Stat card clicked:', filter);
                    this.filterUsers(filter);
                }
            });
        });
        
        // Bulk grant access and export CSV buttons
        document.getElementById('bulkGrantBtn')?.addEventListener('click', () => {
            console.log('🔘 Bulk grant access clicked');
            this.showBulkGrantModal();
        });
        
        document.getElementById('exportUsers')?.addEventListener('click', () => {
            console.log('🔘 Export users CSV clicked');
            this.exportUsersCSV();
        });
        
        // Bulk grant filtered button
        document.getElementById('bulkGrantFiltered')?.addEventListener('click', () => {
            console.log('🔘 Bulk grant filtered clicked');
            this.showBulkGrantModal();
        });
        
        // Select all filtered button
        document.getElementById('selectAllFiltered')?.addEventListener('click', () => {
            console.log('🔘 Select all filtered clicked');
            this.selectAllFilteredUsers();
        });
        
        // Storage management buttons
        document.getElementById('refreshStorageStats')?.addEventListener('click', () => {
            console.log('🔘 Refresh storage stats clicked');
            this.loadStorageTab();
        });
        
        document.getElementById('createStorageBackup')?.addEventListener('click', () => {
            console.log('🔘 Create backup clicked');
            this.createBackup();
        });
        
        document.getElementById('restoreStorageBackup')?.addEventListener('click', () => {
            console.log('🔘 Restore backup clicked');
            this.restoreBackup();
        });
        
        document.getElementById('debugStorageData')?.addEventListener('click', () => {
            console.log('🔘 Debug storage clicked');
            this.debugStorageData();
        });
        
        document.getElementById('exportDatabaseData')?.addEventListener('click', () => {
            console.log('🔘 Export database clicked');
            this.exportDatabaseData();
        });
        
        document.getElementById('clearAllStorageData')?.addEventListener('click', () => {
            console.log('🔘 Clear all data clicked');
            this.adminClearAllData();
        });
        
        console.log('✅ Admin events bound successfully');
    }

    bindDashboardEvents() {
        console.log('🔧 Binding user dashboard events...');
        
        // Tool cards
        document.getElementById('federalTool')?.addEventListener('click', () => {
            console.log('🔘 Federal tool clicked');
            this.showPage('federalPage');
        });
        
        document.getElementById('californiaTool')?.addEventListener('click', () => {
            console.log('🔘 California tool clicked');
            this.showPage('californiaPage');
        });
        
        // Back to dashboard buttons
        document.getElementById('backToDashboard1')?.addEventListener('click', () => {
            console.log('🔘 Back to dashboard 1 clicked');
            this.showPage('dashboard');
        });
        
        document.getElementById('backToDashboard2')?.addEventListener('click', () => {
            console.log('🔘 Back to dashboard 2 clicked');
            this.showPage('dashboard');
        });
        
        document.getElementById('backToDashboardFromCancel')?.addEventListener('click', () => {
            console.log('🔘 Back to dashboard from cancel clicked');
            this.showPage('dashboard');
        });
        
        // Subscription page back button
        document.getElementById('backToHomepage')?.addEventListener('click', () => {
            console.log('🔘 Back to dashboard from subscription clicked');
            this.showPage('dashboard');
        });
        
        // Settings and logout for regular users
        document.getElementById('settingsBtn')?.addEventListener('click', () => {
            console.log('🔘 Settings clicked');
            this.showSettings();
        });
        
        document.getElementById('settingsBtn2')?.addEventListener('click', () => {
            console.log('🔘 Settings 2 clicked');
            this.showSettings();
        });
        
        document.getElementById('settingsBtn3')?.addEventListener('click', () => {
            console.log('🔘 Settings 3 clicked');
            this.showSettings();
        });
        
        // Settings modal close
        document.getElementById('closeSettings')?.addEventListener('click', () => {
            console.log('🔘 Close settings clicked');
            this.hideSettings();
        });
        
        // Settings form submission
        document.getElementById('settingsForm')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSettingsUpdate();
        });
        
        document.getElementById('logoutBtn')?.addEventListener('click', () => {
            console.log('🔘 Logout clicked');
            this.logout();
        });
        
        // Upgrade button
        document.getElementById('upgradeBtn')?.addEventListener('click', () => {
            console.log('🔘 Upgrade clicked');
            this.showUpgradePlans();
        });
        
        // AI submission buttons
        document.getElementById('federalSubmit')?.addEventListener('click', () => {
            console.log('🔘 Federal submit clicked');
            this.handleAISubmit('federal');
        });
        
        document.getElementById('californiaSubmit')?.addEventListener('click', () => {
            console.log('🔘 California submit clicked');
            this.handleAISubmit('california');
        });
        
        // Clear buttons
        document.getElementById('federalClear')?.addEventListener('click', () => {
            console.log('🔘 Federal clear clicked');
            this.clearTool('federal');
        });
        
        document.getElementById('californiaClear')?.addEventListener('click', () => {
            console.log('🔘 California clear clicked');
            this.clearTool('california');
        });
        
        // Copy buttons
        document.getElementById('federalCopy')?.addEventListener('click', () => {
            console.log('🔘 Federal copy clicked');
            this.copyOutput('federal');
        });
        
        document.getElementById('californiaCopy')?.addEventListener('click', () => {
            console.log('🔘 California copy clicked');
            this.copyOutput('california');
        });
        
        // Pricing subscription buttons
        document.querySelectorAll('.subscribe-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const plan = e.target.getAttribute('data-plan');
                const amount = e.target.getAttribute('data-amount');
                console.log('💳 Subscription clicked:', plan, amount);
                this.handleSubscriptionClick(plan, amount);
            });
        });
        
        console.log('✅ Dashboard events bound successfully');
    }

    switchAdminTab(tabName) {
        console.log('🔄 Switching to admin tab:', tabName);
        
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`)?.classList.add('active');
        
        // Update tab content - convert kebab-case to camelCase + Tab
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Convert data-tab names to actual HTML IDs
        const tabIdMap = {
            'users': 'usersTab',
            'payments': 'paymentsTab', 
            'email': 'emailTab',
            'system': 'systemTab',
            'access-codes': 'accessCodesTab',
            'api-settings': 'apiSettingsTab',
            'deployment': 'deploymentTab'
        };
        
        const targetTabId = tabIdMap[tabName];
        const targetTab = document.getElementById(targetTabId);
        
        if (targetTab) {
            targetTab.classList.add('active');
            console.log('✅ Successfully switched to tab:', targetTabId);
            
            // Load tab-specific content
            this.loadAdminTabContent(tabName);
        } else {
            console.warn('⚠️ Tab content not found:', targetTabId);
        }
    }

    loadAdminTabContent(tabName) {
        console.log('📄 Loading content for tab:', tabName);
        
        switch (tabName) {
            case 'users':
                this.loadUsersTab();
                break;
            case 'payments':
                this.loadPaymentsTab();
                break;
            case 'email':
                this.loadEmailTab();
                break;
            case 'system':
                this.loadSystemTab();
                break;
            case 'access-codes':
                this.loadAccessCodesTab();
                break;
            case 'api-settings':
                this.loadApiSettingsTab();
                break;
            case 'deployment':
                this.loadDeploymentTab();
                break;
            case 'storage':
                this.loadStorageTab();
                break;
            default:
                console.warn('⚠️ Unknown tab:', tabName);
        }
    }

    async loadUsersTab() {
        console.log('👥 Loading users tab...');
        
        let users = [];
        
        // Try to load from server first if available
        if (this.serverRunning && this.sessionToken) {
            try {
                const response = await fetch(this.getApiUrl('admin/users'), {
                    headers: {
                        'Authorization': `Bearer ${this.sessionToken}`
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.users) {
                        users = data.users;
                        console.log('📊 Users loaded from server:', users.length);
                        console.log('👥 User emails from server:', users.map(u => u.email));
                    }
                }
            } catch (error) {
                console.warn('Failed to load users from server:', error);
            }
        }
        
        // Fallback to local storage if server failed or no users from server
        if (users.length === 0) {
            users = this.loadUsers();
            console.log('📊 Users loaded from localStorage:', users.length);
            console.log('👥 User emails from localStorage:', users.map(u => u.email));
        }
        
        // Populate users table
        this.populateUsersTable(users);
        
        // Bind user table events
        this.bindUserTableEvents();
    }

    populateUsersTable(users, filter = 'all') {
        console.log('📋 Populating users table with filter:', filter);
        
        // Filter users based on the filter type
        let filteredUsers = users;
        
        switch (filter) {
            case 'all':
                filteredUsers = users;
                break;
            case 'verified':
                filteredUsers = users.filter(u => u.emailVerified);
                break;
            case 'unverified':
                filteredUsers = users.filter(u => !u.emailVerified);
                break;
            case 'subscribed':
                filteredUsers = users.filter(u => u.subscriptionExpiry && u.subscriptionExpiry > Date.now());
                break;
            case 'trial':
                filteredUsers = users.filter(u => !u.isAdmin && (!u.subscriptionExpiry || u.subscriptionExpiry <= Date.now()));
                break;
            case 'expired':
                filteredUsers = users.filter(u => u.trialExpiry && u.trialExpiry < Date.now() && (!u.subscriptionExpiry || u.subscriptionExpiry <= Date.now()));
                break;
        }
        
        console.log('📊 Filtered users:', filteredUsers.length, 'of', users.length);
        
        const tableBody = document.getElementById('usersTableBody');
        if (!tableBody) {
            console.warn('⚠️ Users table body not found');
            return;
        }
        
        if (filteredUsers.length === 0) {
            tableBody.innerHTML = '<tr><td colspan="7" class="text-center">No users found</td></tr>';
            return;
        }
        
        tableBody.innerHTML = filteredUsers.map(user => {
            const status = this.getUserStatus(user);
            const subscription = this.getUserSubscription(user);
            const plan = this.getUserPlan(user);
            const createdDate = new Date(user.createdAt).toLocaleDateString();
            
            return `
                <tr>
                    <td><input type="checkbox" class="user-checkbox" value="${user.id}"></td>
                    <td>${user.firstName} ${user.lastName}</td>
                    <td>${user.email}</td>
                    <td><span class="status-badge status-${status.toLowerCase()}">${status}</span></td>
                    <td>${plan}</td>
                    <td>${createdDate}</td>
                    <td>
                        <button class="btn btn-sm btn-primary view-user-btn" data-user-id="${user.id}">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${!user.isAdmin ? `
                            <button class="btn btn-sm btn-success grant-access-btn" data-user-id="${user.id}">
                                <i class="fas fa-key"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-user-btn" data-user-id="${user.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    }

    getUserStatus(user) {
        if (user.isAdmin) return 'Admin';
        if (!user.emailVerified) return 'Unverified';
        if (user.subscriptionExpiry && user.subscriptionExpiry > Date.now()) return 'Subscribed';
        if (user.trialExpiry && user.trialExpiry > Date.now()) return 'Trial';
        return 'Expired';
    }

    getUserSubscription(user) {
        if (user.isAdmin) return 'Admin';
        if (user.subscriptionExpiry && user.subscriptionExpiry > Date.now()) {
            const expiry = new Date(user.subscriptionExpiry).toLocaleDateString();
            return `Active (${expiry})`;
        }
        if (user.trialExpiry && user.trialExpiry > Date.now()) {
            const expiry = new Date(user.trialExpiry).toLocaleDateString();
            return `Trial (${expiry})`;
        }
        return 'Expired';
    }

    getUserPlan(user) {
        if (user.isAdmin) return 'Admin';
        if (user.subscriptionPlan) {
            const planNames = {
                'monthly': 'Monthly ($29/mo)',
                'annual': 'Annual ($290/yr)',
                'organization': 'Organization ($580/yr)'
            };
            return planNames[user.subscriptionPlan] || user.subscriptionPlan;
        }
        if (user.accessLevel === 'extended') return 'Extended Trial';
        if (user.accessLevel === 'granted') return 'Granted Access';
        return 'Free Trial';
    }

    bindUserTableEvents() {
        console.log('🔗 Binding user table events...');
        
        // View user buttons
        document.querySelectorAll('.view-user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.target.closest('.view-user-btn').getAttribute('data-user-id');
                this.viewUser(userId);
            });
        });
        
        // Grant access buttons
        document.querySelectorAll('.grant-access-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.target.closest('.grant-access-btn').getAttribute('data-user-id');
                this.grantUserAccess(userId);
            });
        });
        
        // Delete user buttons
        document.querySelectorAll('.delete-user-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const userId = e.target.closest('.delete-user-btn').getAttribute('data-user-id');
                this.deleteUser(userId);
            });
        });
        
        // Select all checkbox
        const selectAllCheckbox = document.getElementById('selectAllUsers');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                const checkboxes = document.querySelectorAll('.user-checkbox');
                checkboxes.forEach(cb => cb.checked = e.target.checked);
            });
        }
    }

    loadPaymentsTab() {
        console.log('💳 Loading payments tab...');
        // Load payment configuration
        // TODO: Load payment settings
    }

    loadEmailTab() {
        console.log('📧 Loading email tab...');
        // Load email configuration
        // TODO: Load email settings
    }

    loadSystemTab() {
        console.log('⚙️ Loading system tab...');
        // Load system settings
        // TODO: Load system configuration
    }

    async loadAccessCodesTab() {
        console.log('🔑 Loading access codes tab...');
        
        let accessCodes = [];
        
        // Try database first if server is running
        if (this.serverRunning && this.sessionToken) {
            try {
                accessCodes = await this.dbClient.getAccessCodes();
                if (accessCodes && accessCodes.length > 0) {
                    console.log('📊 Access codes loaded from database:', accessCodes.length);
                }
            } catch (error) {
                console.warn('Failed to load access codes from database:', error);
            }
        }
        
        // Fallback to permanent storage
        if (!accessCodes || accessCodes.length === 0) {
            accessCodes = this.storage.get('accessCodes') || [];
            console.log('📊 Access codes loaded from permanent storage:', accessCodes.length);
        }
        
        // Update the table body
        const tableBody = document.getElementById('accessCodesTableBody');
        if (tableBody) {
            if (accessCodes.length === 0) {
                tableBody.innerHTML = '<tr><td colspan="6" class="text-center">No access codes generated yet</td></tr>';
            } else {
                tableBody.innerHTML = accessCodes.map(code => `
                    <tr>
                        <td><code>${code.code}</code></td>
                        <td>${code.duration} ${code.durationType}</td>
                        <td>${code.description || '-'}</td>
                        <td>${code.uses || 0}</td>
                        <td>${new Date(code.createdAt).toLocaleDateString()}</td>
                        <td>
                            <button class="btn btn-sm btn-danger" onclick="app.deleteAccessCode('${code.id}')">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `).join('');
            }
        }
        
        // Bind access code form if it exists
        const form = document.getElementById('generateAccessCodeForm');
        if (form) {
            form.onsubmit = (e) => {
                e.preventDefault();
                this.generateAccessCode();
            };
        }
    }

    async loadApiSettingsTab() {
        console.log('🔧 Loading API settings tab...');
        
        let config = null;
        
        // Try database first if server is running
        if (this.serverRunning && this.sessionToken) {
            config = await this.dbClient.getApiConfig();
            if (config) {
                console.log('📊 API config loaded from database');
            }
        }
        
        // Fallback to permanent storage
        if (!config) {
            config = this.storage.get('apiConfig') || {};
            console.log('📊 API config loaded from permanent storage');
        }
        
        console.log('📊 API config keys:', Object.keys(config));
        
        // Update API key field if it exists
        const apiKeyField = document.getElementById('systemOpenaiKey');
        if (apiKeyField && config.openaiKey) {
            apiKeyField.value = config.openaiKey;
        }
        
        // Update API status
        const statusIcon = document.getElementById('apiStatusIcon');
        const statusText = document.getElementById('apiStatusText');
        
        if (statusIcon && statusText) {
            if (config.openaiKey) {
                statusIcon.className = 'fas fa-circle text-success';
                statusText.textContent = 'API key configured';
            } else {
                statusIcon.className = 'fas fa-circle text-danger';
                statusText.textContent = 'Not configured';
            }
        }
        
        // Update usage statistics
        const totalRequests = document.getElementById('totalRequests');
        const openaiRequests = document.getElementById('openaiRequests');
        
        if (totalRequests) totalRequests.textContent = config.totalRequests || '0';
        if (openaiRequests) openaiRequests.textContent = config.openaiRequests || '0';
        
        // Bind API settings form
        const form = document.getElementById('apiSettingsForm');
        if (form) {
            form.onsubmit = (e) => {
                e.preventDefault();
                this.saveApiSettings();
            };
        }
        
        // Bind test API key button
        const testBtn = document.getElementById('testApiKey');
        if (testBtn) {
            testBtn.onclick = () => this.testApiKey();
        }
    }

    loadDeploymentTab() {
        console.log('🚀 Loading deployment tab...');
        // Load deployment information
        // TODO: Load deployment status
    }

    filterUsers(filter) {
        console.log('🔍 Filtering users by:', filter);
        
        // Load all users
        const users = this.loadUsers();
        
        // Update the users table with the filter
        this.populateUsersTable(users, filter);
        
        // Update active filter styling
        document.querySelectorAll('.stat-card').forEach(card => {
            card.classList.remove('active-filter');
        });
        
        const activeCard = document.querySelector(`[data-filter="${filter}"]`);
        if (activeCard) {
            activeCard.classList.add('active-filter');
        }
        
        this.showSuccess(`Showing ${filter} users`);
    }

    viewUser(userId) {
        console.log('👁️ Viewing user:', userId);
        const users = this.loadUsers();
        const user = users.find(u => u.id === userId);
        
        if (user) {
            // Create detailed user modal (enhanced version)
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>User Details: ${user.firstName} ${user.lastName}</h3>
                        <button class="btn-close" onclick="this.closest('.modal').remove()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="user-detail-grid">
                            <div class="detail-section">
                                <h4>Personal Information</h4>
                                <p><strong>Name:</strong> ${user.firstName} ${user.lastName}</p>
                                <p><strong>Email:</strong> ${user.email}</p>
                                <p><strong>User ID:</strong> ${user.id}</p>
                                <p><strong>Created:</strong> ${new Date(user.createdAt).toLocaleString()}</p>
                            </div>
                            <div class="detail-section">
                                <h4>Account Status</h4>
                                <p><strong>Status:</strong> <span class="status-badge status-${this.getUserStatus(user).toLowerCase()}">${this.getUserStatus(user)}</span></p>
                                <p><strong>Email Verified:</strong> ${user.emailVerified ? 'Yes' : 'No'}</p>
                                <p><strong>Admin:</strong> ${user.isAdmin ? 'Yes' : 'No'}</p>
                                <p><strong>Access Level:</strong> ${user.accessLevel || 'trial'}</p>
                            </div>
                            <div class="detail-section">
                                <h4>Subscription Details</h4>
                                <p><strong>Current Plan:</strong> ${this.getUserPlan(user)}</p>
                                <p><strong>Trial Started:</strong> ${user.trialStarted ? new Date(user.trialStarted).toLocaleString() : 'N/A'}</p>
                                <p><strong>Trial Expires:</strong> ${user.trialExpiry ? new Date(user.trialExpiry).toLocaleString() : 'N/A'}</p>
                                <p><strong>Subscription:</strong> ${this.getUserSubscription(user)}</p>
                                ${user.subscriptionAmount ? `<p><strong>Last Payment:</strong> $${user.subscriptionAmount}</p>` : ''}
                            </div>
                        </div>
                        <div class="user-actions">
                            <button class="btn btn-primary" onclick="app.editUser('${user.id}')">Edit User</button>
                            <button class="btn btn-success" onclick="app.grantUserAccess('${user.id}')">Grant Access</button>
                            <button class="btn btn-warning" onclick="app.resetUserPassword('${user.id}')">Reset Password</button>
                            ${!user.isAdmin ? `<button class="btn btn-danger" onclick="app.deleteUser('${user.id}')">Delete User</button>` : ''}
                        </div>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
    }

    editUser(userId) {
        console.log('✏️ Editing user:', userId);
        const users = this.loadUsers();
        const user = users.find(u => u.id === userId);
        
        if (user) {
            const modal = document.createElement('div');
            modal.className = 'modal';
            modal.innerHTML = `
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>Edit User: ${user.firstName} ${user.lastName}</h3>
                        <button class="btn-close" onclick="this.closest('.modal').remove()">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form id="editUserForm">
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" id="editFirstName" value="${user.firstName}" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" id="editLastName" value="${user.lastName}" required>
                            </div>
                            <div class="form-group">
                                <label>Email</label>
                                <input type="email" id="editEmail" value="${user.email}" required>
                            </div>
                            <div class="form-group">
                                <label>Email Verified</label>
                                <select id="editEmailVerified">
                                    <option value="true" ${user.emailVerified ? 'selected' : ''}>Yes</option>
                                    <option value="false" ${!user.emailVerified ? 'selected' : ''}>No</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Access Level</label>
                                <select id="editAccessLevel">
                                    <option value="trial" ${user.accessLevel === 'trial' ? 'selected' : ''}>Trial</option>
                                    <option value="extended" ${user.accessLevel === 'extended' ? 'selected' : ''}>Extended</option>
                                    <option value="granted" ${user.accessLevel === 'granted' ? 'selected' : ''}>Granted</option>
                                    <option value="subscribed" ${user.accessLevel === 'subscribed' ? 'selected' : ''}>Subscribed</option>
                                </select>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-primary" onclick="app.saveUserEdit('${user.id}')">Save Changes</button>
                                <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
    }

    saveUserEdit(userId) {
        console.log('💾 Saving user edit:', userId);
        
        const firstName = document.getElementById('editFirstName').value;
        const lastName = document.getElementById('editLastName').value;
        const email = document.getElementById('editEmail').value;
        const emailVerified = document.getElementById('editEmailVerified').value === 'true';
        const accessLevel = document.getElementById('editAccessLevel').value;
        
        if (!firstName || !lastName || !email) {
            this.showError('Please fill in all required fields');
            return;
        }
        
        const users = this.loadUsers();
        const userIndex = users.findIndex(u => u.id === userId);
        
        if (userIndex !== -1) {
            // Check if email is unique (excluding current user)
            const emailExists = users.some(u => u.email === email && u.id !== userId);
            if (emailExists) {
                this.showError('Email already exists');
                return;
            }
            
            users[userIndex].firstName = firstName;
            users[userIndex].lastName = lastName;
            users[userIndex].email = email;
            users[userIndex].emailVerified = emailVerified;
            users[userIndex].accessLevel = accessLevel;
            
            this.saveUsers(users);
            this.loadUsersTab(); // Refresh the table
            this.loadAdminStats(); // Update stats
            
            // Close modal
            document.querySelector('.modal')?.remove();
            
            this.showSuccess('User updated successfully');
        }
    }

    resetUserPassword(userId) {
        console.log('🔑 Resetting password for user:', userId);
        
        const newPassword = prompt('Enter new password for user (minimum 8 characters):');
        if (!newPassword) return;
        
        if (newPassword.length < 8) {
            this.showError('Password must be at least 8 characters long');
            return;
        }
        
        const users = this.loadUsers();
        const userIndex = users.findIndex(u => u.id === userId);
        
        if (userIndex !== -1 && !users[userIndex].isAdmin) {
            users[userIndex].password = newPassword; // In production, this should be hashed
            this.saveUsers(users);
            
            // Close any open modals
            document.querySelector('.modal')?.remove();
            
            this.showSuccess('Password reset successfully');
        }
    }

    grantUserAccess(userId) {
        console.log('🔑 Granting access to user:', userId);
        
        const users = this.loadUsers();
        const user = users.find(u => u.id === userId);
        
        if (!user || user.isAdmin) {
            this.showError('User not found or cannot grant access to admin');
            return;
        }
        
        // Show modal to ask for number of days
        const modal = document.createElement('div');
        modal.className = 'modal grant-access-modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h3>Grant Access to ${user.firstName} ${user.lastName}</h3>
                    <button class="btn-close" onclick="this.closest('.modal').remove()">&times;</button>
                </div>
                <div class="modal-body">
                    <form id="grantAccessForm">
                        <div class="form-group">
                            <label for="accessDays">Number of Days</label>
                            <input type="number" id="accessDays" min="1" max="365" value="30" required>
                            <small class="form-hint">Enter the number of days to grant access (1-365)</small>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Grant Access</button>
                            <button type="button" class="btn btn-secondary" onclick="this.closest('.modal').remove()">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Handle form submission
        const form = modal.querySelector('#grantAccessForm');
        form.addEventListener('submit', (e) => {
            e.preventDefault();
            const days = parseInt(document.getElementById('accessDays').value);
            
            if (days < 1 || days > 365) {
                this.showError('Please enter a valid number of days (1-365)');
                return;
            }
            
            // Grant access for specified days
            const daysMs = days * 24 * 60 * 60 * 1000;
            user.subscriptionExpiry = Date.now() + daysMs;
            user.accessLevel = 'granted';
            
            this.saveUsers(users);
            this.loadUsersTab(); // Refresh the table
            this.loadAdminStats(); // Update stats
            
            modal.remove();
            this.showSuccess(`Access granted to ${user.firstName} ${user.lastName} for ${days} day${days > 1 ? 's' : ''}`);
        });
    }

    deleteUser(userId) {
        console.log('🗑️ Deleting user:', userId);
        
        if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
            const users = this.loadUsers();
            const userIndex = users.findIndex(u => u.id === userId);
            
            if (userIndex !== -1 && !users[userIndex].isAdmin) {
                const deletedUser = users[userIndex];
                users.splice(userIndex, 1);
                
                this.saveUsers(users);
                this.loadUsersTab(); // Refresh the table
                this.loadAdminStats(); // Update stats
                
                this.showSuccess(`User ${deletedUser.firstName} ${deletedUser.lastName} deleted`);
            }
        }
    }

    showBulkGrantModal() {
        console.log('📋 Showing bulk grant modal');
        
        // Get selected users
        const selectedCheckboxes = document.querySelectorAll('.user-checkbox:checked');
        const selectedUserIds = Array.from(selectedCheckboxes).map(cb => cb.value);
        
        if (selectedUserIds.length === 0) {
            this.showError('Please select at least one user to grant access');
            return;
        }
        
        // Update the count in the modal
        const bulkCountElement = document.getElementById('bulkCount');
        if (bulkCountElement) {
            bulkCountElement.textContent = selectedUserIds.length;
        }
        
        // Show the modal
        const modal = document.getElementById('bulkGrantModal');
        if (modal) {
            modal.classList.remove('hidden');
            
            // Bind the form submission if not already bound
            const form = document.getElementById('bulkGrantForm');
            if (form && !form.hasAttribute('data-bound')) {
                form.setAttribute('data-bound', 'true');
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.processBulkGrant(selectedUserIds);
                });
            }
            
            // Bind grant type change to show/hide custom days
            const grantTypeSelect = document.getElementById('grantType');
            const customDaysGroup = document.getElementById('customDaysGroup');
            
            if (grantTypeSelect && !grantTypeSelect.hasAttribute('data-bound')) {
                grantTypeSelect.setAttribute('data-bound', 'true');
                grantTypeSelect.addEventListener('change', (e) => {
                    if (e.target.value === 'custom') {
                        customDaysGroup.style.display = 'block';
                        document.getElementById('customDays').required = true;
                    } else {
                        customDaysGroup.style.display = 'none';
                        document.getElementById('customDays').required = false;
                    }
                });
            }
            
            // Bind close button
            const closeBtn = document.getElementById('closeBulkGrant');
            if (closeBtn && !closeBtn.hasAttribute('data-bound')) {
                closeBtn.setAttribute('data-bound', 'true');
                closeBtn.addEventListener('click', () => {
                    modal.classList.add('hidden');
                });
            }
        }
    }

    processBulkGrant(userIds) {
        console.log('🔑 Processing bulk grant for users:', userIds);
        
        const grantType = document.getElementById('grantType').value;
        const customDays = document.getElementById('customDays').value;
        
        let days;
        switch (grantType) {
            case '7':
                days = 7;
                break;
            case '30':
                days = 30;
                break;
            case '90':
                days = 90;
                break;
            case 'custom':
                days = parseInt(customDays);
                if (!days || days < 1 || days > 365) {
                    this.showError('Please enter a valid number of days (1-365)');
                    return;
                }
                break;
            default:
                this.showError('Please select a valid grant type');
                return;
        }
        
        const users = this.loadUsers();
        let grantedCount = 0;
        
        userIds.forEach(userId => {
            const user = users.find(u => u.id === userId);
            if (user && !user.isAdmin) {
                const daysMs = days * 24 * 60 * 60 * 1000;
                user.subscriptionExpiry = Date.now() + daysMs;
                user.accessLevel = 'granted';
                grantedCount++;
            }
        });
        
        if (grantedCount > 0) {
            this.saveUsers(users);
            this.loadUsersTab(); // Refresh the table
            this.loadAdminStats(); // Update stats
            
            // Hide modal
            document.getElementById('bulkGrantModal').classList.add('hidden');
            
            // Clear selections
            document.querySelectorAll('.user-checkbox:checked').forEach(cb => cb.checked = false);
            
            this.showSuccess(`Access granted to ${grantedCount} user${grantedCount > 1 ? 's' : ''} for ${days} day${days > 1 ? 's' : ''}`);
        } else {
            this.showError('No users were granted access');
        }
    }

    selectAllFilteredUsers() {
        console.log('☑️ Selecting all filtered users');
        
        const checkboxes = document.querySelectorAll('.user-checkbox');
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
        });
        
        const selectedCount = document.querySelectorAll('.user-checkbox:checked').length;
        this.showSuccess(`${allChecked ? 'Deselected' : 'Selected'} ${selectedCount} users`);
    }

    exportUsersCSV() {
        console.log('📊 Exporting users to CSV');
        
        const users = this.loadUsers();
        const nonAdminUsers = users.filter(u => !u.isAdmin);
        
        if (nonAdminUsers.length === 0) {
            this.showError('No users to export');
            return;
        }
        
        // Create CSV headers
        const headers = [
            'First Name',
            'Last Name', 
            'Email',
            'Status',
            'Access Level',
            'Email Verified',
            'Created Date',
            'Trial Expiry',
            'Subscription Expiry'
        ];
        
        // Create CSV rows
        const rows = nonAdminUsers.map(user => {
            const now = Date.now();
            const trialEnd = user.trialExpiry || (user.createdAt + (24 * 60 * 60 * 1000));
            const hasActiveSubscription = user.subscriptionExpiry && user.subscriptionExpiry > now;
            const inTrial = trialEnd > now && !hasActiveSubscription;
            
            let status = 'Expired';
            if (hasActiveSubscription) status = 'Subscribed';
            else if (inTrial) status = 'Trial';
            
            return [
                user.firstName || '',
                user.lastName || '',
                user.email || '',
                status,
                user.accessLevel || 'trial',
                user.emailVerified ? 'Yes' : 'No',
                user.createdAt ? new Date(user.createdAt).toLocaleDateString() : '',
                user.trialExpiry ? new Date(user.trialExpiry).toLocaleDateString() : '',
                user.subscriptionExpiry ? new Date(user.subscriptionExpiry).toLocaleDateString() : ''
            ];
        });
        
        // Combine headers and rows
        const csvContent = [headers, ...rows]
            .map(row => row.map(field => `"${field}"`).join(','))
            .join('\n');
        
        // Create and download file
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        
        link.setAttribute('href', url);
        link.setAttribute('download', `users_export_${new Date().toISOString().split('T')[0]}.csv`);
        link.style.visibility = 'hidden';
        
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        this.showSuccess(`Exported ${nonAdminUsers.length} users to CSV`);
    }

    showSettings() {
        console.log('⚙️ Showing settings modal');
        const settingsModal = document.getElementById('settingsModal');
        if (settingsModal) {
            settingsModal.classList.remove('hidden');
        }
    }

    clearTool(tool) {
        console.log('🧹 Clearing tool:', tool);
        const inputElement = document.getElementById(`${tool}Input`);
        const outputElement = document.getElementById(`${tool}Output`);
        
        if (inputElement) inputElement.value = '';
        if (outputElement) outputElement.innerHTML = '';
        
        this.showSuccess(`${tool} tool cleared`);
    }

    async ensureServerSession() {
        // If we already have a server session, we're good
        if (this.sessionToken && !this.sessionToken.startsWith('client_')) {
            return true;
        }
        
        // If we have a client session, try to convert it to a server session
        if (this.currentUser && this.sessionToken?.startsWith('client_')) {
            console.log('🔄 Converting client session to server session...');
            
            try {
                const apiUrl = this.getApiUrl('auth/login');
                if (apiUrl && this.serverRunning) {
                    const response = await fetch(apiUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            email: this.currentUser.email,
                            password: this.currentUser.password
                        })
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.sessionToken = data.sessionToken;
                        this.currentUser = data.user;
                        
                        // Update stored session
                        this.storage.set('sessionToken', this.sessionToken);
                        this.storage.set('currentUser', this.currentUser);
                        
                        console.log('✅ Successfully converted to server session');
                        return true;
                    }
                }
            } catch (error) {
                console.warn('Failed to convert to server session:', error);
            }
        }
        
        return false;
    }

    async handleAISubmit(tool) {
        console.log('🤖 Handling AI submit for tool:', tool);
        
        try {
            this.showLoading();
            
            const inputElement = document.getElementById(`${tool}Input`);
            const outputElement = document.getElementById(`${tool}Output`);
            
            if (!inputElement || !outputElement) {
                this.showError('Tool elements not found');
                return;
            }
            
            const userInput = inputElement.value.trim();
            if (!userInput) {
                this.showError('Please enter your question or email');
                return;
            }
            
            // Check if user has access
            if (!this.hasAccess()) {
                this.showError('Your trial has expired. Please upgrade to continue.');
                this.showPage('subscriptionPage');
                return;
            }
            
            // Ensure we have a server session for API calls
            const hasServerSession = await this.ensureServerSession();
            
            // Get system API key from database or permanent storage
            let apiConfig = null;
            
            // Try database first if we have a valid server session
            if (hasServerSession && this.serverRunning) {
                try {
                    apiConfig = await this.dbClient.getApiConfig();
                    if (apiConfig) {
                        console.log('📊 API config loaded from database');
                    }
                } catch (error) {
                    console.warn('Database API config failed:', error);
                }
            }
            
            // Fallback to permanent storage
            if (!apiConfig) {
                apiConfig = this.storage.get('apiConfig') || {};
                console.log('📊 API config loaded from permanent storage');
            }
            
            console.log('🔍 API Config check:', {
                source: apiConfig && hasServerSession ? 'database' : 'storage',
                hasApiConfig: !!apiConfig,
                hasOpenaiKey: !!apiConfig?.openaiKey,
                keyLength: apiConfig?.openaiKey ? apiConfig.openaiKey.length : 0,
                keyPrefix: apiConfig?.openaiKey ? apiConfig.openaiKey.substring(0, 8) + '...' : 'none'
            });
            
            if (!apiConfig?.openaiKey) {
                console.error('❌ No API key found in database or storage');
                this.showError('System API key not configured. Please contact administrator.');
                return;
            }
            
            // Prepare the prompt based on tool
            const systemPrompt = tool === 'federal' 
                ? 'You are an expert HR assistant specializing in Federal FMLA compliance. Provide clear, professional responses to employee leave questions based on Federal FMLA regulations.'
                : 'You are an expert HR assistant specializing in California leave laws including CFRA, PDL, and FMLA interactions. Provide clear, professional responses for California employees.';
            
            const messages = [
                { role: 'system', content: systemPrompt },
                { role: 'user', content: userInput }
            ];
            
            // Make API call
            const response = await this.callOpenAI(apiConfig.openaiKey, messages, tool);
            
            if (response.success) {
                outputElement.innerHTML = `<p>${response.content}</p>`;
                this.showSuccess('Response generated successfully');
                
                // Update API usage stats
                apiConfig.totalRequests = (apiConfig.totalRequests || 0) + 1;
                apiConfig.openaiRequests = (apiConfig.openaiRequests || 0) + 1;
                
                // Save updated stats to storage
                this.storage.set('apiConfig', apiConfig);
            } else {
                this.showError(response.error || 'Failed to generate response');
            }
            
        } catch (error) {
            console.error('AI submit error:', error);
            this.logError(error, 'AI Submit', { tool, user: this.currentUser?.email });
            this.showError('Failed to generate response. Please try again.');
        } finally {
            this.hideLoading();
        }
    }

    async callOpenAI(apiKey, messages, toolName = 'unknown') {
        try {
            // Use server endpoint to avoid CORS issues if we have a valid server session
            const apiUrl = this.getApiUrl('openai');
            
            if (apiUrl && this.serverRunning && this.sessionToken && !this.sessionToken.startsWith('client_')) {
                console.log('📡 Using server endpoint for OpenAI API call');
                
                // Call through server
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.sessionToken}`
                    },
                    body: JSON.stringify({
                        apiKey: apiKey,
                        messages: messages,
                        model: 'gpt-4o-mini',
                        max_tokens: 1000,
                        temperature: 0.3,
                        toolName: toolName
                    })
                });
                
                const data = await response.json();
                
                if (response.ok && data.choices && data.choices[0]) {
                    return {
                        success: true,
                        content: data.choices[0].message.content
                    };
                } else {
                    console.warn('Server OpenAI call failed:', data);
                    return {
                        success: false,
                        error: data.error?.message || data.message || 'OpenAI API error'
                    };
                }
            } else {
                // Client-side mode or server not available
                console.warn('⚠️ Server not available or client-side session - API calls not supported in browser');
                return {
                    success: false,
                    error: 'AI responses require a server connection. Please ensure the Node.js server is running on port 3001, or contact your administrator to configure the system API key.'
                };
            }
        } catch (error) {
            console.error('OpenAI API call error:', error);
            return {
                success: false,
                error: error.message || 'Network error'
            };
        }
    }

    hasAccess() {
        if (!this.currentUser) return false;
        if (this.currentUser.isAdmin) return true;
        
        const now = Date.now();
        
        // Check subscription first
        if (this.currentUser.subscriptionExpiry && this.currentUser.subscriptionExpiry > now) {
            return true;
        }
        
        // Check trial - with fallback calculation for older users
        let trialExpiry = this.currentUser.trialExpiry;
        
        // Fallback: If no trialExpiry is set, calculate from createdAt + 24 hours
        if (!trialExpiry && this.currentUser.createdAt) {
            trialExpiry = this.currentUser.createdAt + (24 * 60 * 60 * 1000);
            console.log('⚠️ No trialExpiry found, using fallback calculation:', new Date(trialExpiry).toLocaleString());
        }
        
        if (trialExpiry && trialExpiry > now) {
            console.log('✅ User has active trial until:', new Date(trialExpiry).toLocaleString());
            return true;
        }
        
        console.log('❌ User access expired. Trial expiry:', trialExpiry ? new Date(trialExpiry).toLocaleString() : 'Not set');
        return false;
    }

    handleSettingsUpdate() {
        console.log('⚙️ Updating settings');
        
        const newPassword = document.getElementById('newPassword').value;
        
        if (newPassword && newPassword.length < 8) {
            this.showError('Password must be at least 8 characters long');
            return;
        }
        
        if (newPassword) {
            // Update password
            this.currentUser.password = newPassword;
            const users = this.loadUsers();
            const userIndex = users.findIndex(u => u.id === this.currentUser.id);
            if (userIndex !== -1) {
                users[userIndex] = this.currentUser;
                this.saveUsers(users);
                localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
            }
        }
        
        this.hideSettings();
        this.showSuccess('Settings updated successfully');
        
        // Clear form
        document.getElementById('settingsForm').reset();
    }

    handleSubscriptionClick(plan, amount) {
        console.log('💳 Handling subscription click:', plan, amount);
        
        // Check if user is logged in
        if (!this.currentUser) {
            // Store selected plan and redirect to registration
            localStorage.setItem('selectedPlan', JSON.stringify({ plan, amount }));
            this.showPage('registerPage');
            this.showSuccess('Please create an account to continue with your subscription');
            return;
        }
        
        // User is logged in, show payment options
        this.showPaymentModal(plan, amount);
    }

    showPaymentModal(plan, amount) {
        console.log('💳 Showing payment modal for:', plan, amount);
        
        // Update modal content
        const modalTitle = document.getElementById('paymentModalTitle');
        const planName = document.getElementById('selectedPlanName');
        const planAmount = document.getElementById('selectedPlanAmount');
        const planPeriod = document.getElementById('selectedPlanPeriod');
        
        if (modalTitle) modalTitle.textContent = 'Choose Payment Method';
        if (planName) planName.textContent = this.getPlanDisplayName(plan);
        if (planAmount) planAmount.textContent = amount;
        if (planPeriod) planPeriod.textContent = this.getPlanPeriod(plan);
        
        // Store current selection
        this.selectedPlan = { plan, amount };
        
        // Show modal
        const modal = document.getElementById('paymentModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
        
        // Bind payment method buttons
        this.bindPaymentButtons();
    }

    getPlanDisplayName(plan) {
        const names = {
            'trial': 'Free Trial',
            'monthly': 'Monthly Plan',
            'annual': 'Annual Plan',
            'organization': 'Organization Plan'
        };
        return names[plan] || 'Subscription Plan';
    }

    getPlanPeriod(plan) {
        const periods = {
            'trial': '',
            'monthly': '/month',
            'annual': '/year',
            'organization': '/year'
        };
        return periods[plan] || '';
    }

    bindPaymentButtons() {
        console.log('🔗 Binding payment buttons');
        
        // Remove existing event listeners to prevent duplicates
        const stripeBtn = document.getElementById('stripePaymentBtn');
        const paypalBtn = document.getElementById('paypalPaymentBtn');
        
        if (stripeBtn) {
            // Clone node to remove existing event listeners
            const newStripeBtn = stripeBtn.cloneNode(true);
            stripeBtn.parentNode.replaceChild(newStripeBtn, stripeBtn);
            
            newStripeBtn.addEventListener('click', () => {
                console.log('💳 Stripe payment clicked');
                this.processStripePayment();
            });
        }
        
        if (paypalBtn) {
            // Clone node to remove existing event listeners
            const newPaypalBtn = paypalBtn.cloneNode(true);
            paypalBtn.parentNode.replaceChild(newPaypalBtn, paypalBtn);
            
            newPaypalBtn.addEventListener('click', () => {
                console.log('💳 PayPal payment clicked');
                this.processPayPalPayment();
            });
        }
    }

    processStripePayment() {
        console.log('💳 Processing Stripe payment');
        
        if (!this.selectedPlan) {
            this.showError('No plan selected');
            return;
        }
        
        // Hide payment modal
        document.getElementById('paymentModal')?.classList.add('hidden');
        
        // Show processing message
        this.showLoading();
        this.showSuccess('Processing card payment...');
        
        // Simulate Stripe payment processing
        setTimeout(() => {
            this.hideLoading();
            // For demo purposes, simulate successful payment
            // In production, this would integrate with Stripe API
            this.simulatePaymentSuccess();
        }, 2000);
    }

    processPayPalPayment() {
        console.log('💳 Processing PayPal payment');
        
        if (!this.selectedPlan) {
            this.showError('No plan selected');
            return;
        }
        
        // Create PayPal URL with the exact format specified
        const paypalUrl = `https://www.paypal.com/cgi-bin/webscr?cmd=_xclick&business=talk2char@gmail.com&amount=${this.selectedPlan.amount}&currency_code=USD`;
        
        console.log('🔗 Opening PayPal URL:', paypalUrl);
        
        // Open PayPal in new window
        const paypalWindow = window.open(paypalUrl, '_blank');
        
        if (!paypalWindow) {
            this.showError('Please allow popups to complete PayPal payment');
            return;
        }
        
        // Hide payment modal
        document.getElementById('paymentModal')?.classList.add('hidden');
        
        // Show processing message
        this.showSuccess('Redirected to PayPal. Complete your payment and return to this page.');
        
        // For demo purposes, simulate successful payment after a delay
        // In production, this would be handled by PayPal IPN or return URL
        setTimeout(() => {
            if (confirm('Did you complete the PayPal payment successfully?')) {
                this.simulatePaymentSuccess();
            } else {
                this.showError('Payment was not completed. Please try again.');
                this.showPaymentModal(this.selectedPlan.plan, this.selectedPlan.amount);
            }
        }, 5000);
    }

    simulatePaymentSuccess() {
        console.log('✅ Simulating payment success');
        
        if (!this.selectedPlan || !this.currentUser) return;
        
        // Update user subscription
        const durationMs = this.selectedPlan.plan === 'monthly' 
            ? 30 * 24 * 60 * 60 * 1000  // 30 days
            : 365 * 24 * 60 * 60 * 1000; // 365 days
        
        this.currentUser.subscriptionExpiry = Date.now() + durationMs;
        this.currentUser.accessLevel = 'subscribed';
        this.currentUser.subscriptionPlan = this.selectedPlan.plan;
        this.currentUser.subscriptionAmount = this.selectedPlan.amount;
        
        // Save updated user
        const users = this.loadUsers();
        const userIndex = users.findIndex(u => u.id === this.currentUser.id);
        if (userIndex !== -1) {
            users[userIndex] = this.currentUser;
            this.saveUsers(users);
            localStorage.setItem('currentUser', JSON.stringify(this.currentUser));
        }
        
        // Hide payment modal
        document.getElementById('paymentModal')?.classList.add('hidden');
        
        // Show success and redirect
        this.showSuccess('Payment successful! Your subscription is now active.');
        this.showPage('dashboard');
        
        // Update trial timer if visible
        this.updateTrialTimer();
    }

    updateTrialTimer() {
        console.log('⏰ Updating trial timer');
        
        if (!this.currentUser) return;
        
        const timerElement = document.getElementById('trialTimer');
        const upgradeBtn = document.getElementById('upgradeBtn');
        
        if (!timerElement) return;
        
        const now = Date.now();
        
        // Check if user has active subscription first
        if (this.currentUser.subscriptionExpiry && this.currentUser.subscriptionExpiry > now) {
            const expiryDate = new Date(this.currentUser.subscriptionExpiry).toLocaleDateString();
            timerElement.textContent = `Subscribed (expires ${expiryDate})`;
            timerElement.classList.remove('hidden');
            if (upgradeBtn) upgradeBtn.classList.add('hidden');
            console.log('✅ User has active subscription until:', expiryDate);
            return;
        }
        
        // Check trial status
        const trialEnd = this.currentUser.trialExpiry || (this.currentUser.createdAt + (24 * 60 * 60 * 1000));
        
        if (trialEnd > now) {
            const timeLeft = trialEnd - now;
            const hoursLeft = Math.floor(timeLeft / (1000 * 60 * 60));
            const minutesLeft = Math.floor((timeLeft % (1000 * 60 * 60)) / (1000 * 60));
            const secondsLeft = Math.floor((timeLeft % (1000 * 60)) / 1000);
            
            timerElement.textContent = `Trial: ${hoursLeft}h ${minutesLeft}m ${secondsLeft}s remaining`;
            timerElement.classList.remove('hidden');
            if (upgradeBtn) upgradeBtn.classList.remove('hidden');
            console.log('⏰ Trial time remaining:', hoursLeft, 'hours', minutesLeft, 'minutes');
        } else {
            timerElement.textContent = 'Trial Expired';
            timerElement.classList.remove('hidden');
            if (upgradeBtn) upgradeBtn.classList.remove('hidden');
            console.log('❌ Trial has expired');
        }
    }

    startTrialTimer() {
        console.log('⏰ Starting trial timer');
        
        // Update immediately
        this.updateTrialTimer();
        
        // Update every second
        if (this.trialTimerInterval) {
            clearInterval(this.trialTimerInterval);
        }
        
        this.trialTimerInterval = setInterval(() => {
            this.updateTrialTimer();
        }, 1000); // Update every second
    }

    showUpgradePlans() {
        console.log('📋 Showing upgrade plans modal');
        
        const modal = document.createElement('div');
        modal.className = 'modal upgrade-modal';
        modal.innerHTML = `
            <div class="modal-content upgrade-content">
                <div class="modal-header">
                    <h3>Choose Your Plan</h3>
                    <button class="btn-close" onclick="this.closest('.modal').remove()">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="upgrade-plans-grid">
                        <div class="upgrade-plan-card" data-plan="monthly" data-amount="29">
                            <div class="plan-header">
                                <h4>Monthly</h4>
                                <div class="plan-price">
                                    <span class="currency">$</span>
                                    <span class="amount">29</span>
                                    <span class="period">/month</span>
                                </div>
                            </div>
                            <div class="plan-features">
                                <ul>
                                    <li>✓ Full individual access to HR Leave Assist</li>
                                    <li>✓ Guidance aligned to Federal and California leave laws</li>
                                    <li>✓ Unlimited questions, including follow-ups</li>
                                    <li>✓ Cancel anytime</li>
                                </ul>
                            </div>
                            <button class="btn btn-primary btn-block select-plan-btn" data-plan="monthly" data-amount="29">
                                Select Monthly
                            </button>
                        </div>
                        
                        <div class="upgrade-plan-card featured" data-plan="annual" data-amount="290">
                            <div class="plan-badge">Most Popular</div>
                            <div class="plan-header">
                                <h4>Annual</h4>
                                <div class="plan-price">
                                    <span class="currency">$</span>
                                    <span class="amount">290</span>
                                    <span class="period">/year</span>
                                </div>
                            </div>
                            <div class="plan-features">
                                <ul>
                                    <li>✓ Everything included in Monthly</li>
                                    <li>✓ 12 months of continuous individual access</li>
                                    <li>✓ Unlimited questions throughout the year</li>
                                    <li>✓ Predictable annual billing</li>
                                </ul>
                            </div>
                            <button class="btn btn-primary btn-block select-plan-btn" data-plan="annual" data-amount="290">
                                Select Annual
                            </button>
                        </div>
                        
                        <div class="upgrade-plan-card" data-plan="organization" data-amount="580">
                            <div class="plan-header">
                                <h4>Organization</h4>
                                <div class="plan-price">
                                    <span class="currency">$</span>
                                    <span class="amount">580</span>
                                    <span class="period">/year</span>
                                </div>
                            </div>
                            <div class="plan-features">
                                <ul>
                                    <li>✓ Everything included in Monthly</li>
                                    <li>✓ Up to 5 named HR users under one organization</li>
                                    <li>✓ 12 months of continuous access for each licensed user</li>
                                    <li>✓ Centralized annual billing</li>
                                </ul>
                            </div>
                            <button class="btn btn-primary btn-block select-plan-btn" data-plan="organization" data-amount="580">
                                Select Organization
                            </button>
                        </div>
                    </div>
                    <div class="upgrade-actions">
                        <button class="btn btn-secondary" onclick="this.closest('.modal').remove()">
                            Back to Dashboard
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Bind plan selection buttons
        modal.querySelectorAll('.select-plan-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const plan = e.target.getAttribute('data-plan');
                const amount = e.target.getAttribute('data-amount');
                modal.remove();
                this.showPaymentModal(plan, amount);
            });
        });
    }

    initializeAdminDashboard() {
        console.log('🔧 Initializing admin dashboard...');
        
        // Load initial statistics
        this.loadAdminStats();
        
        // Initialize with users tab active
        this.switchAdminTab('users');
        
        console.log('✅ Admin dashboard initialized');
    }

    initializeDashboard() {
        console.log('🔧 Initializing user dashboard...');
        
        if (!this.currentUser) return;
        
        // Update welcome name
        const welcomeNameEl = document.getElementById('userWelcomeName');
        if (welcomeNameEl) {
            welcomeNameEl.textContent = this.currentUser.firstName;
        }
        
        // Start trial timer
        this.startTrialTimer();
        
        console.log('✅ User dashboard initialized');
    }

    async loadAdminStats() {
        console.log('📊 Loading admin statistics...');
        
        let users = [];
        let stats = null;
        
        // Try to load stats from server first if available
        if (this.serverRunning && this.sessionToken) {
            try {
                const response = await fetch(this.getApiUrl('admin/stats'), {
                    headers: {
                        'Authorization': `Bearer ${this.sessionToken}`
                    }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    if (data.success && data.stats) {
                        stats = data.stats;
                        console.log('📊 Stats loaded from server:', stats);
                    }
                }
            } catch (error) {
                console.warn('Failed to load stats from server:', error);
            }
        }
        
        // If server stats failed, calculate from local storage
        if (!stats) {
            users = this.loadUsers();
            console.log('📊 Calculating stats from localStorage:', users.length, 'users');
            
            const now = Date.now();
            const nonAdmins = users.filter(u => !u.isAdmin);
            
            // Calculate subscribed users (those with active subscriptions)
            const subscribedUsers = nonAdmins.filter(u => {
                return u.subscriptionExpiry && u.subscriptionExpiry > now;
            }).length;
            
            // Calculate trial users (those currently in trial period without subscription)
            const trialUsers = nonAdmins.filter(u => {
                const hasActiveSubscription = u.subscriptionExpiry && u.subscriptionExpiry > now;
                if (hasActiveSubscription) return false; // Already subscribed
                
                const trialEnd = u.trialExpiry || (u.createdAt + (24 * 60 * 60 * 1000));
                return trialEnd > now;
            }).length;
            
            stats = {
                totalUsers: users.filter(u => u.emailVerified).length,
                verifiedUsers: users.filter(u => u.emailVerified).length,
                activeSubscriptions: subscribedUsers,
                trialUsers: trialUsers
            };
        }
        
        // Update stat cards
        const totalUsersEl = document.getElementById('totalUsers');
        const verifiedUsersEl = document.getElementById('verifiedUsers');
        const subscribedUsersEl = document.getElementById('subscribedUsers');
        const trialUsersEl = document.getElementById('trialUsers');
        
        if (totalUsersEl) totalUsersEl.textContent = stats.totalUsers || 0;
        if (verifiedUsersEl) verifiedUsersEl.textContent = stats.verifiedUsers || 0;
        if (subscribedUsersEl) subscribedUsersEl.textContent = stats.activeSubscriptions || 0;
        if (trialUsersEl) trialUsersEl.textContent = stats.trialUsers || 0;
        
        console.log('📊 Admin Stats Updated:', stats);
    }

    async generateAccessCode() {
        console.log('🔑 Generating new access code...');
        
        const length = document.getElementById('codeLength')?.value || '8';
        const duration = document.getElementById('accessDuration')?.value || '30';
        const durationType = document.getElementById('durationType')?.value || 'days';
        const description = document.getElementById('codeDescription')?.value || '';
        
        // Generate random code
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        let code = '';
        for (let i = 0; i < parseInt(length); i++) {
            code += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        
        // Create access code object
        const accessCode = {
            id: 'code_' + Date.now(),
            code: code,
            duration: parseInt(duration),
            durationType: durationType,
            description: description,
            uses: 0,
            maxUses: null, // No limit by default
            createdAt: Date.now()
        };
        
        let success = false;
        
        // Try database first if server is running
        if (this.serverRunning && this.sessionToken) {
            try {
                const response = await fetch(this.getApiUrl('db/access-codes'), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${this.sessionToken}`
                    },
                    body: JSON.stringify(accessCode)
                });
                
                if (response.ok) {
                    success = true;
                    console.log('✅ Access code saved to database');
                }
            } catch (error) {
                console.warn('Database save failed, using storage:', error);
            }
        }
        
        // Fallback to permanent storage
        if (!success) {
            const accessCodes = this.storage.get('accessCodes') || [];
            accessCodes.push(accessCode);
            success = this.storage.set('accessCodes', accessCodes);
            console.log('✅ Access code saved to permanent storage');
        }
        
        if (success) {
            // Refresh the tab
            this.loadAccessCodesTab();
            
            // Clear form
            document.getElementById('generateAccessCodeForm')?.reset();
            
            this.showSuccess(`Access code generated: ${code}`);
            console.log('✅ Access code generated:', accessCode);
        } else {
            this.showError('Failed to generate access code');
        }
    }

    async deleteAccessCode(codeId) {
        console.log('🗑️ Deleting access code:', codeId);
        
        let success = false;
        
        // Try database first if server is running
        if (this.serverRunning && this.sessionToken) {
            try {
                const response = await fetch(this.getApiUrl(`db/access-codes/${codeId}`), {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${this.sessionToken}`
                    }
                });
                
                if (response.ok) {
                    success = true;
                    console.log('✅ Access code deleted from database');
                }
            } catch (error) {
                console.warn('Database delete failed, using storage:', error);
            }
        }
        
        // Fallback to permanent storage
        if (!success) {
            const accessCodes = this.storage.get('accessCodes') || [];
            const filteredCodes = accessCodes.filter(code => code.id !== codeId);
            success = this.storage.set('accessCodes', filteredCodes);
            console.log('✅ Access code deleted from permanent storage');
        }
        
        if (success) {
            // Refresh the tab
            this.loadAccessCodesTab();
            this.showSuccess('Access code deleted');
        } else {
            this.showError('Failed to delete access code');
        }
    }

    async saveApiSettings() {
        console.log('💾 Saving API settings...');
        
        const apiKey = document.getElementById('systemOpenaiKey')?.value;
        
        if (!apiKey) {
            this.showError('Please enter an API key');
            return;
        }
        
        let success = false;
        
        // Try database first if server is running
        if (this.serverRunning && this.sessionToken) {
            success = await this.dbClient.updateApiConfig(apiKey);
            if (success) {
                console.log('✅ API key saved to database');
            }
        }
        
        // Fallback to permanent storage
        if (!success) {
            const config = this.storage.get('apiConfig') || {};
            config.openaiKey = apiKey;
            config.updatedAt = Date.now();
            success = this.storage.set('apiConfig', config);
            console.log('✅ API key saved to permanent storage');
        }
        
        if (success) {
            // Refresh the tab
            this.loadApiSettingsTab();
            this.showSuccess('API settings saved successfully');
        } else {
            this.showError('Failed to save API settings');
        }
    }

    async testApiKey() {
        console.log('🧪 Testing API key...');
        
        let config = null;
        
        // Try database first if server is running
        if (this.serverRunning && this.sessionToken) {
            config = await this.dbClient.getApiConfig();
            if (config) {
                console.log('📊 API config loaded from database for testing');
            }
        }
        
        // Fallback to permanent storage
        if (!config) {
            config = this.storage.get('apiConfig') || {};
            console.log('📊 API config loaded from permanent storage for testing');
        }
        
        if (!config.openaiKey) {
            this.showError('No API key configured');
            return;
        }
        
        // Simple test - just check if key format looks valid
        if (config.openaiKey.startsWith('sk-') && config.openaiKey.length > 20) {
            this.showSuccess('API key format appears valid');
            
            // Update usage stats (mock)
            config.totalRequests = (config.totalRequests || 0) + 1;
            config.openaiRequests = (config.openaiRequests || 0) + 1;
            
            // Save updated stats
            if (this.serverRunning && this.sessionToken) {
                await this.dbClient.updateApiConfig(config.openaiKey);
            } else {
                this.storage.set('apiConfig', config);
            }
            
            // Refresh stats
            this.loadApiSettingsTab();
        } else {
            this.showError('API key format appears invalid');
        }
    }
}

// Global error handlers to prevent refresh loops
window.addEventListener('error', (event) => {
    console.error('❌ Global JavaScript error caught:', event.error);
    console.error('❌ Error details:', {
        message: event.message,
        filename: event.filename,
        lineno: event.lineno,
        colno: event.colno,
        stack: event.error?.stack
    });
    
    // Log error if app is available
    if (window.app && typeof window.app.logError === 'function') {
        window.app.logError(event.error || new Error(event.message), 'Global Error Handler', {
            filename: event.filename,
            lineno: event.lineno,
            colno: event.colno
        });
    }
    
    // Prevent default error handling that might cause refresh
    event.preventDefault();
    return false;
});

window.addEventListener('unhandledrejection', (event) => {
    console.error('❌ Unhandled promise rejection:', event.reason);
    
    // Log error if app is available
    if (window.app && typeof window.app.logError === 'function') {
        const error = event.reason instanceof Error ? event.reason : new Error(event.reason);
        window.app.logError(error, 'Unhandled Promise Rejection', {
            reason: event.reason
        });
    }
    
    // Prevent default error handling
    event.preventDefault();
});

// Start
let app;
document.addEventListener('DOMContentLoaded', () => { 
    try {
        console.log('🚀 DOM loaded, starting app...');
        
        // Check if required elements exist
        const requiredElements = ['landingPage', 'loginPage', 'registerPage'];
        const missingElements = requiredElements.filter(id => !document.getElementById(id));
        
        if (missingElements.length > 0) {
            console.error('❌ Missing required page elements:', missingElements);
        }
        
        // Test if basic DOM elements exist
        console.log('🔍 Testing DOM elements...');
        console.log('loginBtn exists:', !!document.getElementById('loginBtn'));
        console.log('getStartedBtn exists:', !!document.getElementById('getStartedBtn'));
        
        app = new LeaveAssistantApp(); 
        
        // Make app globally accessible for debugging
        window.app = app;
        
        console.log('✅ App initialized successfully');
        
    } catch (error) {
        console.error('❌ Failed to start app:', error);
        console.error('❌ Error stack:', error.stack);
        
        // Try to log error even if app failed to initialize
        try {
            if (window.app && typeof window.app.logError === 'function') {
                window.app.logError(error, 'App Initialization Failure');
            } else {
                // Fallback error logging to localStorage
                const errorData = {
                    timestamp: new Date().toISOString(),
                    context: 'App Initialization Failure',
                    message: error.message || error.toString(),
                    stack: error.stack || 'No stack trace available',
                    url: window.location.href,
                    userAgent: navigator.userAgent
                };
                
                const existingErrors = JSON.parse(localStorage.getItem('errorLog') || '[]');
                existingErrors.push(errorData);
                localStorage.setItem('errorLog', JSON.stringify(existingErrors));
            }
        } catch (loggingError) {
            console.error('❌ Failed to log initialization error:', loggingError);
        }
        
        // Show error message to user without causing refresh
        const errorDiv = document.createElement('div');
        errorDiv.style.cssText = 'display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column; font-family: Arial, sans-serif; background: white; position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 10000;';
        errorDiv.innerHTML = `
            <h2 style="color: #ef4444;">Application Error</h2>
            <p>Failed to initialize the Leave Assistant application.</p>
            <p style="color: #666; font-size: 14px;">Please refresh the page or contact support if the problem persists.</p>
            <button onclick="window.location.reload()" style="margin-top: 20px; padding: 10px 20px; background: #0023F5; color: white; border: none; border-radius: 5px; cursor: pointer;">Refresh Page</button>
        `;
        document.body.appendChild(errorDiv);
    }
});