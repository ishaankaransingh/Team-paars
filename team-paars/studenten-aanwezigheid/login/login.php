<?php
session_start();
include "db_connect.php";

if (isset($_POST['username']) && isset($_POST['password'])){
    function validate($data){
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }
    $username = validate ($_POST['username']);
    $password = validate ($_POST['password']);

    if (empty($username)){
        header("Location: LogScreen.php?error=User Name required");
        exit();
    }else if(empty($password)){
        header("Location: LogScreen.php?error=Password is required");
        exit();
    }else{
        $sql = "SELECT * FROM tgebruiker WHERE user_name = '$username' AND password = '$password'";

        $result = mysqli_query($conn, $sql);

        if(mysqli_num_rows($result) === 1){
            $row = mysqli_fetch_assoc($result);
            if ($row ['user_name'] === $username && $row['password'] === $password) {
                $_SESSION['user_name'] = $row['user_name'];
                $_SESSION['name'] = $row['name'];
                $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
                $_SESSION['role'] = $row['role']; // Adding the role to the session
                if ($row['role'] === 'admin') {
                    header("Location: ../admin/admin-dashboard.php"); // Redirect to admin page

                } else if ($row['role'] === 'student'){
                    header("Location: ../student/student-dashboard.php"); // Redirect to normal user page

                } else if ($row['role'] === 'docent'){
                    header("Location: ../docent/docent-dashboard.php"); // Redirect to normal user page
                }
                
                exit();
            }else{
                header("Location: LogScreen.php?error=Incorrect User Name or Password");
                exit();
            }
        }else{
            header("Location: LogScreen.php?error=Incorrect User Name or Password");
            exit();
        }
    }

}else{
    header("Location: LogScreen.php");
    exit();
}