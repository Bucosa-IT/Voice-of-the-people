<?php
/**
 * Configuration & Utilities
 * System configuration and helper functions
 */

// System Configuration
define('SYSTEM_NAME', 'Voice of the People - BUCOSA');
define('VERSION', '1.0.0');
define('DATA_DIR', __DIR__ . '/data');
define('LOG_DIR', __DIR__ . '/logs');

// Security
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('SESSION_TIMEOUT', 3600); // 1 hour

// Voting Configuration
define('ALLOW_BATCH_VOTING', true);
define('STUDENT_BATCHES', ['2024', '2023', '2022', '2021']);
define('VOTING_POSITIONS', [
    'School President',
    'School Vice President',
    'School Secretary',
    'School Treasurer',
    'Class Representative'
]);

/**
 * Logging utility
 */
class Logger {
    private static $logFile = LOG_DIR . '/system.log';
    
    public static function ensureLogDirectory() {
        if (!is_dir(LOG_DIR)) {
            mkdir(LOG_DIR, 0755, true);
        }
    }
    
    public static function log($level, $message, $data = null) {
        self::ensureLogDirectory();
        
        $timestamp = date('Y-m-d H:i:s');
        $logEntry = "[$timestamp] [$level] $message";
        
        if ($data) {
            $logEntry .= "\n" . json_encode($data, JSON_PRETTY_PRINT);
        }
        
        $logEntry .= "\n" . str_repeat('-', 80) . "\n";
        
        file_put_contents(self::$logFile, $logEntry, FILE_APPEND);
    }
}

/**
 * Response formatter
 */
class ResponseFormatter {
    public static function success($message, $data = null, $code = 200) {
        http_response_code($code);
        return [
            'status' => 'success',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    public static function error($message, $data = null, $code = 400) {
        http_response_code($code);
        return [
            'status' => 'error',
            'message' => $message,
            'data' => $data,
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
    
    public static function json($response) {
        header('Content-Type: application/json');
        return json_encode($response, JSON_PRETTY_PRINT);
    }
}

/**
 * Validation utility
 */
class Validator {
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
    
    public static function validateVoterId($voterId) {
        return !empty($voterId) && strlen($voterId) >= 3;
    }
    
    public static function validateVoterKey($key) {
        return !empty($key) && strlen($key) >= 2;
    }
    
    public static function sanitizeInput($input) {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function validateBatch($batch) {
        return in_array($batch, STUDENT_BATCHES);
    }
}

// Initialize logging
Logger::ensureLogDirectory();
Logger::log('INFO', 'System initialized', ['version' => VERSION]);
?>
