<?php
/**
 * AI Assistant API Endpoint
 * HR Leave Assistant - PHP/MySQL Version
 */

require_once __DIR__ . '/../config/app.php';
require_once __DIR__ . '/../includes/auth.php';

// Set JSON response headers
header('Content-Type: application/json');
header('Cache-Control: no-cache, must-revalidate');

// CORS headers for development
if (!config('app.is_production')) {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
}

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    // Check authentication
    $auth = getAuth();
    if (!$auth->isAuthenticated()) {
        http_response_code(401);
        echo json_encode(['error' => 'Authentication required']);
        exit;
    }
    
    // Check access
    if (!$auth->hasAccess()) {
        http_response_code(403);
        echo json_encode(['error' => 'Active subscription required']);
        exit;
    }
    
    // Get request data
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid JSON input']);
        exit;
    }
    
    // Validate required fields
    $toolName = $input['tool_name'] ?? '';
    $inputText = trim($input['input_text'] ?? '');
    
    if (!in_array($toolName, ['federal', 'california'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid tool name']);
        exit;
    }
    
    if (empty($inputText)) {
        http_response_code(400);
        echo json_encode(['error' => 'Input text is required']);
        exit;
    }
    
    if (strlen($inputText) > 10000) {
        http_response_code(400);
        echo json_encode(['error' => 'Input text is too long (max 10,000 characters)']);
        exit;
    }
    
    // Get OpenAI API key
    $db = getDB();
    $apiConfig = $db->fetch("SELECT openai_key FROM api_config WHERE is_active = 1 ORDER BY id DESC LIMIT 1");
    
    if (!$apiConfig || empty($apiConfig['openai_key'])) {
        http_response_code(503);
        echo json_encode(['error' => 'AI service is not configured']);
        exit;
    }
    
    // Generate AI response
    $aiResponse = generateAIResponse($toolName, $inputText, $apiConfig['openai_key']);
    
    if (!$aiResponse['success']) {
        http_response_code(500);
        echo json_encode(['error' => $aiResponse['error']]);
        exit;
    }
    
    // Save conversation to database
    $user = $auth->getCurrentUser();
    saveConversation($user['id'], $toolName, $inputText, $aiResponse['response'], $aiResponse['tokens_used']);
    
    // Update API usage statistics
    updateApiUsage($aiResponse['tokens_used']);
    
    // Return successful response
    echo json_encode([
        'success' => true,
        'response' => $aiResponse['response'],
        'tokens_used' => $aiResponse['tokens_used']
    ]);
    
} catch (Exception $e) {
    logMessage("AI API error: " . $e->getMessage(), 'error', [
        'user_id' => $auth->getCurrentUser()['id'] ?? null,
        'tool_name' => $toolName ?? null,
        'input_length' => strlen($inputText ?? '')
    ]);
    
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

/**
 * Generate AI response using OpenAI API
 */
function generateAIResponse($toolName, $inputText, $apiKey) {
    try {
        // Prepare system prompt based on tool
        $systemPrompt = getSystemPrompt($toolName);
        
        // Prepare the request
        $data = [
            'model' => config('api.openai_model'),
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $inputText
                ]
            ],
            'max_tokens' => config('api.openai_max_tokens'),
            'temperature' => config('api.openai_temperature'),
            'top_p' => 1,
            'frequency_penalty' => 0,
            'presence_penalty' => 0
        ];
        
        // Make API request
        $ch = curl_init();
        
        // SSL options - disable verification for local development only
        $isProduction = config('app.is_production');
        $sslVerify = $isProduction;
        
        curl_setopt_array($ch, [
            CURLOPT_URL => 'https://api.openai.com/v1/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey
            ],
            CURLOPT_TIMEOUT => 60,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => $sslVerify,
            CURLOPT_SSL_VERIFYHOST => $sslVerify ? 2 : 0,
            CURLOPT_USERAGENT => 'HR Leave Assistant/2.0'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: $error");
        }
        
        if ($httpCode !== 200) {
            $errorData = json_decode($response, true);
            $errorMessage = $errorData['error']['message'] ?? "HTTP $httpCode";
            throw new Exception("OpenAI API error: $errorMessage");
        }
        
        $responseData = json_decode($response, true);
        
        if (!$responseData || !isset($responseData['choices'][0]['message']['content'])) {
            throw new Exception("Invalid response from OpenAI API");
        }
        
        $content = trim($responseData['choices'][0]['message']['content']);
        $tokensUsed = $responseData['usage']['total_tokens'] ?? 0;
        
        return [
            'success' => true,
            'response' => $content,
            'tokens_used' => $tokensUsed
        ];
        
    } catch (Exception $e) {
        logMessage("OpenAI API error: " . $e->getMessage(), 'error');
        
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

/**
 * Get system prompt for specific tool
 */
function getSystemPrompt($toolName) {
    $db = getDB();
    
    // Get custom instructions if they exist and are active
    $customInstructions = '';
    $aiInstructions = $db->fetch("SELECT custom_instructions FROM ai_instructions WHERE tool_name = ? AND is_active = 1", [$toolName]);
    if ($aiInstructions && !empty($aiInstructions['custom_instructions'])) {
        $customInstructions = "\n\nADDITIONAL CUSTOM INSTRUCTIONS:\n" . $aiInstructions['custom_instructions'];
    }
    
    $basePrompt = "You are an expert HR professional specializing in leave law compliance. Your role is to generate professional, accurate, and compliant responses to employee leave requests. Always provide specific guidance based on current regulations and best practices.";
    
    switch ($toolName) {
        case 'federal':
            return $basePrompt . "

FOCUS: Federal Family and Medical Leave Act (FMLA) compliance

KEY RESPONSIBILITIES:
- Analyze employee leave requests for FMLA eligibility
- Provide accurate guidance on federal FMLA requirements
- Generate professional HR responses that ensure compliance
- Address eligibility, entitlements, notice requirements, and documentation

FMLA ELIGIBILITY REQUIREMENTS:
- Employee must work for covered employer (50+ employees within 75 miles)
- Employee must have worked for employer for at least 12 months
- Employee must have worked at least 1,250 hours in the 12 months before leave
- Leave must be for qualifying reason (birth/adoption, serious health condition, military family leave)

RESPONSE FORMAT:
1. Professional greeting and acknowledgment
2. Eligibility determination with specific reasoning
3. Leave entitlement details (duration, pay status, benefits)
4. Required documentation and deadlines
5. Next steps and contact information
6. Professional closing

Always cite specific FMLA provisions and maintain a supportive, professional tone while ensuring legal compliance." . $customInstructions;

        case 'california':
            return $basePrompt . "

FOCUS: California leave laws including CFRA, PDL, and coordination with federal FMLA

KEY RESPONSIBILITIES:
- Analyze requests under California Family Rights Act (CFRA)
- Address Pregnancy Disability Leave (PDL) requirements
- Coordinate California and federal leave entitlements
- Provide guidance on California-specific benefits (SDI, PFL)

CALIFORNIA LEAVE LAW DIFFERENCES:
- CFRA applies to employers with 5+ employees (vs 50 for FMLA)
- Broader definition of family members under CFRA
- PDL is separate from and in addition to CFRA/FMLA
- Different notice and certification requirements
- State disability insurance and paid family leave benefits

COORDINATION RULES:
- CFRA and FMLA may run concurrently when both apply
- PDL is separate and does not count against CFRA/FMLA entitlement
- Employee receives most favorable benefits under applicable laws
- Careful analysis required for complex situations

RESPONSE FORMAT:
1. Professional greeting and acknowledgment
2. Analysis of applicable California laws (CFRA, PDL, etc.)
3. Coordination with federal FMLA if applicable
4. Leave entitlement details and state benefits
5. Required documentation and California-specific requirements
6. Next steps and resources
7. Professional closing

Always address California-specific requirements and benefits while coordinating with federal obligations." . $customInstructions;

        default:
            return $basePrompt . $customInstructions;
    }
}

/**
 * Save conversation to database
 */
function saveConversation($userId, $toolName, $inputText, $responseText, $tokensUsed) {
    try {
        $db = getDB();
        
        $sql = "INSERT INTO conversations (user_id, tool_name, input_text, response_text, tokens_used, cost) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        // Calculate approximate cost (rough estimate)
        $cost = ($tokensUsed / 1000) * 0.002; // $0.002 per 1K tokens (approximate)
        
        $db->query($sql, [
            $userId,
            $toolName,
            $inputText,
            $responseText,
            $tokensUsed,
            $cost
        ]);
        
    } catch (Exception $e) {
        logMessage("Failed to save conversation: " . $e->getMessage(), 'error');
    }
}

/**
 * Update API usage statistics
 */
function updateApiUsage($tokensUsed) {
    try {
        $db = getDB();
        
        $sql = "UPDATE api_config 
                SET total_requests = total_requests + 1, 
                    openai_requests = openai_requests + 1 
                WHERE is_active = 1";
        
        $db->query($sql);
        
    } catch (Exception $e) {
        logMessage("Failed to update API usage: " . $e->getMessage(), 'error');
    }
}
?>