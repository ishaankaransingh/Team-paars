<?php
session_start();
include "db_connect.php";

// Add error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (isset($_POST['username']) && isset($_POST['password'])) {
    // Validation function
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    // Sanitize input
    $usernameOrEmail = validate($_POST['username']);
    $password = validate($_POST['password']);

    // Input validation
    if (empty($usernameOrEmail)) {
        header("Location: LogScreen.php?error=User Name or Email required");
        exit();
    } else if (empty($password)) {
        header("Location: LogScreen.php?error=Password is required");
        exit();
    } else {
        // Updated SQL query to match your table structure
        $sql = "SELECT * FROM tgebruiker
                WHERE (user_name = ? OR email = ?) 
                AND password = ?";
        
        // Prepare statement
        $stmt = $conn->prepare($sql);
        
        // Check prepare statement
        if ($stmt === false) {
            $prepare_error = "Prepare Error: " . $conn->error;
            error_log($prepare_error);
            header("Location: LogScreen.php?error=" . urlencode($prepare_error));
            exit();
        }
        
        // Bind parameters
        $bind_result = $stmt->bind_param("sss", $usernameOrEmail, $usernameOrEmail, $password);
        
        // Check bind result
        if ($bind_result === false) {
            $bind_error = "Bind Error: " . $stmt->error;
            error_log($bind_error);
            header("Location: LogScreen.php?error=" . urlencode($bind_error));
            exit();
        }
        
        // Execute the statement
        $execute_result = $stmt->execute();
        
        // Check execution
        if ($execute_result === false) {
            $execute_error = "Execute Error: " . $stmt->error;
            error_log($execute_error);
            header("Location: LogScreen.php?error=" . urlencode($execute_error));
            exit();
        }

        // Get result
        $result = $stmt->get_result();
        
        // Check result
        if ($result === false) {
            $result_error = "Result Error: " . $stmt->error;
            error_log($result_error);
            header("Location: LogScreen.php?error=" . urlencode($result_error));
            exit();
        }

        // Check number of rows
        if ($result->num_rows === 1) {
            // Fetch the user data
            $row = $result->fetch_assoc();
            
            // Verify credentials
            if ($row['user_name'] === $usernameOrEmail || $row['email'] === $usernameOrEmail) {
                // Set session variables
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
                $_SESSION['role'] = $row['role'];

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
                header("Location: LogScreen.php?error=Incorrect User Name or Password");
                exit();
            }
        } else {
            header("Location: LogScreen.php?error=Incorrect User Name or Password");
            exit();
        }

        // Close statement
        $stmt->close();
    }
} else {
    header("Location: LogScreen.php");
    exit();
}