<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker is ingelogd en een directeur is
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'directeur') {
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

// Haal alle richtingen op voor de dropdown en bewaar richtingnamen
$sql_richtingen = "SELECT Richting_ID, Richting FROM richting";
$result_richtingen = $conn->query($sql_richtingen);
$richtingen = [];
while ($row = $result_richtingen->fetch_assoc()) {
    $richtingen[$row['Richting_ID']] = $row['Richting'];
}

// Geselecteerde richting (default is leeg)
$selected_richting = $_POST['richting_id'] ?? '';

// Haal de naam van de geselecteerde richting
$selected_richting_naam = !empty($selected_richting) && isset($richtingen[$selected_richting]) ? $richtingen[$selected_richting] : '';

// Overzicht: Aanwezigheidsdata voor pie chart
$aanwezigheid_data = [];
if (!empty($selected_richting)) {
    $sql_overzicht = "SELECT k.klas_naam, 
                            COUNT(CASE WHEN pr.status_id = 2 THEN 1 END) AS aanwezig,
                            COUNT(*) AS totaal
                     FROM klassen k
                     LEFT JOIN presentie pr ON k.klas_id = pr.klas_id
                     LEFT JOIN rooster r ON k.klas_id = r.klas_id
                     WHERE r.Richting_ID = ?
                     GROUP BY k.klas_id, k.klas_naam";
    $stmt_overzicht = $conn->prepare($sql_overzicht);
    $stmt_overzicht->bind_param("i", $selected_richting);
    $stmt_overzicht->execute();
    $result_overzicht = $stmt_overzicht->get_result();
    while ($row = $result_overzicht->fetch_assoc()) {
        $aanwezigheid_data[] = [
            'klas_naam' => $row['klas_naam'],
            'aanwezig' => $row['aanwezig'] ?? 0,
            'afwezig' => ($row['totaal'] - $row['aanwezig']) ?? 0
        ];
    }
} else {
    $sql_overzicht = "SELECT k.klas_naam, 
                            COUNT(CASE WHEN pr.status_id = 2 THEN 1 END) AS aanwezig,
                            COUNT(*) AS totaal
                     FROM klassen k
                     LEFT JOIN presentie pr ON k.klas_id = pr.klas_id
                     LEFT JOIN rooster r ON k.klas_id = r.klas_id
                     GROUP BY k.klas_id, k.klas_naam";
    $stmt_overzicht = $conn->prepare($sql_overzicht);
    $stmt_overzicht->execute();
    $result_overzicht = $stmt_overzicht->get_result();
    while ($row = $result_overzicht->fetch_assoc()) {
        $aanwezigheid_data[] = [
            'klas_naam' => $row['klas_naam'],
            'aanwezig' => $row['aanwezig'] ?? 0,
            'afwezig' => ($row['totaal'] - $row['aanwezig']) ?? 0
        ];
    }
}

// Roosters die goedkeuring nodig hebben, gefilterd op richting (inclusief NULL en 'gepland')
$sql_roosters = "SELECT r.Rooster_id, r.start_tijd, r.eind_tijd, k.klas_naam, v.vak_naam, l.lokaal_naam, d.dag, r.status
                 FROM rooster r
                 JOIN klassen k ON r.klas_id = k.klas_id
                 JOIN vakken v ON r.vak_id = v.vak_id
                 JOIN lokaal l ON r.lokaal_id = l.lokaal_id
                 JOIN dagen d ON r.dag_id = d.dag_id
                 WHERE (r.status = 'gepland' OR r.status IS NULL)";
if (!empty($selected_richting)) {
    $sql_roosters .= " AND r.Richting_ID = ?";
}
$stmt_roosters = $conn->prepare($sql_roosters);
if (!empty($selected_richting)) {
    $stmt_roosters->bind_param("i", $selected_richting);
}
$stmt_roosters->execute();
$result_roosters = $stmt_roosters->get_result();

// Goedkeuring verwerken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rooster_id'])) {
    $rooster_id = $_POST['rooster_id'];
    $sql_update = "UPDATE rooster SET status = 'goedgekeurd' WHERE Rooster_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("i", $rooster_id);
    $stmt_update->execute();
    $stmt_update->close();
    header("Location: directeur-dashboard.php");
    exit();
}

// Periodes ophalen (ongefilterd, want dit is schoolbreed)
$sql_periodes = "SELECT periode_id, periode_naam, start_datum, eind_datum FROM periode";
$result_periodes = $conn->query($sql_periodes);

$stmt_persoon->close();
$stmt_overzicht->close();
$stmt_roosters->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=0.5">
    <title>Directeur Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
            font-size: clamp(8px, 1.8vw, 18px);
            width: 100vw;
        }
        .sidebar {
            width: clamp(120px, 18vw, 300px);
            height: 100vh;
            background: rgba(30, 30, 47, 0.95);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            padding: clamp(8px, 1.5vw, 20px);
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar header {
            text-align: center;
            font-size: clamp(1rem, 2.5vw, 2rem);
            color: #00ff88;
            padding: clamp(8px, 1.5vw, 20px);
            text-shadow: 0 0 10px #00ff88;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: clamp(5px, 1vw, 10px);
            padding: clamp(6px, 1vw, 12px);
            border-radius: 8px;
            transition: background 0.3s ease, transform 0.3s ease;
            font-size: clamp(8px, 1.5vw, 16px);
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            width: 100%;
        }
        .sidebar a:hover {
            background: rgba(0, 255, 136, 0.2);
            transform: translateX(3px);
        }
        #sidebarToggle {
            position: fixed;
            top: clamp(5px, 1vh, 10px);
            left: clamp(5px, 1vw, 10px);
            z-index: 1001;
            background: rgba(30, 30, 47, 0.9);
            border: none;
            color: white;
            padding: clamp(5px, 1vw, 10px);
            border-radius: 5px;
            cursor: pointer;
            font-size: clamp(10px, 2vw, 18px);
            display: block;
        }
        .sidebar.open {
            transform: translateX(0);
        }
        @media (min-width: 1024px) {
            #sidebarToggle { display: none; }
            .sidebar { transform: translateX(0); }
        }
        @media (max-width: 1023px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; width: 100vw; }
        }
        .main-content {
            margin-left: clamp(120px, 18vw, 300px);
            padding: clamp(10px, 2vw, 40px);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: calc(100vw - clamp(120px, 18vw, 300px));
            overflow-x: hidden;
        }
        @media (max-width: 1023px) {
            .main-content { margin-left: 0; width: 100vw; }
        }
        .dashboard-title {
            text-align: center;
            margin-bottom: clamp(6px, 1.5vw, 20px);
            font-size: clamp(1rem, 3.5vw, 3.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }
        .welcome-text {
            text-align: center;
            font-size: clamp(0.7rem, 1.8vw, 1.5rem);
            margin-bottom: clamp(10px, 2vw, 40px);
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }
        .clock-container {
            display: flex;
            justify-content: center;
            margin-bottom: clamp(10px, 2vw, 40px);
        }
        .clock {
            width: clamp(80px, 12vw, 200px);
            height: clamp(80px, 12vw, 200px);
            background: rgba(30, 30, 47, 0.9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: clamp(0.8rem, 2.5vw, 2.5rem);
            color: #00ff88;
            box-shadow: 0 8px 30px rgba(0, 255, 136, 0.3);
            border: 3px solid #00ff88;
            text-shadow: 0 0 15px rgba(0, 255, 136, 0.5);
        }
        .section-container {
            display: none;
            margin-top: clamp(6px, 1.5vw, 20px);
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 10px;
            padding: clamp(8px, 1.5vw, 20px);
            box-shadow: 0 8px 32px rgba(0, 255, 136, 0.1);
            width: 100%;
            max-width: clamp(200px, 95vw, 1400px);
            text-align: center;
            overflow-x: hidden;
        }
        h2 {
            font-size: clamp(0.9rem, 2vw, 2rem);
            margin-bottom: clamp(6px, 1.5vw, 20px);
            color: #00ff88;
        }
        .pie-chart-container {
            width: 100%;
            max-width: clamp(150px, 80vw, 700px);
            margin: 0 auto;
            padding: clamp(6px, 1.5vw, 20px);
            height: clamp(150px, 40vh, 450px);
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: clamp(6px, 1.5vw, 20px);
            table-layout: fixed;
        }
        table, th, td {
            border: 1px solid #00ff88;
            text-align: left;
            padding: clamp(4px, 0.8vw, 10px);
            font-size: clamp(6px, 1.2vw, 16px);
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        th {
            background-color: #00ff88;
            color: black;
            white-space: normal;
            min-width: clamp(30px, 10vw, 150px);
        }
        td {
            color: #e0e0e0;
            min-width: clamp(30px, 10vw, 150px);
            white-space: normal;
        }
        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        tr:hover {
            background-color: rgba(0, 255, 136, 0.1);
        }
        button, select {
            padding: clamp(4px, 0.8vw, 12px) clamp(6px, 1vw, 16px);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            border-radius: 5px;
            color: black;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: clamp(6px, 1.2vw, 16px);
            width: 100%;
            max-width: clamp(60px, 12vw, 150px);
        }
        button:hover, select:hover {
            background: linear-gradient(45deg, #00aaff, #00ff88);
        }
        .filter-form {
            margin-bottom: clamp(6px, 1.5vw, 20px);
            width: 100%;
            display: flex;
            justify-content: center;
        }
        .footer {
            margin-top: clamp(10px, 2vw, 40px);
            padding: clamp(6px, 1vw, 15px);
            text-align: center;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            width: 100%;
            font-size: clamp(6px, 1.2vw, 14px);
        }
        .footer p {
            color: #00ff88;
            margin: 0;
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
        <header>Directeur Menu</header>
        <a href="directeur-dashboard.php" id="dashboardButton" class="active">
            <i class="fas fa-qrcode"></i>
            <span>Dashboard</span>
        </a>
        <a href="#" id="roosterButton">
            <i class="fas fa-calendar-alt"></i>
            <span>Rooster Goedkeuring</span>
        </a>
        <a href="#" id="periodeButton">
            <i class="fas fa-clock"></i>
            <span>Periodes</span>
        </a>
        <a href="../login/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="dashboard-title">Directeur Dashboard</h1>
        <div class="welcome-text">Welcome, <?php echo htmlspecialchars($voornaam . ' ' . $naam); ?>!</div>

        <!-- Fysieke Klok -->
        <div class="clock-container">
            <div class="clock" id="clock"></div>
        </div>

        <!-- Richting Filter -->
        <form class="filter-form" method="POST">
            <select name="richting_id" onchange="this.form.submit()">
                <option value="">-- Selecteer een richting --</option>
                <?php foreach ($richtingen as $id => $naam): ?>
                    <option value="<?php echo $id; ?>" <?php echo $selected_richting == $id ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($naam); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <!-- Overzicht Sectie (Pie Chart) -->
        <div class="section-container" id="overzichtContainer" style="display: block;">
            <h2>Aanwezigheidsstatistieken <?php echo $selected_richting_naam ? '(Richting: ' . htmlspecialchars($selected_richting_naam) . ')' : ''; ?></h2>
            <?php if (!empty($aanwezigheid_data)): ?>
                <div class="pie-chart-container">
                    <canvas id="aanwezigheidChart"></canvas>
                </div>
            <?php else: ?>
                <p>Geen gegevens beschikbaar.</p>
            <?php endif; ?>
        </div>

        <!-- Rooster Goedkeuring Sectie -->
        <div class="section-container" id="roosterContainer">
            <h2>Rooster Goedkeuring <?php echo $selected_richting_naam ? '(Richting: ' . htmlspecialchars($selected_richting_naam) . ')' : ''; ?></h2>
            <table>
                <tr>
                    <th>Dag</th>
                    <th>Start Tijd</th>
                    <th>Eind Tijd</th>
                    <th>Klas</th>
                    <th>Vak</th>
                    <th>Lokaal</th>
                    <th>Status</th>
                    <th>Actie</th>
                </tr>
                <?php while ($row = $result_roosters->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['dag']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_tijd']); ?></td>
                        <td><?php echo htmlspecialchars($row['eind_tijd']); ?></td>
                        <td><?php echo htmlspecialchars($row['klas_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['vak_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['lokaal_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['status'] ?? 'Nog niet ingesteld'); ?></td>
                        <td>
                            <form action="" method="POST">
                                <input type="hidden" name="rooster_id" value="<?php echo $row['Rooster_id']; ?>">
                                <button type="submit">Goedkeuren</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>
        </div>

        <!-- Periodes Sectie -->
        <div class="section-container" id="periodeContainer">
            <h2>Periodes Overzicht</h2>
            <table>
                <tr>
                    <th>Periode Naam</th>
                    <th>Start Datum</th>
                    <th>Eind Datum</th>
                </tr>
                <?php while ($row = $result_periodes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['periode_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_datum']); ?></td>
                        <td><?php echo htmlspecialchars($row['eind_datum']); ?></td>
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
        const overzichtContainer = document.getElementById('overzichtContainer');
        const roosterContainer = document.getElementById('roosterContainer');
        const periodeContainer = document.getElementById('periodeContainer');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Toon overzicht bij dashboard-knop
        document.getElementById('dashboardButton').addEventListener('click', (e) => {
            e.preventDefault();
            overzichtContainer.style.display = 'block';
            roosterContainer.style.display = 'none';
            periodeContainer.style.display = 'none';
            document.querySelector('.welcome-text').style.display = 'block';
        });

        // Toon rooster goedkeuring
        document.getElementById('roosterButton').addEventListener('click', (e) => {
            e.preventDefault();
            overzichtContainer.style.display = 'none';
            roosterContainer.style.display = 'block';
            periodeContainer.style.display = 'none';
            document.querySelector('.welcome-text').style.display = 'none';
        });

        // Toon periodes
        document.getElementById('periodeButton').addEventListener('click', (e) => {
            e.preventDefault();
            overzichtContainer.style.display = 'none';
            roosterContainer.style.display = 'none';
            periodeContainer.style.display = 'block';
            document.querySelector('.welcome-text').style.display = 'none';
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
        updateClock();

        // Pie Chart voor aanwezigheidsstatistieken
        <?php if (!empty($aanwezigheid_data)): ?>
            const ctx = document.getElementById('aanwezigheidChart').getContext('2d');
            const aanwezigheidChart = new Chart(ctx, {
                type: 'pie',
                data: {
                    labels: [
                        <?php 
                        foreach ($aanwezigheid_data as $data) {
                            echo "'Aanwezig " . htmlspecialchars($data['klas_naam']) . "', 'Afwezig " . htmlspecialchars($data['klas_naam']) . "',";
                        }
                        ?>
                    ],
                    datasets: [{
                        data: [
                            <?php 
                            foreach ($aanwezigheid_data as $data) {
                                echo $data['aanwezig'] . "," . $data['afwezig'] . ",";
                            }
                            ?>
                        ],
                        backgroundColor: [
                            <?php 
                            foreach ($aanwezigheid_data as $data) {
                                echo "'#00ff88', '#ff4444',";
                            }
                            ?>
                        ],
                        borderColor: '#000',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 1500
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            labels: {
                                font: { size: clamp(6, 1.2 * window.innerWidth / 100, 16) },
                                color: '#fff',
                                padding: clamp(4, 1 * window.innerWidth / 100, 20),
                                boxWidth: clamp(8, 1.5 * window.innerWidth / 100, 40)
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(0, 0, 0, 0.8)',
                            titleFont: { size: clamp(6, 1.2 * window.innerWidth / 100, 16) },
                            bodyFont: { size: clamp(5, 1 * window.innerWidth / 100, 14) }
                        }
                    }
                }
            });
        <?php endif; ?>

        // Clamp functie voor oudere browsers
        function clamp(min, val, max) {
            return Math.min(Math.max(min, val), max);
        }
    </script>
</body>
</html>