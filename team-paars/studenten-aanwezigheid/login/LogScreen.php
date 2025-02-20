<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOGIN - FUTURISTIC OCEAN</title>
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
    <form action="login.php" method="post">
        <div class="logo-container">
            <img src="../CSS/fotos/natinlogo.png" alt="Natin Logo">
        </div>

        <?php if (isset($_GET['error'])) { ?>
            <p class="error"><?php echo $_GET['error']; ?></p>
        <?php } ?>

        <label>User Name or Email</label>
        <input type="text" name="username" placeholder="Email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Log in</button>
    </form>
</body>
</html>