<?php
/**
 * Error Handler and Logger
 * HR Leave Assistant - PHP/MySQL Version
 */

// Define ROOT_PATH if not already defined
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}

// Define error log file path
define('ERROR_LOG_FILE', ROOT_PATH . '/logs/error.log');

// Ensure logs directory exists
if (!is_dir(ROOT_PATH . '/logs')) {
    @mkdir(ROOT_PATH . '/logs', 0755, true);
}

/**
 * Custom error handler
 */
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    // Don't log suppressed errors (@)
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $errorTypes = [
        E_ERROR => 'ERROR',
        E_WARNING => 'WARNING',
        E_PARSE => 'PARSE',
        E_NOTICE => 'NOTICE',
        E_CORE_ERROR => 'CORE_ERROR',
        E_CORE_WARNING => 'CORE_WARNING',
        E_COMPILE_ERROR => 'COMPILE_ERROR',
        E_COMPILE_WARNING => 'COMPILE_WARNING',
        E_USER_ERROR => 'USER_ERROR',
        E_USER_WARNING => 'USER_WARNING',
        E_USER_NOTICE => 'USER_NOTICE',
        E_STRICT => 'STRICT',
        E_RECOVERABLE_ERROR => 'RECOVERABLE_ERROR',
        E_DEPRECATED => 'DEPRECATED',
        E_USER_DEPRECATED => 'USER_DEPRECATED'
    ];
    
    $errorType = $errorTypes[$errno] ?? 'UNKNOWN';
    
    logError([
        'type' => $errorType,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
    ]);
    
    // Don't execute PHP internal error handler
    return true;
}

/**
 * Custom exception handler
 */
function customExceptionHandler($exception) {
    logError([
        'type' => 'EXCEPTION',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine(),
        'trace' => $exception->getTrace()
    ]);
    
    // Show user-friendly error page
    if (!headers_sent()) {
        http_response_code(500);
    }
    
    if (defined('APP_URL') && strpos(APP_URL, 'localhost') === false) {
        // Production - show generic error
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Error - HR Leave Assistant</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #d32f2f; }
        p { color: #666; }
    </style>
</head>
<body>
    <h1>Something went wrong</h1>
    <p>We\'re sorry, but something went wrong. Please try again later.</p>
    <p><a href="/">Return to homepage</a></p>
</body>
</html>';
    } else {
        // Development - show detailed error
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Error - HR Leave Assistant</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .error-box { background: white; border-left: 4px solid #d32f2f; padding: 20px; margin: 20px 0; }
        h1 { color: #d32f2f; margin: 0 0 10px 0; }
        .file { color: #666; font-size: 14px; }
        .trace { background: #f9f9f9; padding: 10px; margin-top: 10px; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="error-box">
        <h1>Exception: ' . htmlspecialchars($exception->getMessage()) . '</h1>
        <div class="file">
            File: ' . htmlspecialchars($exception->getFile()) . ' (Line ' . $exception->getLine() . ')
        </div>
        <div class="trace">
            <strong>Stack Trace:</strong><br>
            ' . nl2br(htmlspecialchars($exception->getTraceAsString())) . '
        </div>
    </div>
    <p><a href="/">Return to homepage</a></p>
</body>
</html>';
    }
    
    exit;
}

/**
 * Shutdown handler for fatal errors
 */
function shutdownHandler() {
    $error = error_get_last();
    
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        logError([
            'type' => 'FATAL_ERROR',
            'message' => $error['message'],
            'file' => $error['file'],
            'line' => $error['line']
        ]);
        
        // Show user-friendly error page for fatal errors
        if (!headers_sent()) {
            http_response_code(500);
        }
        
        echo '<!DOCTYPE html>
<html>
<head>
    <title>Error - HR Leave Assistant</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 50px; }
        h1 { color: #d32f2f; }
        p { color: #666; }
    </style>
</head>
<body>
    <h1>Fatal Error</h1>
    <p>A critical error occurred. Please check the error log for details.</p>
    <p><a href="/">Return to homepage</a></p>
</body>
</html>';
    }
}

/**
 * Log error to file
 */
function logError($error) {
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $url = $_SERVER['REQUEST_URI'] ?? 'unknown';
    $method = $_SERVER['REQUEST_METHOD'] ?? 'unknown';
    
    $logEntry = [
        'timestamp' => $timestamp,
        'type' => $error['type'] ?? 'UNKNOWN',
        'message' => $error['message'] ?? 'No message',
        'file' => $error['file'] ?? 'unknown',
        'line' => $error['line'] ?? 0,
        'url' => $url,
        'method' => $method,
        'ip' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
    ];
    
    // Add trace if available (limit to 5 levels)
    if (isset($error['trace']) && is_array($error['trace'])) {
        $logEntry['trace'] = array_slice($error['trace'], 0, 5);
    }
    
    // Format log entry
    $logLine = str_repeat('=', 80) . PHP_EOL;
    $logLine .= "[{$timestamp}] {$error['type']}" . PHP_EOL;
    $logLine .= "Message: {$error['message']}" . PHP_EOL;
    $logLine .= "File: {$error['file']} (Line {$error['line']})" . PHP_EOL;
    $logLine .= "URL: {$method} {$url}" . PHP_EOL;
    $logLine .= "IP: {$ip}" . PHP_EOL;
    
    if (isset($logEntry['trace'])) {
        $logLine .= "Stack Trace:" . PHP_EOL;
        foreach ($logEntry['trace'] as $i => $trace) {
            $file = $trace['file'] ?? 'unknown';
            $line = $trace['line'] ?? 0;
            $function = $trace['function'] ?? 'unknown';
            $logLine .= "  #{$i} {$file}({$line}): {$function}()" . PHP_EOL;
        }
    }
    
    $logLine .= PHP_EOL;
    
    // Write to log file (suppress errors if directory not writable)
    @file_put_contents(ERROR_LOG_FILE, $logLine, FILE_APPEND | LOCK_EX);
    
    // Also write JSON format for easier parsing
    $jsonLogFile = ROOT_PATH . '/logs/error.json.log';
    @file_put_contents($jsonLogFile, json_encode($logEntry) . PHP_EOL, FILE_APPEND | LOCK_EX);
}

/**
 * Log custom message
 */
function logCustomError($message, $context = []) {
    logError([
        'type' => 'CUSTOM',
        'message' => $message,
        'file' => $context['file'] ?? 'unknown',
        'line' => $context['line'] ?? 0,
        'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5)
    ]);
}

// Register error handlers
set_error_handler('customErrorHandler');
set_exception_handler('customExceptionHandler');
register_shutdown_function('shutdownHandler');

// Enable error logging even in production
ini_set('log_errors', 1);
ini_set('error_log', ERROR_LOG_FILE);
?>
