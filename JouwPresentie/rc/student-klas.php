<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Controleer of de gebruiker is ingelogd en de rol 'od' heeft
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'rc') {
    header("Location: ../index.php");
    exit();
}

$gebruiker_id = $_SESSION['gebruiker_id'];

// Haal de persoon_id op voor de ingelogde gebruiker (uit tgebruiker)
$sql_persoon = "SELECT persoon_id FROM tgebruiker WHERE gebruiker_id = ?";
$stmt_persoon = $conn->prepare($sql_persoon);
$stmt_persoon->bind_param("i", $gebruiker_id);
$stmt_persoon->execute();
$result_persoon = $stmt_persoon->get_result();

if ($result_persoon->num_rows === 1) {
    $user_persoon = $result_persoon->fetch_assoc();
    $persoon_id = $user_persoon['persoon_id'];
} else {
    header("Location: ../index.php?error=User not found");
    exit();
}

// Haal alle studenten op die nog geen klas hebben (klas_id IS NULL) en de role_id '2' (student) hebben
$sql_students = "
    SELECT persoon_id, voornaam, naam 
    FROM personen 
    WHERE klas_id IS NULL AND rol_id = 2
";
$result_students = $conn->query($sql_students);

// Haal alle klassen op voor toewijzing
$sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
$result_klassen = $conn->query($sql_klassen);

// Behandel het formulier om een klas toe te wijzen aan een student
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = $_POST['student_id'] ?? null;
    $klas_id = $_POST['klas_id'] ?? null;

    if ($student_id && $klas_id) {
        // Werk de klas_id van de student bij in de database
        $sql_update = "UPDATE personen SET klas_id = ? WHERE persoon_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param("ii", $klas_id, $student_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Vernieuw de pagina om de bijgewerkte gegevens te tonen
        header("Location: student-klas.php");
        exit();
    }
}

$stmt_persoon->close();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Klas Toewijzing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin 0.3s ease;
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        h1.dashboard-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: #00ff88;
            text-shadow: 0 0 10px #00ff88;
        }
        form {
            margin-top: 20px;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        select, button, input[type="text"] {
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            cursor: pointer;
            transition: background 0.3s ease;
            width: 250px;
            margin: 5px;
        }
        select:hover, button:hover, input[type="text"]:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        /* Fix voor dropdown zichtbaarheid */
        select {
            background: rgba(30, 30, 47, 0.9); /* Achtergrond van de dropdown */
            color: #fff; /* Tekstkleur in de dropdown wanneer gesloten */
        }
        select option {
            background: #fff; /* Achtergrond van de opties */
            color: #000; /* Tekstkleur van de opties */
        }
        button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            color: black;
            margin-top: 10px;
            width: auto;
        }
        .search-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <header>RC Menu</header>
        <a href="RC-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> <!-- Dashboard icon -->
            <span>Dashboard</span>
        </a>
        <a href="student-klas.php">
            <i class="fas fa-users"></i>
            <span>Student Klas</span>
        </a>
        <a href="rooster-student.php">
            <i class="fas fa-calendar-alt"></i>
            <span>Rooster Student</span>
        </a>
        <a href="rooster-docent.php">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Rooster Docent</span>
        </a>
        <a href="overzicht.php">
            <i class="fas fa-chart-bar"></i>
            <span>Overzicht</span>
        </a>
        <a href="vakken.php">
            <i class="fas fa-book"></i>
            <span>Vakken</span>
        </a>
        <a href="periode.php">
            <i class="fas fa-clock"></i>
            <span>Periode</span>
        </a>
        <a href="lokaal.php">
            <i class="fas fa-building"></i>
            <span>Lokaal</span>
        </a>
        <!-- Nieuwe tabs -->
        <a href="klassen.php">
            <i class="fas fa-school"></i>
            <span>Klas</span>
        </a>
        <a href="schooljaar.php">
            <i class="fas fa-calendar-week"></i>
            <span>Schooljaar</span>
        </a>
        <a href="../login/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="dashboard-title">Student Klas Toewijzing</h1>
        
        <?php if ($result_students->num_rows > 0): ?>
            <!-- Formulier met zoekbalk bovenaan -->
            <form action="" method="POST">
                <!-- Zoekbalk -->
                <div class="search-container">
                    <input type="text" id="studentSearch" placeholder="Zoek student..." onkeyup="filterStudents()">
                </div>

                <!-- Studenten dropdown -->
                <select name="student_id" id="studentDropdown" required>
                    <option value="">-- Selecteer een student --</option>
                    <?php while ($student = $result_students->fetch_assoc()): ?>
                        <option value="<?php echo $student['persoon_id']; ?>">
                            <?php echo htmlspecialchars($student['voornaam'] . ' ' . $student['naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <!-- Klassen dropdown -->
                <select name="klas_id" required>
                    <option value="">-- Selecteer een klas --</option>
                    <?php while ($klas = $result_klassen->fetch_assoc()): ?>
                        <option value="<?php echo $klas['klas_id']; ?>">
                            <?php echo htmlspecialchars($klas['klas_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Toewijzen</button>
            </form>
        <?php else: ?>
            <p>Er zijn geen studenten zonder een klas gevonden.</p>
        <?php endif; ?>
    </div>

    <script>
        // Functie om studenten te filteren op basis van zoekopdracht
        function filterStudents() {
            const input = document.getElementById('studentSearch').value.toLowerCase();
            const select = document.getElementById('studentDropdown');
            const options = select.getElementsByTagName('option');

            for (let i = 0; i < options.length; i++) {
                const text = options[i].text.toLowerCase();
                if (text.includes(input) || input === '') {
                    options[i].style.display = '';
                } else {
                    options[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>