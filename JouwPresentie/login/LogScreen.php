<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=0.5">
    <title>Aanwezigheid login</title>
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
            min-height: 100vh;
            margin: 0;
            padding: clamp(10px, 2vw, 20px);
            overflow: hidden;
            font-size: clamp(14px, 2vw, 20px);
            width: 100vw;
            box-sizing: border-box;
        }

        /* Logo Container */
        .logo-container {
            text-align: center;
            margin-bottom: clamp(20px, 4vw, 40px);
            animation: float 3s ease-in-out infinite;
        }

        .logo-container img {
            width: 100%;
            max-width: clamp(120px, 35vw, 300px);
            filter: drop-shadow(0 0 clamp(10px, 2vw, 20px) rgba(0, 255, 204, 0.5));
        }

        /* Form Container */
        form {
            background: rgba(0, 0, 0, 0.8);
            padding: clamp(20px, 5vw, 40px);
            border-radius: clamp(10px, 2vw, 20px);
            box-shadow: 0 0 clamp(20px, 5vw, 50px) rgba(0, 255, 204, 0.3);
            width: clamp(200px, 60vw, 400px);
            max-width: 100%;
            text-align: center;
            position: relative;
            overflow: hidden;
            animation: form-enter 1.5s ease-out;
            z-index: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Labels */
        label {
            display: block;
            margin: clamp(10px, 2vw, 15px) 0 clamp(5px, 1vw, 5px);
            font-size: clamp(14px, 2.2vw, 20px);
            color: #00ffcc;
            text-transform: uppercase;
            letter-spacing: clamp(1px, 0.3vw, 2px);
        }

        /* Input Fields */
        input {
            width: 100%;
            max-width: clamp(180px, 45vw, 300px);
            padding: clamp(8px, 1.5vw, 12px);
            margin-bottom: clamp(15px, 3vw, 25px);
            border: 2px solid rgba(0, 255, 204, 0.3);
            border-radius: clamp(5px, 1vw, 8px);
            background: transparent;
            color: #00ffcc;
            font-family: 'Orbitron', sans-serif;
            font-size: clamp(14px, 2vw, 18px);
            outline: none;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }

        input:focus {
            border-color: #00ffcc;
            box-shadow: 0 0 15px rgba(0, 255, 204, 0.8);
            transform: scale(1.05);
        }

        /* Login Button */
        button {
            width: 100%;
            max-width: clamp(180px, 45vw, 300px);
            padding: clamp(8px, 1.5vw, 12px);
            background: linear-gradient(45deg, #00b4db, #0083b0);
            border: none;
            border-radius: clamp(5px, 1vw, 8px);
            color: #00ffcc;
            font-family: 'Orbitron', sans-serif;
            font-size: clamp(14px, 2.5vw, 20px);
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            margin-top: clamp(10px, 2vw, 20px);
            align-self: center;
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
            padding: clamp(6px, 1vw, 10px);
            border-radius: clamp(3px, 0.5vw, 5px);
            margin-bottom: clamp(10px, 2vw, 20px);
            font-size: clamp(12px, 2vw, 16px);
            width: 100%;
            max-width: clamp(180px, 45vw, 300px);
            text-align: center;
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
            height: clamp(40px, 10vh, 100px);
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
                transform: translateX(-25%) translateY(clamp(-8px, -2vh, -20px));
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
                transform: translateY(clamp(-5px, -1vh, -10px));
            }
        }

        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            25% {
                transform: translateX(clamp(-5px, -1vw, -10px));
            }
            75% {
                transform: translateX(clamp(5px, 1vw, 10px));
            }
        }

        @keyframes form-enter {
            0% {
                opacity: 0;
                transform: translateY(clamp(20px, 5vh, 50px)) rotateX(90deg);
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

        <label>Email</label>
        <input type="text" name="username" placeholder="Email" required>

        <label>Password</label>
        <input type="password" name="password" placeholder="Password" required>

        <button type="submit">Log in</button>
    </form>
</body>
</html>