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
        //sql query om de gebruikers tabel met de rollen tabel te joinen
        $sql = "SELECT t.*, r.role 
                FROM tgebruiker t 
                JOIN rollen r ON t.role_id = r.role_id 
                WHERE (t.user_name = '$usernameOrEmail' OR t.email = '$usernameOrEmail') 
                AND t.password = '$password'";

        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if ($row['user_name'] === $usernameOrEmail || $row['email'] === $usernameOrEmail) {
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
                $_SESSION['role'] = $row['role']; // Adding the role to the session

                // Redirect based on role
                if ($row['role'] === 'admin') {
                    header("Location: ../admin/admin-dashboard.php"); // ...................Redirect to admin page
                } else if ($row['role'] === 'student') {
                    header("Location: ../student/student-dashboard.php"); // ...............Redirect to student page
                } else if ($row['role'] === 'docent') {
                    header("Location: ../docent/docent-dashboard.php"); // .................Redirect to docent page
                } else if ($row['role'] === 'od') {
                    header("Location: ../od/od-dashboard.php"); // .........................Redirect to od page
                } else if ($row['role'] === 'rc') {
                    header("Location: ../rc/rc-dashboard.php"); // .........................Redirect to rc page
                } else if ($row['role'] === 'directeur') {
                    header("Location: ../directeur/directeur-dashboard.php"); // ...........Redirect to directeur page
                } else if ($row['role'] === 'systeembeheer') {
                    header("Location: ../systeembeheer/systeembeheer-dashboard.php"); // ...Redirect to systeembeheer page
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
    }
} else {
    header("Location: LogScreen.php");
    exit();
}