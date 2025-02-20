<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and is a docent
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'docent') {
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
    header("Location: ../index.php?error=User  not found");
    exit();
}

// Fetch teacher's schedule (rooster)
$sql_rooster = "SELECT DISTINCT k.klas_id, k.klas_naam 
                FROM rooster r
                JOIN klassen k ON r.klas_id = k.klas_id
                WHERE r.persoon_id = ?
                ORDER BY k.klas_naam";
$stmt_rooster = $conn->prepare($sql_rooster);
$stmt_rooster->bind_param("i", $persoon_id);
$stmt_rooster->execute();
$result_rooster = $stmt_rooster->get_result();

$stmt_persoon->close();
$stmt_rooster->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanwezigheid Registreren</title>
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

        form {
            background: rgba(255, 255, 255, 0.1);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.3);
            transition: background  0.3s ease;
        }

        form:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #00ff88;
        }

        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 5px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transition: background 0.3s ease;
        }

        select:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        button {
            background: #00ff88;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        button:hover {
            background: #00cc70;
            transform: scale(1.05);
        }

        .student-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            animation: fadeIn 0.5s ease-in-out;
        }

        .student-table th,
        .student-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .student-table th {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            font-weight: bold;
        }

        .student-table tr:hover {
            background: rgba(255, 255, 255, 0.05);
        }


        .student-table input[type="date"] {
    width: 100%;
    padding: 8px;
    border: none;
    border-radius: 5px;
    background: rgba(255, 255, 255, 0.2);
    color: white;
    transition: background 0.3s ease;
}

.student-table input[type="date"]:hover {
    background: rgba(255, 255, 255, 0.3);
}




        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        .fade-in {
            animation: fadeIn 0.5s ease-in-out;
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
    <span>overzicht</span>
</a>


        <a href="../login /logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <div class="main-content fade-in">
        <h1 class="dashboard-title">Aanwezigheid Registreren</h1>

        <form action="docent-aanwezigheid.php" method="GET">
            <label for="klas">Selecteer een klas:</label>
            <select name="klas_id" id="klas" onchange="this.form.submit()">
                <option value="">-- Selecteer een klas --</option>
                <?php while ($row = $result_rooster->fetch_assoc()): ?>
                    <option value="<?php echo $row['klas_id']; ?>" 
                        <?php if (isset($_GET['klas_id']) && $_GET['klas_id'] == $row['klas_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['klas_naam']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php
        if (isset($_GET['klas_id'])) {
            $klas_id = $_GET['klas_id'];

            // Fetch students in this class from the personen table
            $sql_students = "SELECT p.persoon_id, p.voornaam, p.naam 
                             FROM personen p 
                             WHERE p.klas_id = ?";
            $stmt_students = $conn->prepare($sql_students);
            $stmt_students->bind_param("i", $klas_id);
            $stmt_students->execute();
            $result_students = $stmt_students->get_result();

            // Fetch lesson details for this class from the rooster table
            $sql_lesson_details = "SELECT r.vak_id, r.periode_id, r.Jaar_id, r.Richting_ID, r.dag 
                                   FROM rooster r WHERE r.klas_id = ? AND r.persoon_id = ?";
            $stmt_lesson_details = $conn->prepare($sql_lesson_details);
            $stmt_lesson_details->bind_param("ii", $klas_id, $persoon_id);
            $stmt_lesson_details->execute();
            $lesson_details = $stmt_lesson_details->get_result()->fetch_assoc();

            // Fetch statuses from the status table
            $sql_statuses = "SELECT status_id, status_naam FROM status";
            $result_statuses = $conn->query($sql_statuses);

            // Inside the form where you display students
if ($result_students->num_rows > 0 && $lesson_details) {
    echo '<form action="submit_aanwezigheid.php" method="POST">';
    echo '<input type="hidden" name="klas_id" value="' . $klas_id . '">';
    echo '<input type="hidden" name="vak_id" value="' . $lesson_details['vak_id'] . '">';
    echo '<input type="hidden" name="periode_id" value="' . $lesson_details['periode_id'] . '">';
    echo '<input type="hidden" name="Jaar_id" value="' . $lesson_details['Jaar_id'] . '">';
    echo '<input type="hidden" name="Richting_ID" value="' . $lesson_details['Richting_ID'] . '">';
    echo '<input type="hidden" name="dag" value="' . $lesson_details['dag'] . '">';

 

    echo '<table class="student-table">';
    echo '<thead>';
    echo '<tr>';
    echo '<th>Naam</th>';
    echo '<th>Status</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';

    while ($student = $result_students->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($student['voornaam'] . ' ' . $student['naam']) . '</td>';
        echo '<td>';

// Add manual date input field
echo '<label for="datum">Datum:</label>';
echo '<input type="date" name="datum" required><br><br>'; // Single date input for the entire form

        echo '<select name="aanwezigheid[' . $student['persoon_id'] . ']">';
        while ($status = $result_statuses->fetch_assoc()) {
            echo '<option value="' . $status['status_id'] . '">' . htmlspecialchars($status['status_naam']) . '</option>';
        }
        // Reset the statuses result set pointer
        $result_statuses->data_seek(0);
        echo '</select>';
        echo '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';

    echo '<button type="submit">Aanwezigheid Opslaan</button>';
    echo '</form>';
} else {
    echo '<p>Geen studenten gevonden in deze klas.</p>';
}

            $stmt_students->close();
            $stmt_lesson_details->close();
        }
        ?>
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