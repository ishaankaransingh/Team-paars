<?php
session_start();
include "db_connect.php";

// Initialize session variables if they don't exist
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}
if (!isset($_SESSION['lock_until'])) {
    $_SESSION['lock_until'] = null;
}

if (isset($_POST['username']) && isset($_POST['password'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $usernameOrEmail = validate($_POST['username']);
    $password = validate($_POST['password']);

    if (empty($usernameOrEmail)) {
        header("Location: LogScreen.php?error=User Name or Email required");
        exit();
    } else if (empty($password)) {
        header("Location: LogScreen.php?error=Password is required");
        exit();
    } else {
        // Check if the account is currently locked
        if ($_SESSION['lock_until'] !== null && time() < $_SESSION['lock_until']) {
            $remainingTime = $_SESSION['lock_until'] - time();
            header("Location: LogScreen.php?error=Account locked. Try again in " . gmdate("i:s", $remainingTime));
            exit();
        }

        // SQL query to join the user table with the roles table
        $sql = "SELECT t.*, p.rol_id, p.active, r.role 
                FROM tgebruiker t 
                JOIN personen p ON t.persoon_id = p.persoon_id 
                JOIN rollen r ON p.rol_id = r.role_id 
                WHERE (t.email = '$usernameOrEmail')";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);

            if ($row['email'] === $usernameOrEmail) {
                // Verify the password using password_verify
                if (password_verify($password, $row['password'])) {
                    if ($row['active'] !== NULL) {
                        // Reset failed attempts on successful login
                        $_SESSION['failed_attempts'] = 0;
                        $_SESSION['lock_until'] = null;

                        $_SESSION['user_name'] = $row['email'];
                        $_SESSION['name'] = $row['naam'];
                        $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
                        $_SESSION['role'] = $row['role'];
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
                            case 'od':
                                header("Location: ../od/od-dashboard.php");
                                break;
                            case 'rc':
                                header("Location: ../rc/rc-dashboard.php");
                                break;
                            case 'directeur':
                                header("Location: ../directeur/directeur-dashboard.php");
                                break;
                            case 'systeembeheerder':
                                header("Location: ../systeembeheer/systeembeheer-dashboard.php");
                                break;
                        }
                        exit();
                    } else {
                        header("Location: LogScreen.php?error=Account is deactivated");
                        exit();
                    }
                } else {
                    // Increment failed attempts
                    $_SESSION['failed_attempts']++;

                    // Lock account after 3 failed attempts
                    if ($_SESSION['failed_attempts'] >= 3) {
                        $_SESSION['lock_until'] = time() + 60; // Lock for 1 minute
                        header("Location: LogScreen.php?error=Account locked. Try again in 1 minute.");
                        exit();
                    } else {
                        header("Location: LogScreen.php?error=Incorrect email or Password");
                        exit();
                    }
                }
            } else {
                header("Location: LogScreen.php?error=Incorrect email or Password");
                exit();
            }
        } else {
            header("Location: LogScreen.php?error=Incorrect email or Password");
            exit();
        }
    }
} else {
    header("Location: LogScreen.php");
    exit();
}