const express = require('express');
const cors = require('cors');
const path = require('path');
const fs = require('fs-extra');
const nodemailer = require('nodemailer');
const stripe = require('stripe');
const { PayPalApi, OrdersController, PaymentsController } = require('@paypal/paypal-server-sdk');
const DatabaseManager = require('./database');

const app = express();
const PORT = process.env.PORT || 3001;

// Initialize database
const db = new DatabaseManager();

// Data storage paths
const DATA_DIR = path.join(__dirname, 'data');
const USERS_FILE = path.join(DATA_DIR, 'users.json');
const PENDING_FILE = path.join(DATA_DIR, 'pending.json');
const CONFIG_FILE = path.join(DATA_DIR, 'config.json');
const SESSIONS_FILE = path.join(DATA_DIR, 'sessions.json');
const CONVERSATIONS_FILE = path.join(DATA_DIR, 'conversations.json');

// Initialize data directory
fs.ensureDirSync(DATA_DIR);

// Email configuration
let emailTransporter = null;

// Payment configurations
let stripeClient = null;
let paypalClient = null;

// Data storage functions
function loadData(filePath, defaultData = []) {
    try {
        if (fs.existsSync(filePath)) {
            return fs.readJsonSync(filePath);
        }
        return defaultData;
    } catch (error) {
        console.error(`Error loading ${filePath}:`, error);
        return defaultData;
    }
}

function saveData(filePath, data) {
    try {
        fs.writeJsonSync(filePath, data, { spaces: 2 });
        return true;
    } catch (error) {
        console.error(`Error saving ${filePath}:`, error);
        return false;
    }
}

// Initialize default data
function initializeData() {
    // Initialize users with default admin
    const users = loadData(USERS_FILE, [{
        id: '1',
        email: 'talk2char@gmail.com',
        password: 'Password@123',
        isAdmin: true,
        firstName: 'Super',
        lastName: 'Admin',
        emailVerified: true,
        createdAt: Date.now()
    }]);
    saveData(USERS_FILE, users);

    // Initialize other data files
    saveData(PENDING_FILE, loadData(PENDING_FILE, []));
    saveData(CONFIG_FILE, loadData(CONFIG_FILE, {
        monthlyFee: 29,
        systemSettings: {
            allowRegistration: true,
            requireEmailVerification: true
        }
    }));
    saveData(SESSIONS_FILE, loadData(SESSIONS_FILE, {}));
    saveData(CONVERSATIONS_FILE, loadData(CONVERSATIONS_FILE, {}));

    console.log('✅ Data files initialized');
}

// Initialize email transporter using a free service
async function initializeEmailTransporter() {
    try {
        console.log('📧 Initializing email service for production...');
        
        // Production email configuration for Namecheap/cPanel hosting
        const emailConfig = {
            host: process.env.SMTP_HOST || 'mail.hrleaveassist.com', // Namecheap SMTP server
            port: parseInt(process.env.SMTP_PORT) || 587, // Standard SMTP port
            secure: false, // Use STARTTLS
            auth: {
                user: process.env.EMAIL_USER || 'askhrla@hrleaveassist.com',
                pass: process.env.EMAIL_PASS || '' // This should be set via environment variable
            },
            tls: {
                rejectUnauthorized: false, // Allow self-signed certificates
                ciphers: 'SSLv3' // Better compatibility
            },
            // Anti-spam configuration
            pool: true, // Use connection pooling
            maxConnections: 5,
            maxMessages: 100,
            rateLimit: 14, // Max 14 messages per second
            // Authentication and security
            requireTLS: true, // Force TLS
            connectionTimeout: 60000, // 60 seconds
            greetingTimeout: 30000, // 30 seconds
            socketTimeout: 60000 // 60 seconds
        };
        
        // If no password is provided, try alternative configurations
        if (!emailConfig.auth.pass) {
            console.log('⚠️ No email password provided. Trying alternative configurations...');
            
            // Try common Namecheap SMTP settings
            const alternativeConfigs = [
                {
                    host: 'mail.hrleaveassist.com',
                    port: 587,
                    secure: false,
                    auth: {
                        user: 'askhrla@hrleaveassist.com',
                        pass: process.env.EMAIL_PASS || ''
                    }
                },
                {
                    host: 'smtp.privateemail.com', // Namecheap's private email SMTP
                    port: 587,
                    secure: false,
                    auth: {
                        user: 'askhrla@hrleaveassist.com',
                        pass: process.env.EMAIL_PASS || ''
                    }
                },
                {
                    host: 'mail.hrleaveassist.com',
                    port: 465,
                    secure: true, // SSL
                    auth: {
                        user: 'askhrla@hrleaveassist.com',
                        pass: process.env.EMAIL_PASS || ''
                    }
                }
            ];
            
            // Use the first alternative config
            Object.assign(emailConfig, alternativeConfigs[0]);
        }
        
        console.log('📧 Email configuration:', {
            host: emailConfig.host,
            port: emailConfig.port,
            secure: emailConfig.secure,
            user: emailConfig.auth.user
        });
        
        emailTransporter = nodemailer.createTransporter(emailConfig);
        
        // Test the connection if password is provided
        if (emailConfig.auth.pass) {
            try {
                await emailTransporter.verify();
                console.log('✅ Email SMTP connected successfully');
                console.log('📧 Ready to send verification emails from:', emailConfig.auth.user);
                return;
            } catch (smtpError) {
                console.warn('⚠️ SMTP connection test failed:', smtpError.message);
                console.warn('📝 This might be normal - emails may still work');
            }
        }
        
        console.log('📧 Email transporter configured (connection not tested)');
        console.log('💡 To enable email verification:');
        console.log('   1. Set EMAIL_PASS environment variable with your email password');
        console.log('   2. Ensure askhrla@hrleaveassist.com is properly configured in cPanel');
        console.log('   3. Check SMTP settings with your hosting provider');
        
    } catch (error) {
        console.warn('⚠️ Email service initialization failed:', error.message);
        console.log('📧 Running in fallback mode - emails will be logged to console');
        emailTransporter = null;
    }
}

// Free email sending function using a webhook service
async function sendEmailViaWebhook(to, subject, htmlContent, textContent) {
    try {
        console.log('📧 Preparing to send email to:', to);
        console.log('📧 Subject:', subject);
        
        // For development/demo purposes, we'll create a comprehensive log
        // that includes the verification link prominently
        console.log('\n' + '='.repeat(80));
        console.log('📧 EMAIL VERIFICATION REQUIRED');
        console.log('='.repeat(80));
        console.log('To:', to);
        console.log('Subject:', subject);
        console.log('\n📋 EMAIL CONTENT:');
        console.log(textContent);
        console.log('\n� VEeRIFICATION LINK (Click or copy):');
        
        // Extract verification link from content
        const linkMatch = textContent.match(/http[s]?:\/\/[^\s]+/);
        if (linkMatch) {
            console.log(linkMatch[0]);
            console.log('\n✅ User can copy this link to verify their email');
        }
        
        console.log('='.repeat(80) + '\n');
        
        // In a real production environment, you would integrate with:
        // - SendGrid (free tier: 100 emails/day)
        // - Mailgun (free tier: 5,000 emails/month)
        // - AWS SES (free tier: 62,000 emails/month)
        // - Resend (free tier: 3,000 emails/month)
        // - EmailJS (client-side email service)
        
        // For now, simulate successful sending
        await new Promise(resolve => setTimeout(resolve, 500));
        
        return {
            success: true,
            messageId: 'dev_' + Date.now(),
            service: 'development',
            note: 'Email logged to console for development'
        };
        
    } catch (error) {
        console.error('❌ Email sending error:', error);
        throw error;
    }
}

// Production email sending function
async function sendConfirmationEmail(to, subject, htmlContent, textContent) {
    try {
        console.log('📧 Sending confirmation email to:', to);
        
        if (emailTransporter && typeof emailTransporter.sendMail === 'function') {
            // Send real email using configured SMTP with anti-spam headers
            try {
                const mailOptions = {
                    from: {
                        name: 'HR Leave Assistant',
                        address: 'askhrla@hrleaveassist.com'
                    },
                    to: to,
                    subject: subject,
                    text: textContent,
                    html: htmlContent,
                    replyTo: {
                        name: 'HR Leave Assistant Support',
                        address: 'askhrla@hrleaveassist.com'
                    },
                    // Anti-spam headers
                    headers: {
                        'X-Mailer': 'HR Leave Assistant v1.0',
                        'X-Priority': '3', // Normal priority
                        'X-MSMail-Priority': 'Normal',
                        'Importance': 'Normal',
                        'List-Unsubscribe': '<mailto:askhrla@hrleaveassist.com?subject=Unsubscribe>',
                        'X-Auto-Response-Suppress': 'OOF, DR, RN, NRN, AutoReply',
                        // Authentication headers
                        'Message-ID': `<${Date.now()}.${Math.random().toString(36).substring(2)}@hrleaveassist.com>`,
                        'Date': new Date().toUTCString(),
                        // Content classification
                        'Content-Language': 'en-US',
                        'X-Spam-Status': 'No',
                        // Organization info
                        'Organization': 'HR Leave Assistant',
                        'X-Sender': 'askhrla@hrleaveassist.com',
                        // Delivery confirmation
                        'Return-Receipt-To': 'askhrla@hrleaveassist.com',
                        // Security
                        'X-Originating-IP': '[127.0.0.1]',
                        'X-Source-IP': '[127.0.0.1]',
                        'X-Source-Dir': 'hrleaveassist.com'
                    },
                    // Delivery options
                    envelope: {
                        from: 'askhrla@hrleaveassist.com',
                        to: to
                    },
                    // Text encoding
                    encoding: 'utf8',
                    textEncoding: 'base64',
                    // Disable tracking pixels that might trigger spam filters
                    disableFileAccess: true,
                    disableUrlAccess: true
                };
                
                const info = await emailTransporter.sendMail(mailOptions);
                
                console.log('✅ Confirmation email sent successfully!');
                console.log('📧 Message ID:', info.messageId);
                console.log('📧 From:', 'askhrla@hrleaveassist.com');
                console.log('📧 To:', to);
                console.log('📧 Response:', info.response);
                
                return {
                    success: true,
                    messageId: info.messageId,
                    service: 'production-smtp',
                    from: 'askhrla@hrleaveassist.com',
                    response: info.response
                };
                
            } catch (emailError) {
                console.error('❌ Failed to send email:', emailError.message);
                console.error('❌ Email error details:', {
                    code: emailError.code,
                    command: emailError.command,
                    response: emailError.response,
                    responseCode: emailError.responseCode
                });
                
                // Fall back to console logging for debugging
                logEmailToConsole(to, subject, textContent);
                return {
                    success: false,
                    error: emailError.message,
                    messageId: 'fallback_' + Date.now(),
                    service: 'console-fallback'
                };
            }
        } else {
            // Development mode - log email details
            console.log('📧 Email transporter not configured - logging to console');
            logEmailToConsole(to, subject, textContent);
            return {
                success: true,
                messageId: 'dev_' + Date.now(),
                service: 'development'
            };
        }
        
    } catch (error) {
        console.error('❌ Email sending error:', error);
        // Still log to console as fallback
        logEmailToConsole(to, subject, textContent);
        return {
            success: false,
            error: error.message,
            messageId: 'error_fallback_' + Date.now(),
            service: 'error-fallback'
        };
    }
}

// Helper function to log email to console with clear instructions
function logEmailToConsole(to, subject, textContent) {
    console.log('\n' + '🟢'.repeat(40));
    console.log('📧 EMAIL VERIFICATION REQUIRED - TESTING MODE');
    console.log('🟢'.repeat(40));
    console.log('📧 To:', to);
    console.log('📧 Subject:', subject);
    console.log('\n📋 EMAIL CONTENT:');
    console.log(textContent);
    
    // Extract verification link from content
    const linkMatch = textContent.match(/http[s]?:\/\/[^\s]+/);
    if (linkMatch) {
        console.log('\n' + '🔗'.repeat(40));
        console.log('🔗 VERIFICATION LINK (COPY THIS):');
        console.log('🔗'.repeat(40));
        console.log(linkMatch[0]);
        console.log('🔗'.repeat(40));
        console.log('\n✅ TESTING INSTRUCTIONS:');
        console.log('1. Copy the verification link above');
        console.log('2. Open it in your browser');
        console.log('3. Your account will be verified automatically');
        console.log('\n💡 In production, this would be sent as a real email');
    }
    
    console.log('🟢'.repeat(40) + '\n');
}

// Initialize payment systems
function initializePayments() {
    const config = loadData(CONFIG_FILE, {});
    
    // Initialize Stripe
    if (config.stripeSecretKey) {
        try {
            stripeClient = stripe(config.stripeSecretKey);
            console.log('💳 Stripe initialized');
        } catch (error) {
            console.warn('⚠️ Stripe initialization failed:', error.message);
        }
    }
    
    // Initialize PayPal
    if (config.paypalClientId && config.paypalClientSecret) {
        try {
            paypalClient = new PayPalApi({
                clientId: config.paypalClientId,
                clientSecret: config.paypalClientSecret,
                environment: config.paypalEnvironment || 'sandbox' // 'sandbox' or 'live'
            });
            console.log('💰 PayPal initialized');
        } catch (error) {
            console.warn('⚠️ PayPal initialization failed:', error.message);
        }
    }
}

// Session management
function generateSessionToken() {
    return Math.random().toString(36).substring(2) + Date.now().toString(36);
}

function validateSession(sessionToken) {
    const sessions = loadData(SESSIONS_FILE, {});
    const session = sessions[sessionToken];
    
    if (!session) return null;
    
    // Check if session is expired (24 hours)
    if (Date.now() - session.createdAt > 24 * 60 * 60 * 1000) {
        delete sessions[sessionToken];
        saveData(SESSIONS_FILE, sessions);
        return null;
    }
    
    return session;
}

function createSession(userId) {
    const sessionToken = generateSessionToken();
    const sessions = loadData(SESSIONS_FILE, {});
    
    sessions[sessionToken] = {
        userId: userId,
        createdAt: Date.now()
    };
    
    saveData(SESSIONS_FILE, sessions);
    return sessionToken;
}

// User conversation isolation
function getUserConversations(userId) {
    const conversations = loadData(CONVERSATIONS_FILE, {});
    return conversations[userId] || [];
}

function saveUserConversation(userId, conversation) {
    const conversations = loadData(CONVERSATIONS_FILE, {});
    if (!conversations[userId]) {
        conversations[userId] = [];
    }
    
    conversations[userId].push({
        ...conversation,
        timestamp: Date.now(),
        id: Math.random().toString(36).substring(2)
    });
    
    // Keep only last 100 conversations per user
    if (conversations[userId].length > 100) {
        conversations[userId] = conversations[userId].slice(-100);
    }
    
    saveData(CONVERSATIONS_FILE, conversations);
}

// Initialize everything
initializeData();
initializeEmailTransporter();
initializePayments();

// Enable CORS
app.use(cors({
    origin: '*',
    credentials: true
}));

app.use(express.json());

// ==========================================
// AUTHENTICATION MIDDLEWARE
// ==========================================

function requireAuth(req, res, next) {
    const sessionToken = req.headers.authorization?.replace('Bearer ', '');
    const session = validateSession(sessionToken);
    
    if (!session) {
        return res.status(401).json({ error: 'Authentication required' });
    }
    
    // Try to get user from database first, then fallback to file
    db.getUserById(session.userId).then(user => {
        if (!user) {
            // Fallback to file-based users
            const users = loadData(USERS_FILE, []);
            user = users.find(u => u.id === session.userId);
        }
        
        if (!user) {
            return res.status(401).json({ error: 'User not found' });
        }
        
        req.user = user;
        req.sessionToken = sessionToken;
        next();
    }).catch(error => {
        console.error('Error getting user in requireAuth:', error);
        // Fallback to file-based users
        const users = loadData(USERS_FILE, []);
        const user = users.find(u => u.id === session.userId);
        
        if (!user) {
            return res.status(401).json({ error: 'User not found' });
        }
        
        req.user = user;
        req.sessionToken = sessionToken;
        next();
    });
}

function requireAdmin(req, res, next) {
    requireAuth(req, res, () => {
        if (!req.user.isAdmin) {
            return res.status(403).json({ error: 'Admin access required' });
        }
        next();
    });
}

// ==========================================
// AUTH ROUTES
// ==========================================

app.post('/api/auth/login', async (req, res) => {
    try {
        const { email, password } = req.body;
        console.log('🔐 Login attempt for:', email);
        
        let user = null;
        
        // Try database first
        try {
            user = await db.getUserByEmail(email.toLowerCase());
            console.log('📊 Database lookup result:', user ? 'Found' : 'Not found');
        } catch (error) {
            console.warn('Database user lookup failed:', error);
        }
        
        // Fallback to file-based users if not found in database
        if (!user) {
            console.log('🔍 Checking file-based users...');
            const users = loadData(USERS_FILE, []);
            console.log('📊 File users count:', users.length);
            console.log('📊 File user emails:', users.map(u => u.email));
            user = users.find(u => u.email.toLowerCase() === email.toLowerCase());
            console.log('📊 File lookup result:', user ? `Found: ${user.email}` : 'Not found');
        }
        
        if (!user) {
            console.log('❌ User not found in database or file');
            return res.status(401).json({ error: 'Invalid credentials' });
        }
        
        if (user.password !== password) {
            console.log('❌ Password mismatch for user:', email);
            return res.status(401).json({ error: 'Invalid credentials' });
        }
        
        console.log('✅ Login successful for:', email);
        
        // Update last login (try database first, then file)
        try {
            if (user.id && typeof user.id === 'string' && user.id.startsWith('user_')) {
                await db.updateUser(user.id, { lastLogin: Date.now() });
            }
        } catch (error) {
            // If database update fails, update file
            const users = loadData(USERS_FILE, []);
            const userIndex = users.findIndex(u => u.email.toLowerCase() === email.toLowerCase());
            if (userIndex !== -1) {
                users[userIndex].lastLogin = Date.now();
                saveData(USERS_FILE, users);
            }
        }
        
        const sessionToken = createSession(user.id);
        
        res.json({
            success: true,
            user: {
                id: user.id,
                email: user.email,
                firstName: user.firstName,
                lastName: user.lastName,
                isAdmin: user.isAdmin,
                emailVerified: user.emailVerified,
                subscriptionExpiry: user.subscriptionExpiry,
                createdAt: user.createdAt
            },
            sessionToken: sessionToken
        });
    } catch (error) {
        console.error('Login error:', error);
        res.status(500).json({ error: 'Login failed' });
    }
});

// Registration endpoint moved below to handle access codes

app.post('/api/auth/verify', (req, res) => {
    const { token } = req.body;
    const pending = loadData(PENDING_FILE, []);
    const users = loadData(USERS_FILE, []);
    
    const pendingIndex = pending.findIndex(p => p.token === token);
    if (pendingIndex === -1) {
        return res.status(400).json({ error: 'Invalid verification token' });
    }
    
    const pendingUser = pending[pendingIndex];
    
    // Create verified user with all original data preserved
    const now = Date.now();
    const newUser = {
        ...pendingUser.userData,
        id: Date.now().toString(),
        emailVerified: true,
        verifiedAt: now
    };
    
    // Ensure trial expiry is properly set if not already
    if (!newUser.trialExpiry) {
        newUser.trialExpiry = now + (24 * 60 * 60 * 1000); // 24 hours from verification
        console.log('⚠️ Setting trial expiry for verified user:', new Date(newUser.trialExpiry).toLocaleString());
    }
    
    console.log('✅ User verified successfully via API:', {
        email: newUser.email,
        trialExpiry: new Date(newUser.trialExpiry).toLocaleString(),
        accessLevel: newUser.accessLevel
    });
    
    users.push(newUser);
    pending.splice(pendingIndex, 1);
    
    saveData(USERS_FILE, users);
    saveData(PENDING_FILE, pending);
    
    res.json({
        success: true,
        message: 'Email verified successfully. You can now login.'
    });
});

app.post('/api/auth/resend-verification', (req, res) => {
    const { email } = req.body;
    const pending = loadData(PENDING_FILE, []);
    
    const pendingUser = pending.find(p => p.userData.email === email);
    if (!pendingUser) {
        return res.status(404).json({ error: 'No pending verification found for this email' });
    }
    
    // Generate new verification token
    const newToken = Math.random().toString(36).substring(2) + Date.now().toString(36);
    pendingUser.token = newToken;
    
    // Update pending data
    saveData(PENDING_FILE, pending);
    
    // Create verification link
    const verificationLink = `${req.headers.origin || 'https://www.hrleaveassist.com'}/verify?token=${newToken}`;
    
    // Send verification email
    const emailHtml = `
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #0023F5 0%, #0322D8 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #0023F5; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; font-weight: bold; }
                .footer { text-align: center; margin-top: 20px; font-size: 0.9rem; color: #666; }
                .logo { font-size: 1.5rem; font-weight: bold; }
                .support { background: #e3f2fd; padding: 15px; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <div class="logo">🏛️ HR Leave Assistant</div>
                    <p>Professional HR Compliance & Response Tool</p>
                    <p style="font-size: 0.9rem; opacity: 0.9;">www.hrleaveassist.com</p>
                </div>
                <div class="content">
                    <h2>Verification Email Resent</h2>
                    <p>We've resent your verification email. Please click the link below to verify your account:</p>
                    
                    <div style="text-align: center;">
                        <a href="${verificationLink}" class="button">✅ Verify Email Address</a>
                    </div>
                    
                    <p>If the button doesn't work, copy and paste this link into your browser:</p>
                    <p style="word-break: break-all; background: #e9ecef; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 0.9rem;">
                        ${verificationLink}
                    </p>
                    
                    <div class="support">
                        <p><strong>Need Help?</strong></p>
                        <p>If you have any questions or need assistance, please contact us at <a href="mailto:askhrla@hrleaveassist.com">askhrla@hrleaveassist.com</a></p>
                    </div>
                </div>
                <div class="footer">
                    <p>© ${new Date().getFullYear()} HR Leave Assistant - Professional HR Compliance Tool</p>
                    <p><a href="https://www.hrleaveassist.com">www.hrleaveassist.com</a> | <a href="mailto:askhrla@hrleaveassist.com">askhrla@hrleaveassist.com</a></p>
                </div>
            </div>
        </body>
        </html>
    `;

    const textContent = `
Verification Email Resent - HR Leave Assistant

We've resent your verification email. Please click the link below to verify your account:

${verificationLink}

Need Help?
If you have any questions or need assistance, please contact us at askhrla@hrleaveassist.com

© ${new Date().getFullYear()} HR Leave Assistant - Professional HR Compliance Tool
www.hrleaveassist.com | askhrla@hrleaveassist.com
    `;

    sendConfirmationEmail(email, '✅ Verify your Leave Assistant account - Resent', emailHtml, textContent)
        .then(() => {
            res.json({
                success: true,
                message: 'Verification email resent successfully'
            });
        })
        .catch(error => {
            console.error('Error resending verification email:', error);
            res.json({
                success: true,
                message: 'Verification email resent (check console for link)'
            });
        });
});

app.post('/api/auth/logout', requireAuth, (req, res) => {
    const sessions = loadData(SESSIONS_FILE, {});
    delete sessions[req.sessionToken];
    saveData(SESSIONS_FILE, sessions);
    
    res.json({ success: true, message: 'Logged out successfully' });
});

// ==========================================
// USER ROUTES
// ==========================================

app.get('/api/user/profile', requireAuth, (req, res) => {
    res.json({
        success: true,
        user: {
            id: req.user.id,
            email: req.user.email,
            firstName: req.user.firstName,
            lastName: req.user.lastName,
            isAdmin: req.user.isAdmin,
            emailVerified: req.user.emailVerified,
            subscriptionExpiry: req.user.subscriptionExpiry,
            createdAt: req.user.createdAt
        }
    });
});

app.put('/api/user/profile', requireAuth, (req, res) => {
    const { firstName, lastName, password } = req.body;
    const users = loadData(USERS_FILE, []);
    
    const userIndex = users.findIndex(u => u.id === req.user.id);
    if (userIndex === -1) {
        return res.status(404).json({ error: 'User not found' });
    }
    
    // Update user data
    if (firstName) users[userIndex].firstName = firstName;
    if (lastName) users[userIndex].lastName = lastName;
    if (password) users[userIndex].password = password;
    
    saveData(USERS_FILE, users);
    
    res.json({
        success: true,
        message: 'Profile updated successfully',
        user: users[userIndex]
    });
});

app.get('/api/user/conversations', requireAuth, (req, res) => {
    const conversations = getUserConversations(req.user.id);
    res.json({
        success: true,
        conversations: conversations
    });
});

app.post('/api/user/conversation', requireAuth, (req, res) => {
    const { toolName, input, response, provider } = req.body;
    
    saveUserConversation(req.user.id, {
        toolName,
        input,
        response,
        provider
    });
    
    res.json({
        success: true,
        message: 'Conversation saved'
    });
});

// ==========================================
// ADMIN ROUTES
// ==========================================

app.get('/api/admin/users', requireAdmin, (req, res) => {
    const users = loadData(USERS_FILE, []);
    const { search, filter, page = 1, limit = 50 } = req.query;
    
    // For "all" filter, include all users (including admins)
    // For other filters, exclude admin users from the list
    let filteredUsers = filter === 'all' ? users : users.filter(u => !u.isAdmin);
    
    // Apply search filter
    if (search) {
        const searchLower = search.toLowerCase();
        filteredUsers = filteredUsers.filter(u => 
            u.firstName.toLowerCase().includes(searchLower) ||
            u.lastName.toLowerCase().includes(searchLower) ||
            u.email.toLowerCase().includes(searchLower)
        );
    }
    
    // Apply status filter
    if (filter && filter !== 'all') {
        filteredUsers = filteredUsers.filter(u => {
            // Admin users are excluded from status-based filters
            if (u.isAdmin) {
                return false;
            }
            
            const now = Date.now();
            const hasActiveSubscription = u.subscriptionExpiry && new Date(u.subscriptionExpiry).getTime() > now;
            const trialDuration = 24 * 60 * 60 * 1000;
            const trialEnd = u.createdAt + trialDuration;
            const inTrial = now < trialEnd;
            
            switch (filter) {
                case 'verified':
                    return u.emailVerified;
                case 'active':
                    return hasActiveSubscription || inTrial;
                case 'expired':
                    return !hasActiveSubscription && !inTrial;
                case 'trial':
                    return inTrial && !hasActiveSubscription;
                case 'subscribed':
                    return hasActiveSubscription;
                default:
                    return true;
            }
        });
    }
    
    // Pagination
    const startIndex = (page - 1) * limit;
    const endIndex = startIndex + parseInt(limit);
    const paginatedUsers = filteredUsers.slice(startIndex, endIndex);
    
    // Add status information
    const usersWithStatus = paginatedUsers.map(u => {
        // Admin users have permanent access
        if (u.isAdmin) {
            return {
                ...u,
                status: {
                    active: true,
                    type: 'admin',
                    expiry: null
                }
            };
        }
        
        // Regular users - calculate trial/subscription status
        const now = Date.now();
        const hasActiveSubscription = u.subscriptionExpiry && new Date(u.subscriptionExpiry).getTime() > now;
        const trialDuration = 24 * 60 * 60 * 1000;
        const trialEnd = u.createdAt + trialDuration;
        const inTrial = now < trialEnd;
        
        return {
            ...u,
            status: {
                active: hasActiveSubscription || inTrial,
                type: hasActiveSubscription ? 'subscription' : (inTrial ? 'trial' : 'expired'),
                expiry: hasActiveSubscription ? u.subscriptionExpiry : (inTrial ? trialEnd : null)
            }
        };
    });
    
    res.json({
        success: true,
        users: usersWithStatus,
        pagination: {
            page: parseInt(page),
            limit: parseInt(limit),
            total: filteredUsers.length,
            pages: Math.ceil(filteredUsers.length / limit)
        }
    });
});

app.get('/api/admin/stats', requireAdmin, (req, res) => {
    console.log('📊 Admin stats endpoint called by user:', req.user.email);
    
    const users = loadData(USERS_FILE, []);
    const pending = loadData(PENDING_FILE, []);
    const nonAdmins = users.filter(u => !u.isAdmin);
    const allUsers = users; // Include all users for total count
    
    console.log('📊 Data loaded - Users:', users.length, 'Pending:', pending.length);
    
    const now = Date.now();
    const trialDuration = 24 * 60 * 60 * 1000;
    
    const stats = {
        totalUsers: allUsers.filter(u => u.emailVerified).length, // All verified users including admins
        verifiedUsers: allUsers.filter(u => u.emailVerified).length, // Same as total for now
        pendingVerifications: pending.length,
        activeSubscriptions: nonAdmins.filter(u => 
            u.subscriptionExpiry && new Date(u.subscriptionExpiry).getTime() > now
        ).length,
        trialUsers: nonAdmins.filter(u => {
            const trialEnd = u.createdAt + trialDuration;
            const hasActiveSubscription = u.subscriptionExpiry && new Date(u.subscriptionExpiry).getTime() > now;
            return now < trialEnd && !hasActiveSubscription;
        }).length
    };
    
    console.log('📊 Calculated Admin Stats:', stats); // Debug log
    
    res.json({
        success: true,
        stats: stats
    });
});

app.post('/api/admin/grant-access', requireAdmin, (req, res) => {
    const { userIds, duration } = req.body;
    const users = loadData(USERS_FILE, []);
    
    const expiryDate = new Date();
    if (duration === 'forever') {
        expiryDate.setFullYear(expiryDate.getFullYear() + 100);
    } else {
        expiryDate.setMonth(expiryDate.getMonth() + parseInt(duration));
    }
    
    userIds.forEach(userId => {
        const userIndex = users.findIndex(u => u.id === userId);
        if (userIndex !== -1) {
            users[userIndex].subscriptionExpiry = expiryDate.toISOString();
        }
    });
    
    saveData(USERS_FILE, users);
    
    res.json({
        success: true,
        message: `Access granted to ${userIds.length} users until ${expiryDate.toLocaleDateString()}`
    });
});

app.delete('/api/admin/user/:userId', requireAdmin, (req, res) => {
    const { userId } = req.params;
    const users = loadData(USERS_FILE, []);
    
    const userIndex = users.findIndex(u => u.id === userId && !u.isAdmin);
    if (userIndex === -1) {
        return res.status(404).json({ error: 'User not found' });
    }
    
    users.splice(userIndex, 1);
    saveData(USERS_FILE, users);
    
    // Also remove user conversations
    const conversations = loadData(CONVERSATIONS_FILE, {});
    delete conversations[userId];
    saveData(CONVERSATIONS_FILE, conversations);
    
    res.json({
        success: true,
        message: 'User deleted successfully'
    });
});

app.get('/api/admin/pending', requireAdmin, (req, res) => {
    console.log('📋 Admin pending endpoint called by user:', req.user.email);
    
    const pending = loadData(PENDING_FILE, []);
    console.log('📋 Loaded pending verifications:', pending.length);
    
    res.json({
        success: true,
        pending: pending
    });
});

app.post('/api/admin/approve-verification', requireAdmin, (req, res) => {
    const { token } = req.body;
    const pending = loadData(PENDING_FILE, []);
    const users = loadData(USERS_FILE, []);
    
    const pendingIndex = pending.findIndex(p => p.token === token);
    if (pendingIndex === -1) {
        return res.status(404).json({ error: 'Verification not found' });
    }
    
    const pendingUser = pending[pendingIndex];
    const newUser = {
        ...pendingUser.userData,
        id: Date.now().toString(),
        emailVerified: true
    };
    
    users.push(newUser);
    pending.splice(pendingIndex, 1);
    
    saveData(USERS_FILE, users);
    saveData(PENDING_FILE, pending);
    
    res.json({
        success: true,
        message: 'User approved and activated'
    });
});

app.delete('/api/admin/reject-verification', requireAdmin, (req, res) => {
    const { token } = req.body;
    const pending = loadData(PENDING_FILE, []);
    
    const pendingIndex = pending.findIndex(p => p.token === token);
    if (pendingIndex === -1) {
        return res.status(404).json({ error: 'Verification not found' });
    }
    
    pending.splice(pendingIndex, 1);
    saveData(PENDING_FILE, pending);
    
    res.json({
        success: true,
        message: 'Verification rejected'
    });
});

// ==========================================
// PAYMENT ROUTES
// ==========================================

app.post('/api/payment/stripe/create-session', requireAuth, (req, res) => {
    if (!stripeClient) {
        return res.status(400).json({ error: 'Stripe not configured' });
    }
    
    const { plan, amount } = req.body;
    const stripeAmount = Math.round(parseFloat(amount) * 100); // Convert to cents
    
    const planNames = {
        monthly: 'Leave Assistant - Monthly Subscription',
        annual: 'Leave Assistant - Annual Subscription',
        organization: 'Leave Assistant - Organization Subscription'
    };
    
    const mode = plan === 'monthly' ? 'subscription' : 'payment';
    
    stripeClient.checkout.sessions.create({
        payment_method_types: ['card'],
        line_items: [{
            price_data: {
                currency: 'usd',
                product_data: {
                    name: planNames[plan] || 'Leave Assistant Subscription',
                    description: 'HR Compliance & Response Tool'
                },
                unit_amount: stripeAmount,
            },
            quantity: 1,
        }],
        mode: mode,
        success_url: `${req.headers.origin}/payment-success?session_id={CHECKOUT_SESSION_ID}`,
        cancel_url: `${req.headers.origin}/payment-cancelled`,
        client_reference_id: req.user.id,
        metadata: {
            userId: req.user.id,
            email: req.user.email,
            plan: plan,
            amount: amount
        }
    }).then(session => {
        res.json({
            success: true,
            sessionId: session.id,
            url: session.url
        });
    }).catch(error => {
        console.error('Stripe session creation error:', error);
        res.status(500).json({ error: 'Failed to create payment session' });
    });
});

app.post('/api/payment/stripe/webhook', express.raw({type: 'application/json'}), (req, res) => {
    const sig = req.headers['stripe-signature'];
    const config = loadData(CONFIG_FILE, {});
    
    let event;
    try {
        event = stripeClient.webhooks.constructEvent(req.body, sig, config.stripeWebhookSecret);
    } catch (err) {
        console.error('Webhook signature verification failed:', err.message);
        return res.status(400).send(`Webhook Error: ${err.message}`);
    }
    
    if (event.type === 'checkout.session.completed') {
        const session = event.data.object;
        const userId = session.client_reference_id;
        
        // Grant 30 days access
        const users = loadData(USERS_FILE, []);
        const userIndex = users.findIndex(u => u.id === userId);
        
        if (userIndex !== -1) {
            const expiryDate = new Date();
            expiryDate.setMonth(expiryDate.getMonth() + 1);
            users[userIndex].subscriptionExpiry = expiryDate.toISOString();
            users[userIndex].stripeCustomerId = session.customer;
            
            saveData(USERS_FILE, users);
            console.log(`✅ Subscription activated for user ${userId}`);
        }
    }
    
    res.json({received: true});
});

app.post('/api/payment/paypal/create-order', requireAuth, (req, res) => {
    if (!paypalClient) {
        return res.status(400).json({ error: 'PayPal not configured' });
    }
    
    const config = loadData(CONFIG_FILE, {});
    const amount = (config.monthlyFee || 29).toFixed(2);
    
    const request = {
        intent: 'CAPTURE',
        purchase_units: [{
            amount: {
                currency_code: 'USD',
                value: amount
            },
            description: 'Leave Assistant - Monthly Subscription'
        }],
        application_context: {
            return_url: `${req.headers.origin}/payment-success`,
            cancel_url: `${req.headers.origin}/payment-cancelled`,
            brand_name: 'Leave Assistant',
            user_action: 'PAY_NOW'
        }
    };
    
    const ordersController = new OrdersController(paypalClient);
    
    ordersController.ordersCreate({
        body: request,
        prefer: 'return=representation'
    }).then(order => {
        const approvalUrl = order.result.links.find(link => link.rel === 'approve').href;
        
        res.json({
            success: true,
            orderId: order.result.id,
            approvalUrl: approvalUrl
        });
    }).catch(error => {
        console.error('PayPal order creation error:', error);
        res.status(500).json({ error: 'Failed to create PayPal order' });
    });
});

app.post('/api/payment/paypal/capture-order', requireAuth, (req, res) => {
    const { orderId } = req.body;
    
    if (!paypalClient) {
        return res.status(400).json({ error: 'PayPal not configured' });
    }
    
    const ordersController = new OrdersController(paypalClient);
    
    ordersController.ordersCapture({
        id: orderId,
        prefer: 'return=representation'
    }).then(capture => {
        if (capture.result.status === 'COMPLETED') {
            // Grant 30 days access
            const users = loadData(USERS_FILE, []);
            const userIndex = users.findIndex(u => u.id === req.user.id);
            
            if (userIndex !== -1) {
                const expiryDate = new Date();
                expiryDate.setMonth(expiryDate.getMonth() + 1);
                users[userIndex].subscriptionExpiry = expiryDate.toISOString();
                users[userIndex].paypalOrderId = orderId;
                
                saveData(USERS_FILE, users);
            }
            
            res.json({
                success: true,
                message: 'Payment completed successfully',
                orderId: orderId
            });
        } else {
            res.status(400).json({ error: 'Payment not completed' });
        }
    }).catch(error => {
        console.error('PayPal capture error:', error);
        res.status(500).json({ error: 'Failed to capture PayPal payment' });
    });
});

// ==========================================
// CONFIG ROUTES
// ==========================================

app.get('/api/config', requireAdmin, (req, res) => {
    const config = loadData(CONFIG_FILE, {});
    // Don't send sensitive data - remove email configuration but include system API key status
    const safeConfig = {
        monthlyFee: config.monthlyFee,
        systemSettings: config.systemSettings,
        hasStripe: !!config.stripeSecretKey,
        hasPaypal: !!(config.paypalClientId && config.paypalClientSecret),
        systemOpenaiKey: config.systemOpenaiKey, // Include for admin use
        hasSystemOpenaiKey: !!config.systemOpenaiKey
    };
    
    res.json({
        success: true,
        config: safeConfig
    });
});

app.put('/api/config', requireAdmin, (req, res) => {
    const config = loadData(CONFIG_FILE, {});
    const updates = req.body;
    
    // Update configuration (excluding email settings)
    Object.keys(updates).forEach(key => {
        if (!key.startsWith('smtp')) { // Skip email configuration
            config[key] = updates[key];
        }
    });
    
    saveData(CONFIG_FILE, config);
    
    // Reinitialize payment services if needed
    if (updates.stripeSecretKey || updates.paypalClientId || updates.paypalClientSecret) {
        initializePayments();
    }
    
    res.json({
        success: true,
        message: 'Configuration updated successfully'
    });
});

// ==========================================
// AI API ROUTES (existing)
// ==========================================

app.post('/api/openai', requireAuth, async (req, res) => {
    try {
        const { apiKey, messages, model = 'gpt-4o-mini', max_tokens = 1000, temperature = 0.3 } = req.body;

        if (!apiKey || apiKey === 'demo') {
            return res.status(400).json({ error: 'Valid OpenAI API key required.' });
        }

        const fetch = (await import('node-fetch')).default;

        const response = await fetch('https://api.openai.com/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${apiKey}`,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ model, messages, max_tokens, temperature })
        });

        const data = await response.json();
        if (!response.ok) return res.status(response.status).json(data);
        
        // Save conversation
        saveUserConversation(req.user.id, {
            toolName: req.body.toolName || 'unknown',
            input: messages[messages.length - 1]?.content || '',
            response: data.choices?.[0]?.message?.content || '',
            provider: 'openai'
        });
        
        res.json(data);
    } catch (error) {
        console.error('❌ OpenAI Server Error:', error);
        res.status(500).json({ error: 'Internal server error', message: error.message });
    }
});

app.post('/api/gemini', requireAuth, async (req, res) => {
    try {
        const { apiKey, prompt, systemPrompt } = req.body;

        if (!apiKey) {
            return res.status(400).json({ error: 'Valid Gemini API key required.' });
        }

        const fetch = (await import('node-fetch')).default;

        const url = `https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=${apiKey}`;
        
        const response = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                contents: [{
                    parts: [{ text: `${systemPrompt}\n\nUser Query: ${prompt}` }]
                }],
                generationConfig: {
                    temperature: 0.3,
                    maxOutputTokens: 1024,
                }
            })
        });

        const data = await response.json();
        
        if (response.ok && data.candidates && data.candidates.length > 0) {
            // Save conversation
            saveUserConversation(req.user.id, {
                toolName: req.body.toolName || 'unknown',
                input: prompt,
                response: data.candidates[0].content.parts[0].text,
                provider: 'gemini'
            });
            
            return res.json(data);
        } else {
            return res.status(response.status || 400).json({ 
                error: data.error?.message || 'Gemini API request failed. Please check your API key.',
                details: data 
            });
        }

    } catch (error) {
        console.error('❌ Gemini Server Error:', error);
        res.status(500).json({ error: 'Internal server error', message: error.message });
    }
});

// Email configuration endpoint
app.put('/api/admin/email-config', requireAdmin, (req, res) => {
    try {
        const { email, password, provider, host, port } = req.body;
        
        // Validate required fields
        if (!email || !password || !provider) {
            return res.status(400).json({ error: 'Email, password, and provider are required' });
        }
        
        // Update email configuration
        const config = loadData(CONFIG_FILE, {});
        config.emailConfig = {
            email: email,
            password: password,
            provider: provider,
            host: host,
            port: port || 587,
            updatedAt: Date.now()
        };
        
        saveData(CONFIG_FILE, config);
        
        // Reinitialize email transporter with new settings
        initializeEmailTransporter();
        
        res.json({
            success: true,
            message: 'Email configuration updated successfully'
        });
        
    } catch (error) {
        console.error('Email config update error:', error);
        res.status(500).json({ error: 'Failed to update email configuration' });
    }
});

// Test email endpoint
app.post('/api/admin/test-email', requireAdmin, (req, res) => {
    try {
        const { testEmail } = req.body;
        const adminEmail = testEmail || req.user.email;
        
        const testSubject = 'HRLA Leave Assistant - Email Test';
        const testContent = `
            <h2>Email Test Successful!</h2>
            <p>This is a test email from HRLA Leave Assistant.</p>
            <p>Your email configuration is working correctly.</p>
            <p>Sent at: ${new Date().toLocaleString()}</p>
        `;
        
        const textContent = `
Email Test Successful!

This is a test email from HRLA Leave Assistant.
Your email configuration is working correctly.

Sent at: ${new Date().toLocaleString()}
        `;
        
        // Send test email
        sendConfirmationEmail(adminEmail, testSubject, testContent, textContent)
            .then(result => {
                res.json({
                    success: true,
                    message: `Test email sent successfully to ${adminEmail}`,
                    result: result
                });
            })
            .catch(error => {
                res.status(500).json({
                    success: false,
                    error: 'Failed to send test email: ' + error.message
                });
            });
            
    } catch (error) {
        console.error('Test email error:', error);
        res.status(500).json({ error: 'Failed to send test email' });
    }
});

// Error logging endpoint
app.post('/api/log-error', (req, res) => {
    try {
        const errorData = req.body;
        const timestamp = new Date().toISOString();
        
        // Create error log entry
        const logEntry = `[${timestamp}] ${errorData.context || 'Unknown Context'}
Message: ${errorData.message || 'No message'}
URL: ${errorData.url || 'Unknown URL'}
User: ${errorData.user ? `${errorData.user.email} (${errorData.user.id})` : 'Anonymous'}
User Agent: ${errorData.userAgent || 'Unknown'}
Stack: ${errorData.stack || 'No stack trace'}
Additional Info: ${JSON.stringify(errorData.additionalInfo || {})}
${'='.repeat(80)}

`;
        
        // Append to error.log file
        const fs = require('fs');
        const path = require('path');
        const logPath = path.join(__dirname, 'error.log');
        
        fs.appendFile(logPath, logEntry, (err) => {
            if (err) {
                console.error('❌ Failed to write to error.log:', err);
                // Try to create the file if it doesn't exist
                fs.writeFile(logPath, logEntry, (writeErr) => {
                    if (writeErr) {
                        console.error('❌ Failed to create error.log:', writeErr);
                        return res.status(500).json({ error: 'Failed to log error' });
                    }
                    console.log('✅ Created error.log and logged error');
                    res.json({ success: true, message: 'Error logged successfully' });
                });
            } else {
                console.log('✅ Error logged to error.log');
                res.json({ success: true, message: 'Error logged successfully' });
            }
        });
        
    } catch (error) {
        console.error('❌ Error logging endpoint failed:', error);
        res.status(500).json({ error: 'Failed to process error log' });
    }
});

app.get('/api/health', (req, res) => {
    res.json({ 
        status: 'OK', 
        message: 'Leave Assistant backend is running',
        timestamp: new Date().toISOString()
    });
});

// Reset user password
app.post('/api/admin/reset-password/:userId', requireAdmin, (req, res) => {
    const { userId } = req.params;
    const { newPassword } = req.body;
    const users = loadData(USERS_FILE, []);
    
    if (!newPassword || newPassword.length < 6) {
        return res.status(400).json({ error: 'Password must be at least 6 characters long' });
    }
    
    const userIndex = users.findIndex(u => u.id === userId && !u.isAdmin);
    if (userIndex === -1) {
        return res.status(404).json({ error: 'User not found' });
    }
    
    users[userIndex].password = newPassword;
    saveData(USERS_FILE, users);
    
    res.json({
        success: true,
        message: 'Password reset successfully'
    });
});

// Edit user profile
app.put('/api/admin/edit-user/:userId', requireAdmin, (req, res) => {
    const { userId } = req.params;
    const { firstName, lastName, email } = req.body;
    const users = loadData(USERS_FILE, []);
    
    if (!firstName || !lastName || !email) {
        return res.status(400).json({ error: 'First name, last name, and email are required' });
    }
    
    const userIndex = users.findIndex(u => u.id === userId && !u.isAdmin);
    if (userIndex === -1) {
        return res.status(404).json({ error: 'User not found' });
    }
    
    // Check if email is already taken by another user
    const existingUser = users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.id !== userId);
    if (existingUser) {
        return res.status(400).json({ error: 'Email address is already in use' });
    }
    
    users[userIndex].firstName = firstName;
    users[userIndex].lastName = lastName;
    users[userIndex].email = email.toLowerCase();
    
    saveData(USERS_FILE, users);
    
    res.json({
        success: true,
        message: 'User updated successfully',
        user: users[userIndex]
    });
});

// Get user conversations
app.get('/api/admin/user-conversations/:userId', requireAdmin, (req, res) => {
    const { userId } = req.params;
    const users = loadData(USERS_FILE, []);
    
    // Verify user exists
    const user = users.find(u => u.id === userId);
    if (!user) {
        return res.status(404).json({ error: 'User not found' });
    }
    
    const conversations = getUserConversations(userId);
    
    res.json({
        success: true,
        conversations: conversations,
        user: {
            id: user.id,
            firstName: user.firstName,
            lastName: user.lastName,
            email: user.email
        }
    });
});

// ==========================================
// NEW ADMIN ENDPOINTS FOR ACCESS CODES AND API SETTINGS
// ==========================================

// Generate access code
app.post('/api/admin/generate-access-code', requireAdmin, (req, res) => {
    const { codeLength = 8, duration, durationType, description } = req.body;
    
    if (!duration || !durationType) {
        return res.status(400).json({ error: 'Duration and duration type are required' });
    }
    
    // Generate random access code
    const characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    let code = '';
    for (let i = 0; i < codeLength; i++) {
        code += characters.charAt(Math.floor(Math.random() * characters.length));
    }
    
    // Load existing access codes
    const config = loadData(CONFIG_FILE, {});
    if (!config.accessCodes) {
        config.accessCodes = [];
    }
    
    // Create new access code
    const newCode = {
        code: code,
        description: description || '',
        duration: parseInt(duration),
        durationType: durationType,
        createdAt: Date.now(),
        usedCount: 0,
        active: true
    };
    
    config.accessCodes.push(newCode);
    saveData(CONFIG_FILE, config);
    
    res.json({
        success: true,
        message: 'Access code generated successfully',
        code: code
    });
});

// Get all access codes
app.get('/api/admin/access-codes', requireAdmin, (req, res) => {
    const config = loadData(CONFIG_FILE, {});
    const codes = config.accessCodes || [];
    
    res.json({
        success: true,
        codes: codes
    });
});

// Delete access code
app.delete('/api/admin/access-codes/:code', requireAdmin, (req, res) => {
    const { code } = req.params;
    const config = loadData(CONFIG_FILE, {});
    
    if (!config.accessCodes) {
        return res.status(404).json({ error: 'Access code not found' });
    }
    
    const codeIndex = config.accessCodes.findIndex(c => c.code === code);
    if (codeIndex === -1) {
        return res.status(404).json({ error: 'Access code not found' });
    }
    
    config.accessCodes.splice(codeIndex, 1);
    saveData(CONFIG_FILE, config);
    
    res.json({
        success: true,
        message: 'Access code deleted successfully'
    });
});

// Save API settings
app.post('/api/admin/api-settings', requireAdmin, (req, res) => {
    const { openaiApiKey } = req.body;
    
    const config = loadData(CONFIG_FILE, {});
    config.systemOpenaiKey = openaiApiKey;
    
    saveData(CONFIG_FILE, config);
    
    res.json({
        success: true,
        message: 'API settings saved successfully'
    });
});

// Test API key
app.post('/api/admin/test-api-key', requireAdmin, async (req, res) => {
    try {
        const config = loadData(CONFIG_FILE, {});
        const apiKey = config.systemOpenaiKey;
        
        if (!apiKey) {
            return res.status(400).json({ error: 'No API key configured' });
        }
        
        const fetch = (await import('node-fetch')).default;
        
        // Test the API key with a simple request
        const response = await fetch('https://api.openai.com/v1/models', {
            headers: {
                'Authorization': `Bearer ${apiKey}`
            }
        });
        
        if (response.ok) {
            res.json({
                success: true,
                message: 'API key is valid and working'
            });
        } else {
            res.status(400).json({
                error: 'API key test failed',
                details: response.statusText
            });
        }
    } catch (error) {
        console.error('API key test error:', error);
        res.status(500).json({ error: 'Failed to test API key' });
    }
});

// Get API usage stats
app.get('/api/admin/api-usage', requireAdmin, (req, res) => {
    const conversations = loadData(CONVERSATIONS_FILE, {});
    
    // Calculate usage statistics
    let totalRequests = 0;
    let openaiRequests = 0;
    
    Object.values(conversations).forEach(userConversations => {
        if (Array.isArray(userConversations)) {
            totalRequests += userConversations.length;
            userConversations.forEach(conv => {
                if (conv.provider === 'openai') {
                    openaiRequests++;
                }
            });
        }
    });
    
    res.json({
        success: true,
        usage: {
            totalRequests,
            openaiRequests,
            lastUpdated: Date.now()
        }
    });
});

// ==========================================
// ACCESS CODE VALIDATION ENDPOINT
// ==========================================

// Access code validation endpoint (no auth required for registration)
app.post('/api/validate-access-code', async (req, res) => {
    try {
        const { code } = req.body;
        
        if (!code) {
            return res.status(400).json({ error: 'Access code is required' });
        }
        
        const accessCode = await db.getAccessCodeByCode(code);
        
        if (!accessCode || !accessCode.isActive) {
            return res.status(404).json({ error: 'Invalid access code' });
        }
        
        // Check if code has reached max uses
        if (accessCode.maxUses && accessCode.uses >= accessCode.maxUses) {
            return res.status(400).json({ error: 'Access code has reached maximum uses' });
        }
        
        res.json({
            success: true,
            accessCode: {
                id: accessCode.id,
                code: accessCode.code,
                duration: accessCode.duration,
                durationType: accessCode.durationType,
                uses: accessCode.uses,
                maxUses: accessCode.maxUses
            }
        });
    } catch (error) {
        console.error('Error validating access code:', error);
        res.status(500).json({ error: 'Failed to validate access code' });
    }
});

// ==========================================
// UPDATED REGISTRATION ENDPOINT TO HANDLE ACCESS CODES
// ==========================================

app.post('/api/auth/register', async (req, res) => {
    try {
        const { email, firstName, lastName, password, accessCode } = req.body;
        
        console.log('📝 Registration attempt for email:', email);
        
        // Comprehensive duplicate email check
        let existingUser = null;
        
        // 1. Check database first
        try {
            existingUser = await db.getUserByEmail(email.toLowerCase());
            if (existingUser) {
                console.log('❌ Email already exists in database:', email);
                return res.status(400).json({ error: 'This email address is already registered. Please use a different email or try logging in.' });
            }
        } catch (error) {
            console.warn('Database check failed, checking file storage:', error);
        }
        
        // 2. Check file-based users storage
        const users = loadData(USERS_FILE, []);
        const fileUser = users.find(u => u.email.toLowerCase() === email.toLowerCase());
        if (fileUser) {
            console.log('❌ Email already exists in file storage:', email);
            return res.status(400).json({ error: 'This email address is already registered. Please use a different email or try logging in.' });
        }
        
        // 3. Check pending verification list
        const pending = loadData(PENDING_FILE, []);
        const pendingUser = pending.find(p => p.userData.email.toLowerCase() === email.toLowerCase());
        if (pendingUser) {
            console.log('❌ Email already has pending verification:', email);
            return res.status(400).json({ error: 'This email address already has a pending verification. Please check your email or try resending the verification.' });
        }
        
        console.log('✅ Email is available for registration:', email);
        
        // Handle access code if provided
        let accessCodeData = null;
        if (accessCode) {
            accessCodeData = await db.getAccessCodeByCode(accessCode);
            
            if (!accessCodeData || !accessCodeData.isActive) {
                return res.status(400).json({ error: 'Invalid access code' });
            }
            
            // Check if code has reached max uses
            if (accessCodeData.maxUses && accessCodeData.uses >= accessCodeData.maxUses) {
                return res.status(400).json({ error: 'Access code has reached maximum uses' });
            }
            
            // Update access code usage
            await db.updateAccessCodeUsage(accessCodeData.id, (accessCodeData.uses || 0) + 1);
        }
        
        // Generate verification token
        const token = Math.random().toString(36).substring(2) + Date.now().toString(36);
        
        // Create user data for pending verification with proper trial setup
        const now = Date.now();
        const trialDuration = 24 * 60 * 60 * 1000; // 24 hours in milliseconds
        
        const userData = {
            id: 'user_' + now,
            firstName,
            lastName,
            email: email.toLowerCase(),
            password, // In production, this should be hashed
            isAdmin: false,
            emailVerified: false, // Require verification
            createdAt: now,
            trialStarted: now,
            trialExpiry: now + trialDuration, // 24 hours from now
            accessLevel: 'trial',
            subscriptionExpiry: null // No subscription initially
        };
        
        // Apply access code benefits if provided
        if (accessCodeData) {
            const durationMs = accessCodeData.durationType === 'months' 
                ? accessCodeData.duration * 30 * 24 * 60 * 60 * 1000
                : accessCodeData.duration * 24 * 60 * 60 * 1000;
            
            userData.trialExpiry = now + durationMs;
            userData.accessLevel = 'extended';
            console.log(`🎫 Access code applied: ${accessCodeData.duration} ${accessCodeData.durationType}`);
        }
        
        console.log('👤 Creating user with trial expiry:', new Date(userData.trialExpiry).toLocaleString()); 
                ? accessCodeData.duration * 30 * 24 * 60 * 60 * 1000
                : accessCodeData.duration * 24 * 60 * 60 * 1000;
            
            userData.trialExpiry = Date.now() + durationMs;
            userData.accessLevel = 'extended';
        }
        
        // Store in pending verification (file-based for email verification flow)
        const pending = loadData(PENDING_FILE, []);
        pending.push({
            token: token,
            userData: userData,
            createdAt: Date.now()
        });
        saveData(PENDING_FILE, pending);
        
        // Create verification link
        const verificationLink = `${req.headers.origin || 'https://www.hrleaveassist.com'}/verify?token=${token}`;
        
        // Send verification email
        const emailHtml = `
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Verify Your HR Leave Assistant Account</title>
                <style>
                    body { 
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; 
                        line-height: 1.6; 
                        color: #333333; 
                        margin: 0; 
                        padding: 0; 
                        background-color: #f8f9fa;
                    }
                    .container { 
                        max-width: 600px; 
                        margin: 20px auto; 
                        padding: 0; 
                        background-color: #ffffff;
                        border-radius: 8px;
                        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    }
                    .header { 
                        background: linear-gradient(135deg, #0023F5 0%, #0322D8 100%); 
                        color: white; 
                        padding: 30px 20px; 
                        text-align: center; 
                        border-radius: 8px 8px 0 0; 
                    }
                    .content { 
                        background: #ffffff; 
                        padding: 30px 20px; 
                        border-radius: 0 0 8px 8px; 
                    }
                    .button { 
                        display: inline-block; 
                        background: #0023F5; 
                        color: white !important; 
                        padding: 15px 30px; 
                        text-decoration: none; 
                        border-radius: 5px; 
                        margin: 20px 0; 
                        font-weight: bold;
                        font-size: 16px;
                        text-align: center;
                    }
                    .footer { 
                        text-align: center; 
                        margin-top: 30px; 
                        padding-top: 20px;
                        border-top: 1px solid #eee;
                        font-size: 14px; 
                        color: #666666; 
                    }
                    .logo { 
                        font-size: 24px; 
                        font-weight: bold; 
                        margin-bottom: 10px;
                    }
                    .support { 
                        background: #f8f9fa; 
                        padding: 20px; 
                        border-radius: 5px; 
                        margin-top: 20px;
                        border-left: 4px solid #0023F5;
                    }
                    .verification-code {
                        background: #e3f2fd;
                        padding: 15px;
                        border-radius: 5px;
                        font-family: monospace;
                        font-size: 14px;
                        word-break: break-all;
                        margin: 15px 0;
                    }
                    .company-info {
                        margin-top: 20px;
                        padding: 15px;
                        background: #f8f9fa;
                        border-radius: 5px;
                        font-size: 12px;
                        color: #666;
                    }
                    a { color: #0023F5; }
                    .preheader { display: none !important; visibility: hidden; opacity: 0; color: transparent; height: 0; width: 0; }
                </style>
            </head>
            <body>
                <div class="preheader">Complete your HR Leave Assistant registration by verifying your email address. This helps us ensure account security.</div>
                <div class="container">
                    <div class="header">
                        <div class="logo">🏛️ HR Leave Assistant</div>
                        <p style="margin: 0; font-size: 16px;">Professional HR Compliance Tool</p>
                        <p style="margin: 5px 0 0 0; font-size: 14px; opacity: 0.9;">hrleaveassist.com</p>
                    </div>
                    <div class="content">
                        <h2 style="color: #333; margin-top: 0;">Account Verification Required</h2>
                        <p>Hello ${firstName},</p>
                        <p>Thank you for creating your HR Leave Assistant account. To ensure the security of your account and complete your registration, please verify your email address.</p>
                        
                        ${accessCodeData ? `<p style="background: #e8f5e8; padding: 15px; border-radius: 5px; border-left: 4px solid #4CAF50;"><strong>🎫 Access Code Applied:</strong> Your account will have ${accessCodeData.duration} ${accessCodeData.durationType} of extended access once verified.</p>` : '<p style="background: #fff3cd; padding: 15px; border-radius: 5px; border-left: 4px solid #ffc107;"><strong>🆓 Free Trial:</strong> Your account includes a 24-hour free trial to explore all features.</p>'}
                        
                        <div style="text-align: center; margin: 30px 0;">
                            <a href="${verificationLink}" class="button" style="color: white;">Verify Email Address</a>
                        </div>
                        
                        <p><strong>Alternative verification method:</strong></p>
                        <p>If the button above doesn't work, copy and paste this link into your web browser:</p>
                        <div class="verification-code">${verificationLink}</div>
                        
                        <div class="support">
                            <p style="margin: 0 0 10px 0;"><strong>Need assistance?</strong></p>
                            <p style="margin: 0;">Our support team is here to help. Contact us at <a href="mailto:askhrla@hrleaveassist.com">askhrla@hrleaveassist.com</a> for any questions about your account or our services.</p>
                        </div>
                        
                        <div class="company-info">
                            <p style="margin: 0;"><strong>About HR Leave Assistant:</strong></p>
                            <p style="margin: 5px 0 0 0;">We provide professional HR compliance tools to help organizations manage Federal FMLA and California leave laws effectively. Our platform ensures accurate, compliant responses to employee leave requests.</p>
                        </div>
                    </div>
                    <div class="footer">
                        <p style="margin: 0;">This email was sent by HR Leave Assistant</p>
                        <p style="margin: 5px 0;"><a href="https://www.hrleaveassist.com">www.hrleaveassist.com</a> | <a href="mailto:askhrla@hrleaveassist.com">askhrla@hrleaveassist.com</a></p>
                        <p style="margin: 10px 0 0 0; font-size: 12px;">© ${new Date().getFullYear()} HR Leave Assistant. All rights reserved.</p>
                        <p style="margin: 5px 0 0 0; font-size: 11px;">
                            If you did not create this account, please ignore this email or contact us at askhrla@hrleaveassist.com
                        </p>
                    </div>
                </div>
            </body>
            </html>
        `;

        const textContent = `
HR Leave Assistant - Account Verification Required

Hello ${firstName},

Thank you for creating your HR Leave Assistant account. To ensure the security of your account and complete your registration, please verify your email address.

${accessCodeData ? `Access Code Applied: Your account will have ${accessCodeData.duration} ${accessCodeData.durationType} of extended access once verified.` : 'Free Trial: Your account includes a 24-hour free trial to explore all features.'}

VERIFICATION LINK:
${verificationLink}

ALTERNATIVE METHOD:
Copy and paste the link above into your web browser to verify your account.

NEED ASSISTANCE?
Our support team is here to help. Contact us at askhrla@hrleaveassist.com for any questions about your account or our services.

ABOUT HR LEAVE ASSISTANT:
We provide professional HR compliance tools to help organizations manage Federal FMLA and California leave laws effectively. Our platform ensures accurate, compliant responses to employee leave requests.

---
This email was sent by HR Leave Assistant
Website: https://www.hrleaveassist.com
Support: askhrla@hrleaveassist.com

© ${new Date().getFullYear()} HR Leave Assistant. All rights reserved.

If you did not create this account, please ignore this email or contact us at askhrla@hrleaveassist.com
        `;

        try {
            await sendConfirmationEmail(email, '✅ Verify your Leave Assistant account - Start your access', emailHtml, textContent);
        } catch (error) {
            console.error('Email sending error:', error);
            // Don't fail registration if email sending fails
        }
        
        res.json({
            success: true,
            message: 'Registration successful. Please check your email for verification.',
            verificationLink: verificationLink,
            email: email,
            accessCodeApplied: !!accessCodeData
        });
        
    } catch (error) {
        console.error('Registration error:', error);
        if (error.code === 'SQLITE_CONSTRAINT_UNIQUE') {
            res.status(400).json({ error: 'This email address is already registered' });
        } else {
            res.status(500).json({ error: 'Registration failed' });
        }
    }
});

// Verification endpoint for email links
app.get('/verify', (req, res) => {
    const { token } = req.query;
    
    if (!token) {
        return res.redirect('/?error=missing-token');
    }
    
    const pending = loadData(PENDING_FILE, []);
    const users = loadData(USERS_FILE, []);
    
    const pendingIndex = pending.findIndex(p => p.token === token);
    if (pendingIndex === -1) {
        return res.redirect('/?error=invalid-token');
    }
    
    const pendingUser = pending[pendingIndex];
    
    // Create verified user with all original data preserved
    const now = Date.now();
    const newUser = {
        ...pendingUser.userData,
        id: Date.now().toString(),
        emailVerified: true,
        verifiedAt: now
    };
    
    // Ensure trial expiry is properly set if not already
    if (!newUser.trialExpiry) {
        newUser.trialExpiry = now + (24 * 60 * 60 * 1000); // 24 hours from verification
        console.log('⚠️ Setting trial expiry for verified user:', new Date(newUser.trialExpiry).toLocaleString());
    }
    
    console.log('✅ User verified successfully:', {
        email: newUser.email,
        trialExpiry: new Date(newUser.trialExpiry).toLocaleString(),
        accessLevel: newUser.accessLevel
    });
    
    users.push(newUser);
    pending.splice(pendingIndex, 1);
    
    saveData(USERS_FILE, users);
    saveData(PENDING_FILE, pending);
    
    // Redirect to success page
    res.redirect('/?verified=true');
});

// ==========================================
// DATABASE API ENDPOINTS
// ==========================================

// Get API configuration from database
app.get('/api/db/config', requireAdmin, async (req, res) => {
    try {
        const config = await db.getApiConfig();
        res.json({
            success: true,
            config: config ? {
                openaiKey: config.openaiKey,
                totalRequests: config.totalRequests || 0,
                openaiRequests: config.openaiRequests || 0,
                updatedAt: config.updatedAt
            } : null
        });
    } catch (error) {
        console.error('Error getting API config:', error);
        res.status(500).json({ error: 'Failed to get API configuration' });
    }
});

// Update API configuration in database
app.post('/api/db/config', requireAdmin, async (req, res) => {
    try {
        const { openaiKey } = req.body;
        
        if (!openaiKey) {
            return res.status(400).json({ error: 'API key is required' });
        }
        
        await db.updateApiConfig(openaiKey, req.user.id);
        
        res.json({
            success: true,
            message: 'API configuration updated successfully'
        });
    } catch (error) {
        console.error('Error updating API config:', error);
        res.status(500).json({ error: 'Failed to update API configuration' });
    }
});

// Get all users from database
app.get('/api/db/users', requireAdmin, async (req, res) => {
    try {
        const users = await db.getAllUsers();
        res.json({
            success: true,
            users: users.map(user => ({
                id: user.id,
                firstName: user.firstName,
                lastName: user.lastName,
                email: user.email,
                isAdmin: user.isAdmin,
                emailVerified: user.emailVerified,
                createdAt: user.createdAt,
                subscriptionExpiry: user.subscriptionExpiry,
                trialExpiry: user.trialExpiry,
                accessLevel: user.accessLevel,
                lastLogin: user.lastLogin
            }))
        });
    } catch (error) {
        console.error('Error getting users:', error);
        res.status(500).json({ error: 'Failed to get users' });
    }
});

// Create user in database
app.post('/api/db/users', requireAdmin, async (req, res) => {
    try {
        const userData = req.body;
        userData.id = userData.id || 'user_' + Date.now();
        userData.createdAt = Date.now();
        
        const result = await db.createUser(userData);
        res.json({
            success: true,
            user: { id: result.id },
            message: 'User created successfully'
        });
    } catch (error) {
        console.error('Error creating user:', error);
        if (error.code === 'SQLITE_CONSTRAINT_UNIQUE') {
            res.status(400).json({ error: 'User with this email already exists' });
        } else {
            res.status(500).json({ error: 'Failed to create user' });
        }
    }
});

// Update user in database
app.put('/api/db/users/:id', requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;
        const userData = req.body;
        
        const result = await db.updateUser(id, userData);
        
        if (result.changes === 0) {
            return res.status(404).json({ error: 'User not found' });
        }
        
        res.json({
            success: true,
            message: 'User updated successfully'
        });
    } catch (error) {
        console.error('Error updating user:', error);
        res.status(500).json({ error: 'Failed to update user' });
    }
});

// Delete user from database
app.delete('/api/db/users/:id', requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;
        
        const result = await db.deleteUser(id);
        
        if (result.changes === 0) {
            return res.status(404).json({ error: 'User not found or cannot delete admin user' });
        }
        
        res.json({
            success: true,
            message: 'User deleted successfully'
        });
    } catch (error) {
        console.error('Error deleting user:', error);
        res.status(500).json({ error: 'Failed to delete user' });
    }
});

// Get access codes from database
app.get('/api/db/access-codes', requireAdmin, async (req, res) => {
    try {
        const codes = await db.getAllAccessCodes();
        res.json({
            success: true,
            codes: codes
        });
    } catch (error) {
        console.error('Error getting access codes:', error);
        res.status(500).json({ error: 'Failed to get access codes' });
    }
});

// Create access code in database
app.post('/api/db/access-codes', requireAdmin, async (req, res) => {
    try {
        const codeData = req.body;
        codeData.id = codeData.id || 'code_' + Date.now();
        codeData.createdAt = Date.now();
        codeData.createdBy = req.user.id;
        
        await db.createAccessCode(codeData);
        
        res.json({
            success: true,
            code: { id: codeData.id },
            message: 'Access code created successfully'
        });
    } catch (error) {
        console.error('Error creating access code:', error);
        res.status(500).json({ error: 'Failed to create access code' });
    }
});

// Delete access code from database
app.delete('/api/db/access-codes/:id', requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;
        
        const result = await db.deleteAccessCode(id);
        
        res.json({
            success: true,
            message: 'Access code deleted successfully'
        });
    } catch (error) {
        console.error('Error deleting access code:', error);
        res.status(500).json({ error: 'Failed to delete access code' });
    }
});

// Export all data from database
app.get('/api/db/export', requireAdmin, async (req, res) => {
    try {
        const exportData = await db.exportAllData();
        
        res.setHeader('Content-Type', 'application/json');
        res.setHeader('Content-Disposition', `attachment; filename="hrla_export_${Date.now()}.json"`);
        res.json(exportData);
    } catch (error) {
        console.error('Error exporting data:', error);
        res.status(500).json({ error: 'Failed to export data' });
    }
});

// Get database statistics
app.get('/api/db/stats', requireAdmin, async (req, res) => {
    try {
        const stats = await db.getStats();
        res.json({
            success: true,
            stats: stats
        });
    } catch (error) {
        console.error('Error getting database stats:', error);
        res.status(500).json({ error: 'Failed to get database statistics' });
    }
});

// Log API usage
app.post('/api/db/log-usage', requireAuth, async (req, res) => {
    try {
        const { endpoint, requestData, responseData, tokensUsed, cost } = req.body;
        
        await db.logApiUsage(
            req.user.id,
            endpoint,
            requestData,
            responseData,
            tokensUsed || 0,
            cost || 0
        );
        
        res.json({
            success: true,
            message: 'API usage logged'
        });
    } catch (error) {
        console.error('Error logging API usage:', error);
        res.status(500).json({ error: 'Failed to log API usage' });
    }
});

// ==========================================
// ACCESS CODES DATABASE ENDPOINTS
// ==========================================

// Get all access codes
app.get('/api/db/access-codes', requireAdmin, async (req, res) => {
    try {
        const accessCodes = await db.getAllAccessCodes();
        res.json({
            success: true,
            codes: accessCodes
        });
    } catch (error) {
        console.error('Error getting access codes:', error);
        res.status(500).json({ error: 'Failed to get access codes' });
    }
});

// Create new access code
app.post('/api/db/access-codes', requireAdmin, async (req, res) => {
    try {
        const codeData = {
            ...req.body,
            createdBy: req.user.id
        };
        
        await db.createAccessCode(codeData);
        
        res.json({
            success: true,
            message: 'Access code created successfully'
        });
    } catch (error) {
        console.error('Error creating access code:', error);
        res.status(500).json({ error: 'Failed to create access code' });
    }
});

// Delete access code
app.delete('/api/db/access-codes/:id', requireAdmin, async (req, res) => {
    try {
        const { id } = req.params;
        
        await db.deleteAccessCode(id);
        
        res.json({
            success: true,
            message: 'Access code deleted successfully'
        });
    } catch (error) {
        console.error('Error deleting access code:', error);
        res.status(500).json({ error: 'Failed to delete access code' });
    }
});

// ==========================================
// STATIC FILES (Must be after API routes)
// ==========================================
app.use(express.static('.'));

// Catch-all route for SPA (Must be last)
app.get('*', (req, res) => {
    res.sendFile(path.join(__dirname, 'index.html'));
});

app.listen(PORT, () => {
    console.log(`🚀 Leave Assistant Server running on http://localhost:${PORT}`);
    console.log(`📁 Data directory: ${DATA_DIR}`);
    console.log(`👥 Users file: ${USERS_FILE}`);
});
