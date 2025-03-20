<?php
session_start();

// Check if the user is logged in and is a student
if (isset($_SESSION['gebruiker_id']) && isset($_SESSION['user_name']) && $_SESSION['role'] === 'student') {
    $activePage = 'dashboard';
    include('../login/db_connect.php'); // Include your database connection

    // Get the klas_id and klas_naam of the logged-in student
    $gebruiker_id = $_SESSION['gebruiker_id'];

    // Query to get klas_id and klas_naam by joining gebruiker, persoon, and klassen tables
    $klasQuery = "
        SELECT k.klas_naam, p.klas_id 
        FROM tgebruiker g 
        JOIN personen p ON g.persoon_id = p.persoon_id
        JOIN klassen k ON p.klas_id = k.klas_id
        WHERE g.gebruiker_id = ?";

    $stmt = $conn->prepare($klasQuery);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $gebruiker_id);
    $stmt->execute();
    $klasResult = $stmt->get_result();
    $klasRow = $klasResult->fetch_assoc();

    if (!$klasRow) {
        die("Klas not found for the user.");
    }

    $klas_id = $klasRow['klas_id'];
    $klas_naam = $klasRow['klas_naam']; // Get the class name

    // Fetch days from the dagen table
    $daysQuery = "SELECT dag_id, dag FROM dagen ORDER BY dag_id";
    $daysResult = $conn->query($daysQuery);

    if (!$daysResult) {
        die("Failed to fetch days: " . $conn->error);
    }

    $days = [];
    while ($dayRow = $daysResult->fetch_assoc()) {
        $days[$dayRow['dag_id']] = $dayRow['dag'];
    }

    // Fetch lessons grouped by block for the student's class
    $blocksQuery = "
        SELECT lb.lesblok, rs.dag_id, v.vak_naam, l.lokaal_naam
        FROM roosterstud rs
        LEFT JOIN vakken v ON rs.vak_id = v.vak_id
        LEFT JOIN lesblok lb ON rs.lesblok_id = lb.lesblok_id
        LEFT JOIN lokaal l ON rs.lokaal_id = l.lokaal_id
        WHERE rs.klas_id = ?
        ORDER BY lb.lesblok_id, rs.dag_id";

    $stmt = $conn->prepare($blocksQuery);

    if (!$stmt) {
        die("Query preparation failed: " . $conn->error);
    }

    $stmt->bind_param("i", $klas_id);
    $stmt->execute();
    $blocksResult = $stmt->get_result();

    if (!$blocksResult) {
        die("Query execution failed: " . $conn->error);
    }

    $rooster = [];
    while ($row = $blocksResult->fetch_assoc()) {
        $vak_naam = $row['vak_naam'] ?? "Geen les"; // Default to "Geen les" if no subject
        $lokaal_naam = $row['lokaal_naam'] ?? ""; // Default to empty if no lokaal
        $rooster[$row['lesblok']][$row['dag_id']] = $vak_naam . ($lokaal_naam ? " <small>(" . $lokaal_naam . ")</small>" : "");
    }
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooster</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Reset and base styles */
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
            cursor: button;
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
        }

        small {
            font-size: 0.8em;
            color: #ccc;
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
        <h1 class="dashboard-title">Rooster Overzicht voor Klas <?php echo htmlspecialchars($klas_naam); ?></h1>

        <table class="tbl-full-rooster tbl-full">
            <tr>
                <th>Blok</th>
                <?php foreach ($days as $dayName) { echo "<th>$dayName</th>"; } ?>
            </tr>

            <?php foreach ($rooster as $blokNaam => $dagData): ?>
                <tr>
                    <td><?php echo htmlspecialchars($blokNaam); ?></td>
                    <?php foreach (array_keys($days) as $day_id): ?>
                        <td><?php echo $dagData[$day_id] ?? "Geen les"; ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
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

<?php
} else {
    header("Location: index.php");
    exit();
}
?>