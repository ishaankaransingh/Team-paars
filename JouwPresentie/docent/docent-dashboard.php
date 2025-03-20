<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker is ingelogd en een docent is
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: ../index.php");
    exit();
}

// Gebruikersgegevens ophalen
$gebruiker_id = $_SESSION['gebruiker_id'];

// Persoon_id en naam van de ingelogde gebruiker ophalen
$sql_persoon = "SELECT p.persoon_id, p.voornaam, p.naam 
                FROM tgebruiker t 
                JOIN personen p ON t.persoon_id = p.persoon_id 
                WHERE t.gebruiker_id = ?";
$stmt_persoon = $conn->prepare($sql_persoon);
$stmt_persoon->bind_param("i", $gebruiker_id);
$stmt_persoon->execute();
$result_persoon = $stmt_persoon->get_result();

if ($result_persoon->num_rows === 1) {
    $user_persoon = $result_persoon->fetch_assoc();
    $persoon_id = $user_persoon['persoon_id'];
    $voornaam = $user_persoon['voornaam'] ?? 'Onbekend';
    $naam = $user_persoon['naam'] ?? 'Onbekend';
} else {
    header("Location: ../index.php?error=User not found");
    exit();
}

// Rooster van de docent ophalen inclusief Richting en dag
$sql_rooster = "SELECT r.start_tijd, r.eind_tijd, k.klas_naam, v.vak_naam, l.lokaal_naam, ri.Richting, d.dag
                FROM rooster r
                JOIN klassen k ON r.klas_id = k.klas_id
                JOIN vakken v ON r.vak_id = v.vak_id
                JOIN lokaal l ON r.lokaal_id = l.lokaal_id
                JOIN richting ri ON r.Richting_ID = ri.Richting_ID
                JOIN dagen d ON r.dag_id = d.dag_id
                WHERE r.persoon_id = ?
                ORDER BY r.dag_id, r.start_tijd";

$stmt_rooster = $conn->prepare($sql_rooster);
$stmt_rooster->bind_param("i", $persoon_id);
$stmt_rooster->execute();
$result_rooster = $stmt_rooster->get_result();

$stmt_persoon->close();
$stmt_rooster->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docenten Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            height: 100vh;
            background: rgba(30, 30, 47, 0.95);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            padding: 20px;
        }

        .sidebar header {
            text-align: center;
            font-size: 1.8rem;
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
            padding: 12px;
            border-radius: 10px;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .sidebar a:hover {
            background: rgba(0, 255, 136, 0.2);
            transform: translateX(5px);
        }

        /* Sidebar toggle for small screens */
        #sidebarToggle {
            display: none;
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 1001;
            background: rgba(30, 30, 47, 0.9);
            border: none;
            color: white;
            padding: 10px;
            border-radius: 5px;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            #sidebarToggle {
                display: block;
            }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }
        }

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 40px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: margin 0.3s ease;
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
            }
        }

        /* Title */
        .dashboard-title {
            text-align: center;
            margin-bottom: 20px;
            font-size: clamp(2rem, 6vw, 3.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }

        /* Welcome Text */
        .welcome-text {
            text-align: center;
            font-size: 1.5rem;
            margin-bottom: 40px;
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }

        /* Clock styles */
        .clock-container {
            display: flex;
            justify-content: center;
            margin-bottom: 40px;
        }

        .clock {
            width: 200px;
            height: 200px;
            background: rgba(30, 30, 47, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: #00ff88;
            box-shadow: 0 8px 30px rgba(0, 255, 136, 0.3);
            border: 3px solid #00ff88;
            text-shadow: 0 0 15px rgba(0, 255, 136, 0.5);
        }

        /* Schedule Container */
        .schedule-container {
            display: none;
            margin-top: 20px;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 8px 32px rgba(0, 255, 136, 0.1);
            width: 100%;
            max-width: 900px;
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table, th, td {
            border: 1px solid #00ff88;
            text-align: left;
            padding: 10px;
        }

        th {
            background-color: #00ff88;
            color: black;
        }

        td {
            color: #e0e0e0;
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }

        tr:hover {
            background-color: rgba(0, 255, 136, 0.1);
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            padding: 15px;
            text-align: center;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            width: 100%;
        }

        .footer p {
            color: #00ff88;
            margin: 0;
            font-size: 0.9rem;
            text-shadow: 0 0 5px rgba(0, 255, 136, 0.3);
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <header>Docent Menu</header>
        <a href="docent-dashboard.php" id="dashboardButton" class="active">
            <i class="fas fa-qrcode"></i>
            <span>Dashboard</span>
        </a>
        <a href="docent_schedule.php" id="scheduleButton">
            <i class="fas fa-calendar-alt"></i>
            <span>Schedule</span>
        </a>
        <a href="docent-aanwezigheid.php" id="presentieButton">
            <i class="fas fa-user-check"></i>
            <span>Presentie</span>
        </a>
        <a href="docent-aanwezigheid-overzicht.php" id="overzichtButton">
            <i class="fas fa-calendar-alt"></i>
            <span>Overzicht</span>
        </a>
        <a href="../login/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="dashboard-title">Docenten Dashboard</h1>
        <div class="welcome-text">Welcome, <?php echo htmlspecialchars($voornaam . ' ' . $naam); ?>!</div>

        <!-- Fysieke Klok -->
        <div class="clock-container">
            <div class="clock" id="clock"></div>
        </div>

        <!-- Schedule Section (optioneel, verborgen standaard) -->
        <div class="schedule-container" id="scheduleContainer">
            <h2>Your Schedule</h2>
            <table>
                <tr>
                    <th>Dag</th>
                    <th>Start Tijd</th>
                    <th>Eind Tijd</th>
                    <th>Klas</th>
                    <th>Vak</th>
                    <th>Lokaal</th>
                    <th>Richting</th>
                </tr>
                <?php while ($row = $result_rooster->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['dag']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_tijd']); ?></td>
                        <td><?php echo htmlspecialchars($row['eind_tijd']); ?></td>
                        <td><?php echo htmlspecialchars($row['klas_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['vak_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['lokaal_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['Richting']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <div class="footer">
            <p>© 2099 Quantum Admin System</p>
        </div>
    </div>

    <script>
        // Sidebar toggle functionaliteit
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const scheduleContainer = document.getElementById('scheduleContainer');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Toon rooster bij klikken op de knop
        document.getElementById('scheduleButton').addEventListener('click', () => {
            scheduleContainer.style.display = scheduleContainer.style.display === 'block' ? 'none' : 'block';
        });

        // Toon welkomsttekst bij klikken op de dashboardknop en verberg rooster
        document.getElementById('dashboardButton').addEventListener('click', () => {
            document.querySelector('.welcome-text').style.display = 'block';
            scheduleContainer.style.display = 'none';
        });

        // Sluit sidebar bij klikken buiten de sidebar
        document.addEventListener('click', (event) => {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Klok functionaliteit
        function updateClock() {
            const now = new Date();
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');
            document.getElementById('clock').textContent = `${hours}:${minutes}:${seconds}`;
        }
        setInterval(updateClock, 1000);
        updateClock(); // Initialiseer direct
    </script>
</body>
</html>