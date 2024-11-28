<?php
// Enable strict error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0); // Disable display of errors in production
ini_set('log_errors', 1);
ini_set('error_log', '/path/to/your/error.log'); // Specify error log path

// Security headers
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' \'unsafe-eval\'');

// Start secure session
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1); // Use only over HTTPS

session_start();

// CSRF Protection
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// Rate Limiting
function checkRateLimit($ip) {
    $max_attempts = 5;
    $lockout_time = 15 * 60; // 15 minutes
    
    $cache_file = "/path/to/rate_limit_cache/{$ip}.json";
    
    // Create cache directory if not exists
    if (!is_dir(dirname($cache_file))) {
        mkdir(dirname($cache_file), 0755, true);
    }
    
    // Initialize or read existing attempts
    if (file_exists($cache_file)) {
        $attempts_data = json_decode(file_get_contents($cache_file), true);
    } else {
        $attempts_data = [
            'attempts' => 0,
            'last_attempt' => time()
        ];
    }
    
    // Check if lockout period is active
    if ((time() - $attempts_data['last_attempt']) < $lockout_time) {
        if ($attempts_data['attempts'] >= $max_attempts) {
            return false; // Locked out
        }
    } else {
        // Reset attempts if lockout period has passed
        $attempts_data = [
            'attempts' => 0,
            'last_attempt' => time()
        ];
    }
    
    // Increment attempts
    $attempts_data['attempts']++;
    $attempts_data['last_attempt'] = time();
    
    // Save attempts
    file_put_contents($cache_file, json_encode($attempts_data));
    
    return true;
}

// Enhanced Validation Function
function validate($data) {
    // Trim whitespace
    $data = trim($data);
    
    // Remove backslashes
    $data = stripslashes($data);
    
    // Convert special characters to HTML entities
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    
    // Additional sanitization
    $data = filter_var($data, FILTER_SANITIZE_STRING);
    
    return $data;
}

// Logging Function
function securityLog($message, $level = 'INFO') {
    $log_file = '/path/to/security.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'];
    
    $log_message = "[{$timestamp}] [{$level}] [{$ip}] {$message}\n";
    
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

// Main Login Process
include "db_connect.php";

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: LogScreen.php");
    exit();
}

// CSRF Token Validation
if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
    securityLog('CSRF Token Validation Failed', 'SECURITY');
    header("Location: LogScreen.php?error=Invalid Request");
    exit();
}

// Rate Limiting
$client_ip = $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit($client_ip)) {
    securityLog('Rate Limit Exceeded', 'SECURITY');
    header("Location: LogScreen.php?error=Too Many Attempts. Please try again later.");
    exit();
}

// Input Validation
$usernameOrEmail = validate($_POST['username']);
$password = $_POST['password']; // Do not sanitize password

if (empty($usernameOrEmail)) {
    securityLog('Empty Username Attempt', 'WARNING');
    header("Location: LogScreen.php?error=Username required");
    exit();
}

if (empty($password)) {
    securityLog('Empty Password Attempt', 'WARNING');
    header("Location: LogScreen.php?error=Password required");
    exit();
}

// Prepared Statement with Enhanced Security
try {
    // Use password hashing for comparison
    $sql = "SELECT * FROM tgebruiker 
            WHERE (user_name = ? OR email = ?)";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        throw new Exception("Prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ss", $usernameOrEmail, $usernameOrEmail);
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        
        // Verify password using password_verify (recommended)
        if (password_verify($password, $row['password'])) {
            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            
            // Set secure session variables
            $_SESSION['user_name'] = $row['user_name'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
            $_SESSION['role'] = $row['role'];
            
            // Set last login time
            $update_sql = "UPDATE tgebruiker SET last_login = NOW() WHERE gebruiker_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $row['gebruiker_id']);
            $update_stmt->execute();
            
            // Log successful login
            securityLog("Successful Login for {$usernameOrEmail}", 'INFO');
            
            // Redirect based on role
            switch ($row['role']) {
                case 'admin':
                    header("Location: ../admin/admin-dashboard.php");
                    break;
                case 'student':
                    header("Location: ../student/student-dashboard.php");
                    break;
                case 'docent':
                    header("Location: ../docent/docent-dashboard.php");
                    break;
                default:
                    header("Location: LogScreen.php?error=Invalid user role");
            }
            exit();
        } else {
            // Log failed password attempt
            securityLog("Failed Login Attempt for {$usernameOrEmail}", 'WARNING');
            header("Location: LogScreen.php?error=Invalid Credentials");
            exit();
        }
    } else {
        // Log user not found
        securityLog("Login Attempt for Non-Existent User {$usernameOrEmail}", 'WARNING');
        header("Location: LogScreen.php?error=Invalid Credentials");
        exit();
    }
} catch (Exception $e) {
    // Log any unexpected errors
    securityLog("Login Error: " . $e-> getMessage(), 'ERROR');
    header("Location: LogScreen.php?error=An unexpected error occurred. Please try again later.");
    exit();
} finally {
    // Close statement
    if (isset($stmt) && $stmt) {
        $stmt->close();
    }
    // Close database connection
    $conn->close();
}