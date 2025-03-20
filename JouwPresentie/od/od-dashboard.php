<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and has the role of rc (role_id = 4)
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'od') {
    header("Location: ../index.php");
    exit();
}

$gebruiker_id = $_SESSION['gebruiker_id'];

// Get persoon_id for the logged-in user
$sql_persoon = "SELECT p.persoon_id FROM tgebruiker t JOIN personen p ON t.persoon_id = p.persoon_id WHERE t.gebruiker_id = ?";
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

$stmt_persoon->close();

// Fetch statistics
$sql_student_count = "SELECT COUNT(*) AS total_students FROM personen WHERE klas_id IS NOT NULL";
$result_student_count = $conn->query($sql_student_count)->fetch_assoc();
$total_students = $result_student_count['total_students'];

$sql_klassen_count = "SELECT COUNT(*) AS total_klassen FROM klassen";
$result_klassen_count = $conn->query($sql_klassen_count)->fetch_assoc();
$total_klassen = $result_klassen_count['total_klassen'];

$sql_vakken_count = "SELECT COUNT(*) AS total_vakken FROM vakken";
$result_vakken_count = $conn->query($sql_vakken_count)->fetch_assoc();
$total_vakken = $result_vakken_count['total_vakken'];

$sql_docenten_count = "SELECT COUNT(*) AS total_docenten FROM personen WHERE klas_id IS NULL";
$result_docenten_count = $conn->query($sql_docenten_count)->fetch_assoc();
$total_docenten = $result_docenten_count['total_docenten'];
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RC Dashboard</title>
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
        }
        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin 0.3s ease;
            flex: 1;
        }
        h1.dashboard-title {
            text-align: center;
            font-size: 2rem;
            margin-bottom: 20px;
            color: #00ff88;
            text-shadow: 0 0 10px #00ff88;
        }
        /* Cards */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        .card {
            background: rgba(30, 30, 47, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.2);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 255, 136, 0.4);
        }
        .card i {
            font-size: 2rem;
            color: #00ff88;
            margin-bottom: 10px;
        }
        .card h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
        }
        .card p {
            font-size: 1rem;
            color: #ccc;
        }



        
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
    <header>OD Menu</header>
    
    <a href="od-dashboard.php" id="dashboardButton" class="active">
            <i class="fas fa-qrcode"></i>
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
    <div class="main-content">
        <h1 class="dashboard-title">Welkom bij het Onderdirecteur Dashboard</h1>

        <!-- Cards Container -->
        <div class="cards-container">
            <div class="card">
                <i class="fas fa-users"></i>
                <h3><?php echo $total_students; ?></h3>
                <p>Totaal Studenten</p>
            </div>
            <div class="card">
                <i class="fas fa-school"></i>
                <h3><?php echo $total_klassen; ?></h3>
                <p>Totaal Klassen</p>
            </div>
            <div class="card">
                <i class="fas fa-book"></i>
                <h3><?php echo $total_vakken; ?></h3>
                <p>Totaal Vakken</p>
            </div>
            <div class="card">
                <i class="fas fa-chalkboard-teacher"></i>
                <h3><?php echo $total_docenten; ?></h3>
                <p>Totaal Docenten</p>
            </div>
        </div>
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