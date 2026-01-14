// ==========================================
// DATABASE MANAGER - SQLite Implementation
// ==========================================

const sqlite3 = require('sqlite3').verbose();
const path = require('path');
const fs = require('fs');

class DatabaseManager {
    constructor() {
        this.dbPath = path.join(__dirname, 'data', 'hrla_database.db');
        this.db = null;
        this.initializeDatabase();
    }

    initializeDatabase() {
        console.log('🗄️ Initializing SQLite database...');
        
        // Ensure data directory exists
        const dataDir = path.dirname(this.dbPath);
        if (!fs.existsSync(dataDir)) {
            fs.mkdirSync(dataDir, { recursive: true });
        }

        // Connect to database
        this.db = new sqlite3.Database(this.dbPath, (err) => {
            if (err) {
                console.error('❌ Database connection error:', err);
            } else {
                console.log('✅ Connected to SQLite database');
                this.createTables();
            }
        });
    }

    createTables() {
        console.log('📋 Creating database tables...');

        const createUsersTable = () => {
            return new Promise((resolve, reject) => {
                this.db.run(`
                    CREATE TABLE IF NOT EXISTS users (
                        id TEXT PRIMARY KEY,
                        firstName TEXT NOT NULL,
                        lastName TEXT NOT NULL,
                        email TEXT UNIQUE NOT NULL,
                        password TEXT NOT NULL,
                        isAdmin BOOLEAN DEFAULT 0,
                        emailVerified BOOLEAN DEFAULT 0,
                        createdAt INTEGER NOT NULL,
                        subscriptionExpiry INTEGER,
                        trialStarted INTEGER,
                        trialExpiry INTEGER,
                        accessLevel TEXT DEFAULT 'trial',
                        lastLogin INTEGER,
                        updatedAt INTEGER DEFAULT (strftime('%s', 'now') * 1000)
                    )
                `, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        };

        const createApiConfigTable = () => {
            return new Promise((resolve, reject) => {
                this.db.run(`
                    CREATE TABLE IF NOT EXISTS api_config (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        openaiKey TEXT,
                        totalRequests INTEGER DEFAULT 0,
                        openaiRequests INTEGER DEFAULT 0,
                        updatedAt INTEGER DEFAULT (strftime('%s', 'now') * 1000),
                        createdBy TEXT,
                        isActive BOOLEAN DEFAULT 1
                    )
                `, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        };

        const createAccessCodesTable = () => {
            return new Promise((resolve, reject) => {
                this.db.run(`
                    CREATE TABLE IF NOT EXISTS access_codes (
                        id TEXT PRIMARY KEY,
                        code TEXT UNIQUE NOT NULL,
                        duration INTEGER NOT NULL,
                        durationType TEXT NOT NULL,
                        uses INTEGER DEFAULT 0,
                        maxUses INTEGER,
                        createdAt INTEGER NOT NULL,
                        createdBy TEXT,
                        isActive BOOLEAN DEFAULT 1
                    )
                `, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        };

        const createSessionsTable = () => {
            return new Promise((resolve, reject) => {
                this.db.run(`
                    CREATE TABLE IF NOT EXISTS sessions (
                        id TEXT PRIMARY KEY,
                        userId TEXT NOT NULL,
                        token TEXT UNIQUE NOT NULL,
                        createdAt INTEGER NOT NULL,
                        expiresAt INTEGER NOT NULL,
                        lastActivity INTEGER,
                        ipAddress TEXT,
                        userAgent TEXT,
                        FOREIGN KEY (userId) REFERENCES users (id)
                    )
                `, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        };

        const createApiUsageTable = () => {
            return new Promise((resolve, reject) => {
                this.db.run(`
                    CREATE TABLE IF NOT EXISTS api_usage (
                        id INTEGER PRIMARY KEY AUTOINCREMENT,
                        userId TEXT NOT NULL,
                        endpoint TEXT NOT NULL,
                        requestData TEXT,
                        responseData TEXT,
                        tokensUsed INTEGER,
                        cost REAL,
                        timestamp INTEGER DEFAULT (strftime('%s', 'now') * 1000),
                        FOREIGN KEY (userId) REFERENCES users (id)
                    )
                `, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        };

        const createSystemSettingsTable = () => {
            return new Promise((resolve, reject) => {
                this.db.run(`
                    CREATE TABLE IF NOT EXISTS system_settings (
                        key TEXT PRIMARY KEY,
                        value TEXT NOT NULL,
                        type TEXT DEFAULT 'string',
                        updatedAt INTEGER DEFAULT (strftime('%s', 'now') * 1000),
                        updatedBy TEXT
                    )
                `, (err) => {
                    if (err) reject(err);
                    else resolve();
                });
            });
        };

        // Create all tables sequentially
        createUsersTable()
            .then(() => createApiConfigTable())
            .then(() => createAccessCodesTable())
            .then(() => createSessionsTable())
            .then(() => createApiUsageTable())
            .then(() => createSystemSettingsTable())
            .then(() => {
                console.log('✅ All database tables created successfully');
                this.insertDefaultData();
            })
            .catch((err) => {
                console.error('❌ Error creating database tables:', err);
            });
    }

    insertDefaultData() {
        console.log('👤 Inserting default data...');

        // Check if admin user exists
        this.db.get('SELECT id FROM users WHERE email = ?', ['talk2char@gmail.com'], (err, row) => {
            if (err) {
                console.error('❌ Error checking admin user:', err);
                return;
            }

            if (!row) {
                // Insert default admin user
                const adminUser = {
                    id: 'admin-001',
                    firstName: 'Admin',
                    lastName: 'User',
                    email: 'talk2char@gmail.com',
                    password: 'Password@123',
                    isAdmin: 1,
                    emailVerified: 1,
                    createdAt: Date.now(),
                    accessLevel: 'admin'
                };

                this.db.run(`
                    INSERT INTO users (id, firstName, lastName, email, password, isAdmin, emailVerified, createdAt, accessLevel)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                `, [
                    adminUser.id, adminUser.firstName, adminUser.lastName, adminUser.email,
                    adminUser.password, adminUser.isAdmin, adminUser.emailVerified,
                    adminUser.createdAt, adminUser.accessLevel
                ], (err) => {
                    if (err) {
                        console.error('❌ Error inserting admin user:', err);
                    } else {
                        console.log('✅ Default admin user created');
                    }
                });
            }
        });

        // Insert default API configuration if not exists
        this.db.get('SELECT id FROM api_config WHERE isActive = 1', (err, row) => {
            if (err) {
                console.error('❌ Error checking API config:', err);
                return;
            }

            if (!row) {
                const defaultApiKey = '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';
                
                this.db.run(`
                    INSERT INTO api_config (openaiKey, createdBy)
                    VALUES (?, ?)
                `, [defaultApiKey, 'admin-001'], (err) => {
                    if (err) {
                        console.error('❌ Error inserting API config:', err);
                    } else {
                        console.log('✅ Default API configuration created');
                    }
                });
            }
        });
    }

    // User operations
    async createUser(userData) {
        return new Promise((resolve, reject) => {
            const sql = `
                INSERT INTO users (id, firstName, lastName, email, password, isAdmin, emailVerified, createdAt, trialStarted, trialExpiry, accessLevel)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            `;
            
            this.db.run(sql, [
                userData.id, userData.firstName, userData.lastName, userData.email,
                userData.password, userData.isAdmin || 0, userData.emailVerified || 0,
                userData.createdAt, userData.trialStarted, userData.trialExpiry, userData.accessLevel
            ], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ id: userData.id, changes: this.changes });
                }
            });
        });
    }

    async getUserByEmail(email) {
        return new Promise((resolve, reject) => {
            this.db.get('SELECT * FROM users WHERE email = ?', [email], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    async getUserById(id) {
        return new Promise((resolve, reject) => {
            this.db.get('SELECT * FROM users WHERE id = ?', [id], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    async getAllUsers() {
        return new Promise((resolve, reject) => {
            this.db.all('SELECT * FROM users ORDER BY createdAt DESC', (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    async updateUser(id, userData) {
        return new Promise((resolve, reject) => {
            const fields = Object.keys(userData).map(key => `${key} = ?`).join(', ');
            const values = Object.values(userData);
            values.push(Date.now()); // updatedAt
            values.push(id); // WHERE condition

            const sql = `UPDATE users SET ${fields}, updatedAt = ? WHERE id = ?`;
            
            this.db.run(sql, values, function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    async deleteUser(id) {
        return new Promise((resolve, reject) => {
            this.db.run('DELETE FROM users WHERE id = ? AND isAdmin = 0', [id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    // API Configuration operations
    async getApiConfig() {
        return new Promise((resolve, reject) => {
            this.db.get('SELECT * FROM api_config WHERE isActive = 1 ORDER BY updatedAt DESC LIMIT 1', (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    async updateApiConfig(apiKey, updatedBy) {
        return new Promise((resolve, reject) => {
            // Deactivate old configs
            this.db.run('UPDATE api_config SET isActive = 0', (err) => {
                if (err) {
                    reject(err);
                    return;
                }

                // Insert new config
                this.db.run(`
                    INSERT INTO api_config (openaiKey, createdBy, updatedAt)
                    VALUES (?, ?, ?)
                `, [apiKey, updatedBy, Date.now()], function(err) {
                    if (err) {
                        reject(err);
                    } else {
                        resolve({ id: this.lastID });
                    }
                });
            });
        });
    }

    // Access Codes operations
    async createAccessCode(codeData) {
        return new Promise((resolve, reject) => {
            const sql = `
                INSERT INTO access_codes (id, code, duration, durationType, maxUses, createdAt, createdBy)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            `;
            
            this.db.run(sql, [
                codeData.id, codeData.code, codeData.duration, codeData.durationType,
                codeData.maxUses, codeData.createdAt, codeData.createdBy
            ], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ id: codeData.id });
                }
            });
        });
    }

    async getAllAccessCodes() {
        return new Promise((resolve, reject) => {
            this.db.all('SELECT * FROM access_codes WHERE isActive = 1 ORDER BY createdAt DESC', (err, rows) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(rows);
                }
            });
        });
    }

    async getAccessCodeByCode(code) {
        return new Promise((resolve, reject) => {
            this.db.get('SELECT * FROM access_codes WHERE code = ? AND isActive = 1', [code], (err, row) => {
                if (err) {
                    reject(err);
                } else {
                    resolve(row);
                }
            });
        });
    }

    async updateAccessCodeUsage(id, uses) {
        return new Promise((resolve, reject) => {
            this.db.run('UPDATE access_codes SET uses = ? WHERE id = ?', [uses, id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    async deleteAccessCode(id) {
        return new Promise((resolve, reject) => {
            this.db.run('UPDATE access_codes SET isActive = 0 WHERE id = ?', [id], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ changes: this.changes });
                }
            });
        });
    }

    // API Usage logging
    async logApiUsage(userId, endpoint, requestData, responseData, tokensUsed, cost) {
        return new Promise((resolve, reject) => {
            const sql = `
                INSERT INTO api_usage (userId, endpoint, requestData, responseData, tokensUsed, cost)
                VALUES (?, ?, ?, ?, ?, ?)
            `;
            
            this.db.run(sql, [
                userId, endpoint, JSON.stringify(requestData), JSON.stringify(responseData),
                tokensUsed, cost
            ], function(err) {
                if (err) {
                    reject(err);
                } else {
                    resolve({ id: this.lastID });
                }
            });
        });
    }

    // Export data
    async exportAllData() {
        return new Promise((resolve, reject) => {
            const exportData = {};
            
            // Export users
            this.db.all('SELECT * FROM users', (err, users) => {
                if (err) {
                    reject(err);
                    return;
                }
                
                exportData.users = users;
                
                // Export API config
                this.db.all('SELECT * FROM api_config', (err, apiConfigs) => {
                    if (err) {
                        reject(err);
                        return;
                    }
                    
                    exportData.apiConfigs = apiConfigs;
                    
                    // Export access codes
                    this.db.all('SELECT * FROM access_codes', (err, accessCodes) => {
                        if (err) {
                            reject(err);
                            return;
                        }
                        
                        exportData.accessCodes = accessCodes;
                        
                        // Export API usage
                        this.db.all('SELECT * FROM api_usage ORDER BY timestamp DESC LIMIT 1000', (err, apiUsage) => {
                            if (err) {
                                reject(err);
                                return;
                            }
                            
                            exportData.apiUsage = apiUsage;
                            exportData.exportedAt = Date.now();
                            
                            resolve(exportData);
                        });
                    });
                });
            });
        });
    }

    // Get database statistics
    async getStats() {
        return new Promise((resolve, reject) => {
            const stats = {};
            
            this.db.get('SELECT COUNT(*) as count FROM users', (err, result) => {
                if (err) {
                    reject(err);
                    return;
                }
                
                stats.totalUsers = result.count;
                
                this.db.get('SELECT COUNT(*) as count FROM users WHERE isAdmin = 0', (err, result) => {
                    if (err) {
                        reject(err);
                        return;
                    }
                    
                    stats.regularUsers = result.count;
                    
                    this.db.get('SELECT COUNT(*) as count FROM access_codes WHERE isActive = 1', (err, result) => {
                        if (err) {
                            reject(err);
                            return;
                        }
                        
                        stats.activeCodes = result.count;
                        
                        this.db.get('SELECT COUNT(*) as count FROM api_usage', (err, result) => {
                            if (err) {
                                reject(err);
                                return;
                            }
                            
                            stats.totalApiCalls = result.count;
                            
                            resolve(stats);
                        });
                    });
                });
            });
        });
    }

    // Close database connection
    close() {
        if (this.db) {
            this.db.close((err) => {
                if (err) {
                    console.error('❌ Error closing database:', err);
                } else {
                    console.log('✅ Database connection closed');
                }
            });
        }
    }
}

module.exports = DatabaseManager;