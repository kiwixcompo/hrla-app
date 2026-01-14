// Test script to verify all fixes are working
const https = require('https');
const http = require('http');

const BASE_URL = 'http://localhost:3001';

function makeRequest(url, options = {}) {
    return new Promise((resolve, reject) => {
        const urlObj = new URL(url);
        const requestOptions = {
            hostname: urlObj.hostname,
            port: urlObj.port,
            path: urlObj.pathname,
            method: options.method || 'GET',
            headers: options.headers || {}
        };

        const req = http.request(requestOptions, (res) => {
            let data = '';
            res.on('data', (chunk) => {
                data += chunk;
            });
            res.on('end', () => {
                try {
                    const jsonData = JSON.parse(data);
                    resolve(jsonData);
                } catch (error) {
                    resolve({ error: 'Invalid JSON response', data });
                }
            });
        });

        req.on('error', (error) => {
            reject(error);
        });

        if (options.body) {
            req.write(options.body);
        }

        req.end();
    });
}

async function testFixes() {
    console.log('🧪 Testing all fixes...\n');
    
    try {
        // Test 1: Admin Login
        console.log('1️⃣ Testing admin login...');
        const loginData = await makeRequest(`${BASE_URL}/api/auth/login`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: 'talk2char@gmail.com',
                password: 'Password@123'
            })
        });
        if (loginData.success) {
            console.log('✅ Admin login successful');
            const token = loginData.sessionToken;
            
            // Test 2: Create Access Code
            console.log('\n2️⃣ Testing access code creation...');
            const accessCodeData = await makeRequest(`${BASE_URL}/api/db/access-codes`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    id: 'test_fix_' + Date.now(),
                    code: 'FIX123',
                    duration: 30,
                    durationType: 'days',
                    description: 'Test fix verification',
                    createdAt: Date.now()
                })
            });
            if (accessCodeData.success) {
                console.log('✅ Access code creation successful');
                
                // Test 3: Validate Access Code
                console.log('\n3️⃣ Testing access code validation...');
                const validateResponse = await fetch(`${BASE_URL}/api/validate-access-code`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ code: 'FIX123' })
                });
                
                const validateData = await validateResponse.json();
                if (validateData.success) {
                    console.log('✅ Access code validation successful');
                    
                    // Test 4: Register with Access Code
                    console.log('\n4️⃣ Testing registration with access code...');
                    const registerResponse = await fetch(`${BASE_URL}/api/auth/register`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            email: 'testfix@example.com',
                            firstName: 'Test',
                            lastName: 'Fix',
                            password: 'password123',
                            accessCode: 'FIX123'
                        })
                    });
                    
                    const registerData = await registerResponse.json();
                    if (registerData.success) {
                        console.log('✅ Registration with access code successful');
                    } else {
                        console.log('❌ Registration failed:', registerData.error);
                    }
                } else {
                    console.log('❌ Access code validation failed:', validateData.error);
                }
            } else {
                console.log('❌ Access code creation failed:', accessCodeData.error);
            }
            
            // Test 5: API Configuration
            console.log('\n5️⃣ Testing API configuration...');
            const apiConfigResponse = await fetch(`${BASE_URL}/api/db/config`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify({
                    openaiKey: 'sk-test-fix-verification-key'
                })
            });
            
            const apiConfigData = await apiConfigResponse.json();
            if (apiConfigData.success) {
                console.log('✅ API configuration save successful');
                
                // Test 6: Retrieve API Configuration
                console.log('\n6️⃣ Testing API configuration retrieval...');
                const getConfigResponse = await fetch(`${BASE_URL}/api/db/config`, {
                    method: 'GET',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });
                
                const getConfigData = await getConfigResponse.json();
                if (getConfigData.success && getConfigData.config) {
                    console.log('✅ API configuration retrieval successful');
                    console.log('   API Key present:', !!getConfigData.config.openaiKey);
                } else {
                    console.log('❌ API configuration retrieval failed');
                }
            } else {
                console.log('❌ API configuration save failed:', apiConfigData.error);
            }
            
        } else {
            console.log('❌ Admin login failed:', loginData.error);
        }
        
        console.log('\n🎉 Fix verification complete!');
        
    } catch (error) {
        console.error('❌ Test error:', error.message);
    }
}

testFixes();