<?php
session_start();
include "db_connect.php";

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
        header("Location: LogScreen.php?error=User  Name or Email required");
        exit();
    } else if (empty($password)) {
        header("Location: LogScreen.php?error=Password is required");
        exit();
    } else {
        // Update the SQL query to check both username and email
        $sql = "SELECT * FROM tgebruiker WHERE (user_name = '$usernameOrEmail' OR email = '$usernameOrEmail') AND password = '$password'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['user_name'] === $usernameOrEmail || $row['email'] === $usernameOrEmail) {
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
                $_SESSION['role'] = $row['role']; // Adding the role to the session

                if ($row['role'] === 'admin') {
                    header("Location: ../admin/admin-dashboard.php"); // Redirect to admin page
                } else if ($row['role'] === 'student') {
                    header("Location: ../student/student-dashboard.php"); // Redirect to student page
                } else if ($row['role'] === 'docent') {
                    header("Location: ../docent/docent-dashboard.php"); // Redirect to docent page
                }

                exit();
            } else {
                header("Location: LogScreen.php?error=Incorrect User Name or Password");// geeft een error 
                exit();
            }
        } else {
            header("Location: LogScreen.php?error=Incorrect User Name or Password");// geeft een inlog error
            exit();
        }
    }
} else {
    header("Location: LogScreen.php");
    exit();
}