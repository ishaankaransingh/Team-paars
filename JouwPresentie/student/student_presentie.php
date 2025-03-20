<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and has the role of a student
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../index.php");
    exit();
}

$gebruiker_id = $_SESSION['gebruiker_id'];

// Get persoon_id and naam details for the logged-in user
$sql_persoon = "
    SELECT p.persoon_id, p.voornaam, p.naam 
    FROM tgebruiker t 
    JOIN personen p ON t.persoon_id = p.persoon_id 
    WHERE t.gebruiker_id = ?
";
$stmt_persoon = $conn->prepare($sql_persoon);
$stmt_persoon->bind_param("i", $gebruiker_id);
$stmt_persoon->execute();
$result_persoon = $stmt_persoon->get_result();

if ($result_persoon->num_rows === 1) {
    $user_persoon = $result_persoon->fetch_assoc();
    $persoon_id = $user_persoon['persoon_id'];
    $voornaam = $user_persoon['voornaam'];
    $naam = $user_persoon['naam'];
} else {
    header("Location: ../index.php?error=User not found");
    exit();
}

// Fetch filter values from GET request
$filter_periode = isset($_GET['periode']) ? intval($_GET['periode']) : null;
$filter_datum = isset($_GET['datum']) ? $_GET['datum'] : null;
$filter_vak = isset($_GET['vak']) ? intval($_GET['vak']) : null;

// Base query to fetch all data from the presentie table for the logged-in user
$sql_presentie = "
    SELECT pr.*, d.dag, sj.Schooljaar, k.klas_naam, p.periode_naam, r.Richting, s.status_naam, v.vak_naam, lo.lokaal_naam
    FROM presentie pr
    LEFT JOIN dagen d ON pr.dag_id = d.dag_id
    LEFT JOIN schooljaar sj ON pr.Jaar_id = sj.jaar_id
    LEFT JOIN klassen k ON pr.klas_id = k.klas_id
    LEFT JOIN periode p ON pr.periode_id = p.periode_id
    LEFT JOIN richting r ON pr.Richting_ID = r.richting_id
    LEFT JOIN status s ON pr.status_id = s.status_id
    LEFT JOIN vakken v ON pr.vak_id = v.vak_id
    LEFT JOIN lokaal lo ON pr.lokaal_id = lo.lokaal_id
    WHERE pr.persoon_id = ?
";

$params = [$persoon_id];
$types = "i";

// Add filters to the query
if ($filter_periode) {
    $sql_presentie .= " AND pr.periode_id = ?";
    $params[] = $filter_periode;
    $types .= "i";
}
if ($filter_datum) {
    $sql_presentie .= " AND pr.datum = ?";
    $params[] = $filter_datum;
    $types .= "s";
}
if ($filter_vak) {
    $sql_presentie .= " AND pr.vak_id = ?";
    $params[] = $filter_vak;
    $types .= "i";
}

$sql_presentie .= " ORDER BY pr.datum DESC";

$stmt_presentie = $conn->prepare($sql_presentie);
$stmt_presentie->bind_param($types, ...$params);
$stmt_presentie->execute();
$result_presentie = $stmt_presentie->get_result();

// Fetch options for filters (only those relevant to the logged-in user)
$periodes = $conn->query("
    SELECT DISTINCT p.periode_id, p.periode_naam 
    FROM presentie pr
    JOIN periode p ON pr.periode_id = p.periode_id
    WHERE pr.persoon_id = $persoon_id
    ORDER BY p.periode_naam
")->fetch_all(MYSQLI_ASSOC);

$vakken = $conn->query("
    SELECT DISTINCT v.vak_id, v.vak_naam 
    FROM presentie pr
    JOIN vakken v ON pr.vak_id = v.vak_id
    WHERE pr.persoon_id = $persoon_id
    ORDER BY v.vak_naam
")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanwezigheid Overzicht</title>
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
            background: rgba(30, 30, 47, 0.9);
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
            padding: 20px;
            transition: margin 0.3s ease;
            width: calc(100% - 250px);
        }

        @media (max-width: 768px) {
            .main-content {
                margin-left: 0;
                width: 100%;
            }
        }

        /* Title */
        .dashboard-title {
            text-align: center;
            margin: 1rem 0;
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }

        /* Filters */
        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filters select, .filters input[type="date"], .filters button {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(30, 30, 47, 0.9);
            color: white;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .filters select:focus, .filters input[type="date"]:focus {
            outline: none;
            border-color: #00ff88;
        }

        .filters button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            color: black;
            cursor: pointer;
        }

        .filters button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
        }

        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(30, 30, 47, 0.9);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.2);
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        th {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            color: black;
            font-weight: bold;
        }

        tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }

        .no-data {
            text-align: center;
            font-style: italic;
            color: #999;
        }    </style>
</head>
<body>
  <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <header>Student Menu</header>
        <a href="student-dashboard.php" id="roosterButton" class="active">
            <i class="fas fa-calendar-alt"></i>
            <span>Rooster</span>
        </a>
        <a href="student_presentie.php" id="aanwezigheidButton">
            <i class="fas fa-user-check"></i>
            <span>Aanwezigheid Overzicht</span>
        </a>
        <a href="../login/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Uitloggen</span>
        </a>
    </div>

    <div class="main-content">
        <h1 class="dashboard-title">Aanwezigheid Overzicht voor <?php echo htmlspecialchars($voornaam . ' ' . $naam); ?></h1>

        <!-- Filter Form -->
        <form method="GET" action="" class="filters">
            <label for="periode">Filter op Periode:</label>
            <select name="periode" id="periode">
                <option value="">-- Alle periodes --</option>
                <?php foreach ($periodes as $periode): ?>
                    <option value="<?php echo $periode['periode_id']; ?>" 
                        <?php if ($filter_periode == $periode['periode_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($periode['periode_naam']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="datum">Filter op Datum:</label>
            <input type="date" name="datum" id="datum" value="<?php echo htmlspecialchars($filter_datum); ?>">

            <label for="vak">Filter op Vak:</label>
            <select name="vak" id="vak">
                <option value="">-- Alle vakken --</option>
                <?php foreach ($vakken as $vak): ?>
                    <option value="<?php echo $vak['vak_id']; ?>" 
                        <?php if ($filter_vak == $vak['vak_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($vak['vak_naam']); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Filter</button>
        </form>

        <!-- Attendance Table -->
        <table>
            <tr>
                <th>Datum</th>
                <th>Dag</th>
                <th>Schooljaar</th>
                <th>Klas</th>
                <th>Periode</th>
                <th>Richting</th>
                <th>Status</th>
                <th>Vak</th>
                <th>Lokaal</th> <!-- Nieuwe kolom voor lokaal_naam -->
            </tr>
            <?php if ($result_presentie->num_rows > 0): ?>
                <?php while ($row = $result_presentie->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['datum'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['dag'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['Schooljaar'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['klas_naam'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['periode_naam'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['Richting'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['status_naam'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['vak_naam'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['lokaal_naam'] ?? '-'); ?></td> <!-- Toon lokaal_naam -->
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="9" class="no-data">Geen aanwezigheidsgegevens gevonden.</td>
                </tr>
            <?php endif; ?>
        </table>
    </div>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>