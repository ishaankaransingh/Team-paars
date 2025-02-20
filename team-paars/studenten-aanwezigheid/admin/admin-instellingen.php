<?php
session_start();

// Verbinding maken met de database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "aanwezigheids_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Verbinding mislukt: " . $conn->connect_error);
}

// Haal de ingelogde gebruiker op
$gebruiker_id = $_SESSION['gebruiker_id'] ?? null;
$persoon_id = $_SESSION['persoon_id'] ?? null;

if ($gebruiker_id && $persoon_id) {
    $sql = "SELECT p.naam, p.voornaam, r.role 
            FROM personen p 
            JOIN rollen r ON p.rol_id = r.role_id 
            WHERE p.persoon_id = $persoon_id";
    $result = $conn->query($sql);
    $user = $result->fetch_assoc();
} else {
    header("Location: ../login/login.php");
    exit();
}

// Functie om een willekeurige gebruiker te selecteren
function selectRandomUser($conn) {
    $sql = "SELECT persoon_id, naam, voornaam FROM personen ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);
    return ($result->num_rows > 0) ? $result->fetch_assoc() : null;
}

// Functie om beloningen toe te kennen
function assignReward($conn, $persoon_id, $beloning_naam) {
    $datum = date("Y-m-d");
    $sql = "INSERT INTO beloningen (persoon_id, beloning_naam, datum) VALUES ($persoon_id, '$beloning_naam', '$datum')";
    return $conn->query($sql);
}

// Functie om het aantal actieve gebruikers te tellen
function countActiveUsers($conn) {
    $sql = "SELECT COUNT(*) as total FROM personen WHERE active = 1";
    $result = $conn->query($sql);
    return $result->fetch_assoc()['total'];
}

// Functie om een notificatie toe te voegen
function addNotification($conn, $persoon_id, $bericht) {
    $datum = date("Y-m-d H:i:s");
    $sql = "INSERT INTO notificaties (persoon_id, bericht, datum) VALUES ($persoon_id, '$bericht', '$datum')";
    return $conn->query($sql);
}

// Functie om het thema te wijzigen
function changeTheme($theme) {
    setcookie("theme", $theme, time() + (86400 * 30), "/"); // Cookie voor 30 dagen
    return "<p>Thema succesvol gewijzigd naar: <strong>" . $theme . "</strong></p>";
}

// Verwerk formulier voor thema wijzigen
if (isset($_POST['theme'])) {
    echo changeTheme($_POST['theme']);
}

// Gebruiker van de Dag selecteren
$randomUser = selectRandomUser($conn);

// Aantal actieve gebruikers tellen
$activeUsers = countActiveUsers($conn);

// Notificatie toevoegen (voorbeeld)
$bericht = "Welkom terug! Bekijk de nieuwe updates.";
addNotification($conn, $persoon_id, $bericht);

// Haal het huidige thema op
$theme = $_COOKIE['theme'] ?? 'dark';
?>

<!DOCTYPE html>
<html lang="nl" data-theme="<?= $theme ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instellingen</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Reset en basisstijlen */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: radial-gradient(circle, #0a0a1a, #000);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
        }

        body[data-theme="light"] {
            background: radial-gradient(circle, #f0f0f0, #fff);
            color: #333;
        }

        .light {
            display: inline-block;
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 5px;
        }

        .green {
            background-color: green;
        }

        .red {
            background-color: red;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar header {
            text-align: center;
            font-size: 1.5rem;
            color: #00ff88;
            padding: 20px;
            text-shadow: 0 0 10px #00ff88;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px;
            border-radius: 10px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(0, 255, 136, 0.1);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        .sidebar a i {
            font-size: 1.2rem;
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        /* Titel */
        .dashboard-title {
            text-align: center;
            margin: 1rem 0;
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }

        /* Button styles */
        .btn {
            background: #00ff88;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .btn:hover {
            background: #00aaff;
        }

        /* Form styles */
        .form-container {
            padding: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
        }

        .input-group input[type="text"],
        .input-group input[type="email"],
        .input-group input[type="password"],
        .input-group input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            font-size: 16px;
            color: #333;
            background-color: #333;
            color: #fff;
            transition: border-color 0.3s;
        }

        .input-group input[type="text"]:focus,
        .input-group input[type="email"]:focus,
        .input-group input[type="password"]:focus,
        .input-group input[type="date"]:focus {
            border-color: #aaa;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <header>Admin Menu</header>
        <a href="admin-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-qrcode"></i>
            <span>Dashboard</span>
        </a>
        <a href="admin-docenten.php" class="<?= ($activePage == 'docenten') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Docenten</span>
        </a>
        <a href="admin-studenten.php" class="<?= ($activePage == 'studenten') ? 'active' : ''; ?>">
            <i class="fas fa-user-graduate"></i>
            <span>Studenten</span>
        </a>
        <a href="admin-systeembeheerder.php" class="<?= ($activePage == 'systeembeheerder') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard"></i>
            <span>Systeembeheerder</span>
        </a>
        <a href="admin-directeur.php" class="<?= ($activePage == 'directeur') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Directeur</span>
        </a>
        <a href="admin -Richtingcoordinator.php" class="<?= ($activePage == 'Richtingcoordinator') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Richtingcoordinator</span>
        </a>
        <a href="admin_od.php" class="<?= ($activePage == 'od') ? 'active' : ''; ?>">
            <i class="fas fa-book-open"></i>
            <span>Onderdirecteur</span>
        </a>
        <a href="admin-instellingen.php" class="<?= ($activePage == 'instellingen') ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i>
            <span>Instellingen</span>
        </a>
        <a href="../login/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <!-- Content -->
    <div class="main-content">
        <h1 class="dashboard-title">Instellingen</h1>

        <!-- Persoonlijke begroeting -->
        <h2>Welkom, <?= $user['voornaam'] ?> <?= $user['naam'] ?>!</h2>

        <!-- Gebruiker van de Dag -->
        <h2>Gebruiker van de Dag 🎉</h2>
        <?php if ($randomUser ): ?>
            <p>Vandaag is <strong><?= $randomUser ['voornaam'] . " " . $randomUser ['naam'] ?></strong> de gelukkige!</p>
        <?php else: ?>
            <p>Geen gebruikers gevonden in de database.</p>
        <?php endif; ?>

        <!-- Statistieken -->
        <h2>Statistieken 📊</h2>
        <p>Aantal actieve gebruikers: <strong><?= $activeUsers ?></strong></p>

        <!-- Thema Switcher -->
        <h2>Thema Switcher 🎨</h2>
        <form method="POST">
            <label for="theme">Kies een thema:</label>
            <select name="theme" id="theme">
                <option value="dark" <?= $theme == 'dark' ? 'selected' : '' ?>>Donker</option>
                <option value="light" <?= $theme == 'light' ? 'selected' : '' ?>>Licht</option>
            </select>
            <button type="submit" class="btn">Thema wijzigen</button>
        </form>
    </div>
</body>
</html>

<?php
// Verbinding sluiten
$conn->close();
?>