<?php
// Start the session
session_start();

// Include the database connection file
include('./login/db_connect.php'); // Include your database connection

// Initialize session variables if they don't exist
if (!isset($_SESSION['failed_attempts'])) {
    $_SESSION['failed_attempts'] = 0;
}
if (!isset($_SESSION['lock_until'])) {
    $_SESSION['lock_until'] = null;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate and sanitize user input
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $usernameOrEmail = validate($_POST['username']);
    $password = validate($_POST['password']);

    // Check for empty fields
    if (empty($usernameOrEmail)) {
        $error = "User Name or Email required";
    } else if (empty($password)) {
        $error = "Password is required";
    } else {
        // Check if the account is currently locked
        if ($_SESSION['lock_until'] !== null && time() < $_SESSION['lock_until']) {
            $remainingTime = $_SESSION['lock_until'] - time();
            $error = "Account locked. Try again in " . gmdate("i:s", $remainingTime);
        } else {
            // Prepare and execute the query to fetch user data
            $sql = "SELECT t.*, p.rol_id, p.active, r.role 
                    FROM tgebruiker t 
                    JOIN personen p ON t.persoon_id = p.persoon_id 
                    JOIN rollen r ON p.rol_id = r.role_id 
                    WHERE t.email = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $usernameOrEmail);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                if (password_verify($password, $row['password'])) {
                    if ($row['active'] !== NULL) {
                        // Reset failed attempts on successful login
                        $_SESSION['failed_attempts'] = 0;
                        $_SESSION['lock_until'] = null;
                        $_SESSION['user_name'] = $row['email'];
                        $_SESSION['name'] = $row['naam'];
                        $_SESSION['gebruiker_id'] = $row['gebruiker_id'];
                        $_SESSION['role'] = $row['role'];

                        // Redirect based on role
                        switch ($row['role']) {
                            case 'admin':
                                header("Location: ./admin/admin-dashboard.php");
                                break;
                            case 'student':
                                header("Location: ./student/student-dashboard.php");
                                break;
                            case 'docent':
                                header("Location: ./docent/docent-dashboard.php");
                                break;
                            case 'od':
                                header("Location: ./od/od-dashboard.php");
                                break;
                            case 'rc':
                                header("Location: ./rc/rc-dashboard.php");
                                break;
                            case 'directeur':
                                header("Location: ./directeur/directeur-dashboard.php");
                                break;
                            case 'systeembeheerder':
                                header("Location: ./systeembeheer/systeembeheer-dashboard.php");
                                break;
                           
                        }
                        exit();
                    } else {
                        $error = "Account is deactivated";
                    }
                } else {
                    // Increment failed attempts
                    $_SESSION['failed_attempts']++;
                    if ($_SESSION['failed_attempts'] >= 3) {
                        $_SESSION['lock_until'] = time() + 60; // Lock for 1 minute
                        $error = "Account locked. Try again in 1 minute.";
                    } else {
                        $error = "Incorrect email or Password";
                    }
                }
            } else {
                $error = "Incorrect email or Password";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanwezigheid Login Portal</title>
    <link rel="stylesheet" type="text/css" href="../CSS/style.css">
    <style>
        /* Global Styles */
        body {
            background: linear-gradient(135deg, #000428, #004e92);
            color: #00ffcc;
            font-family: 'Orbitron', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }
        /* Logo Container */
        .logo-container {
            text-align: center;
            margin-bottom: 30px;
            animation: float 3s ease-in-out infinite;
        }
        .logo-container img {
            width: 100%;
            max-width: 300px;
            filter: drop-shadow(0 0 20px rgba(0, 255, 204, 0.5));
        }
        /* Form Container */
        form {
            background: rgba(0, 0, 0, 0.8);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 50px rgba(0, 255, 204, 0.3);
            width: 400px;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: form-enter 1.5s ease-out;
            z-index: 1;
        }
        /* Labels */
        label {
            display: block;
            margin: 15px 0 5px;
            font-size: 16px;
            color: #00ffcc;
            text-transform: uppercase;
            letter-spacing: 2px;
        }
        /* Input Fields */
        input {
            width: 100%;
            padding: 12px;
            margin-bottom: 25px;
            border: 2px solid rgba(0, 255, 204, 0.3);
            border-radius: 8px;
            background: transparent;
            color: #00ffcc;
            font-family: 'Orbitron', sans-serif;
            outline: none;
            transition: all 0.3s ease;
        }
        input:focus {
            border-color: #00ffcc;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.8);
            transform: scale(1.05);
        }
        /* Login Button */
        button {
            width: 100%;
            padding: 12px;
            background: linear-gradient(45deg, #00b4db, #0083b0);
            border: none;
            border-radius: 8px;
            color: #00ffcc;
            font-family: 'Orbitron', sans-serif;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 300%;
            height: 300%;
            background: radial-gradient(circle, rgba(0, 255, 204, 0.5), transparent);
            transform: translate(-50%, -50%) scale(0);
            transition: transform 0.5s ease;
        }
        button:hover {
            transform: scale(1.05);
            box-shadow: 0 0 20px rgba(0, 180, 219, 0.8);
        }
        button:hover::before {
            transform: translate(-50%, -50%) scale(1);
        }
        /* Error Message */
        .error {
            color: #ff0000;
            background: rgba(255, 0, 0, 0.1);
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            animation: shake 0.5s ease-in-out;
        }
        /* Ocean Wave Animation */
        .ocean {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }
        .wave {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 200%;
            height: 100px;
            background: linear-gradient(transparent, rgba(0, 180, 219, 0.5));
            animation: wave-animation 5s linear infinite;
        }
        .wave:nth-child(1) {
            animation-duration: 7s;
            opacity: 0.7;
        }
        .wave:nth-child(2) {
            animation-duration: 5s;
            opacity: 0.5;
        }
        .wave:nth-child(3) {
            animation-duration: 3s;
            opacity: 0.3;
        }
        @keyframes wave-animation {
            0% {
                transform: translateX(0) translateY(0);
            }
            50% {
                transform: translateX(-25%) translateY(-20px);
            }
            100% {
                transform: translateX(-50%) translateY(0);
            }
        }
        /* Animations */
        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(-10px);
            }
            75% {
                transform: translateX(10px);
            }
        }
        @keyframes form-enter {
            0% {
                opacity: 0;
                transform: translateY(50px) rotateX(90deg);
            }
            100% {
                opacity: 1;
                transform: translateY(0) rotateX(0);
            }
        }
    </style>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Ocean Waves -->
    <div class="ocean">
        <div class="wave"></div>
        <div class="wave"></div>
        <div class="wave"></div>
    </div>
    <!-- Login Form -->
    <form method="post" action="">
        <div class="logo-container">
            <img src="./CSS/fotos/natinlogo.png" alt="Natin Logo">
        </div>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <label>Email</label>
        <input type="text" name="username" placeholder="Email" required>
        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Log in</button>
    </form>
</body>
</html>