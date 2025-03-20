<?php
session_start();
include('../login/db_connect.php');

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
    header("Location: ../index.php?error=User not found");
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

        /* Card styles */
        .card {
            background: rgba(30, 30, 47, 0.9);
            border-radius: 10px;
            padding: 20px;
            margin: 10px;
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.2);
            text-align: center;
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

        select, input[type="date"] {
            width: 100%;
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(30, 30, 47, 0.9);
            color: white;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        select:focus, input[type="date"]:focus {
            outline: none;
            border-color: #00ff88;
        }

        /* Dropdowns container */
        .dropdowns-container {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .dropdowns-container label {
            font-size: 0.9rem;
            margin-bottom: 5px;
            display: block;
        }

        .dropdowns-container select,
        .dropdowns-container input[type="date"] {
            flex: 1;
            min-width: 150px;
            font-size: 0.9rem;
            padding: 6px;
        }

        /* Stylish button */
        .submit-button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            color: black;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            margin-top: 20px;
        }

        .submit-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
        }

        /* Footer */
        .footer {
            margin-top: 2rem;
            padding: 15px;
            text-align: center;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
        }

        .footer p {
            color: #00ff88;
            margin: 0;
            font-size: 0.9rem;
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

    <div class="main-content">
        <h1 class="dashboard-title">Aanwezigheid Registreren</h1>

        <form action="docent-aanwezigheid.php" method="GET" class="card">
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
            $sql_lesson_details = "SELECT DISTINCT r.vak_id, v.vak_naam, r.periode_id, pe.periode_naam, r.Jaar_id, sj.Schooljaar, r.Richting_ID, ri.Richting, r.lokaal_id, lo.lokaal_naam 
                                   FROM rooster r
                                   LEFT JOIN vakken v ON r.vak_id = v.vak_id
                                   LEFT JOIN periode pe ON r.periode_id = pe.periode_id
                                   LEFT JOIN schooljaar sj ON r.Jaar_id = sj.jaar_id
                                   LEFT JOIN richting ri ON r.Richting_ID = ri.richting_id
                                   LEFT JOIN lokaal lo ON r.lokaal_id = lo.lokaal_id
                                   WHERE r.klas_id = ? AND r.persoon_id = ?";
            $stmt_lesson_details = $conn->prepare($sql_lesson_details);
            $stmt_lesson_details->bind_param("ii", $klas_id, $persoon_id);
            $stmt_lesson_details->execute();
            $result_lesson_details = $stmt_lesson_details->get_result();

            // Fetch dagen (days) from the dagen table
            $sql_dagen = "SELECT dag_id, dag FROM dagen";
            $result_dagen = $conn->query($sql_dagen);

            // Fetch statuses from the status table
            $sql_statuses = "SELECT status_id, status_naam FROM status";
            $result_statuses = $conn->query($sql_statuses);

            // Display the form
            echo '<form action="submit_aanwezigheid.php" method="POST" class="card">';
            echo '<input type="hidden" name="klas_id" value="' . $klas_id . '">';

            // Dropdowns for Richting, Periode, Schooljaar, Vak, Datum, Dag, and Lokaal
            echo '<div class="dropdowns-container">';
            echo '<div>';
            echo '<label for="Richting_ID">Richting:</label>';
            echo '<select name="Richting_ID" id="Richting_ID" required>';
            echo '<option value="">-- Selecteer een richting --</option>';
            $unique_richtingen = [];
            while ($lesson = $result_lesson_details->fetch_assoc()) {
                if (!in_array($lesson['Richting_ID'], $unique_richtingen)) {
                    $unique_richtingen[] = $lesson['Richting_ID'];
                    echo '<option value="' . $lesson['Richting_ID'] . '">' . htmlspecialchars($lesson['Richting']) . '</option>';
                }
            }
            $result_lesson_details->data_seek(0);
            echo '</select>';
            echo '</div>';

            echo '<div>';
            echo '<label for="periode_id">Periode:</label>';
            echo '<select name="periode_id" id="periode_id" required>';
            echo '<option value="">-- Selecteer een periode --</option>';
            $unique_periodes = [];
            while ($lesson = $result_lesson_details->fetch_assoc()) {
                if (!in_array($lesson['periode_id'], $unique_periodes)) {
                    $unique_periodes[] = $lesson['periode_id'];
                    echo '<option value="' . $lesson['periode_id'] . '">' . htmlspecialchars($lesson['periode_naam']) . '</option>';
                }
            }
            $result_lesson_details->data_seek(0);
            echo '</select>';
            echo '</div>';

            echo '<div>';
            echo '<label for="Jaar_id">Schooljaar:</label>';
            echo '<select name="Jaar_id" id="Jaar_id" required>';
            echo '<option value="">-- Selecteer een schooljaar --</option>';
            $unique_jaren = [];
            while ($lesson = $result_lesson_details->fetch_assoc()) {
                if (!in_array($lesson['Jaar_id'], $unique_jaren)) {
                    $unique_jaren[] = $lesson['Jaar_id'];
                    echo '<option value="' . $lesson['Jaar_id'] . '">' . htmlspecialchars($lesson['Schooljaar']) . '</option>';
                }
            }
            $result_lesson_details->data_seek(0);
            echo '</select>';
            echo '</div>';

            echo '<div>';
            echo '<label for="vak_id">Vak:</label>';
            echo '<select name="vak_id" id="vak_id" required>';
            echo '<option value="">-- Selecteer een vak --</option>';
            $unique_vakken = [];
            while ($lesson = $result_lesson_details->fetch_assoc()) {
                if (!in_array($lesson['vak_id'], $unique_vakken)) {
                    $unique_vakken[] = $lesson['vak_id'];
                    echo '<option value="' . $lesson['vak_id'] . '">' . htmlspecialchars($lesson['vak_naam']) . '</option>';
                }
            }
            $result_lesson_details->data_seek(0);
            echo '</select>';
            echo '</div>';

            echo '<div>';
            echo '<label for="datum">Datum:</label>';
            echo '<input type="date" name="datum" id="datum" required>';
            echo '</div>';

            echo '<div>';
            echo '<label for="dag_id">Dag:</label>';
            echo '<select name="dag_id" id="dag_id" required>';
            echo '<option value="">-- Selecteer een dag --</option>';
            while ($dag = $result_dagen->fetch_assoc()) {
                echo '<option value="' . $dag['dag_id'] . '">' . htmlspecialchars($dag['dag']) . '</option>';
            }
            $result_dagen->data_seek(0);
            echo '</select>';
            echo '</div>';

            echo '<div>';
            echo '<label for="lokaal_id">Lokaal:</label>';
            echo '<select name="lokaal_id" id="lokaal_id" required>';
            echo '<option value="">-- Selecteer een lokaal --</option>';
            $unique_lokalen = [];
            while ($lesson = $result_lesson_details->fetch_assoc()) {
                if (!in_array($lesson['lokaal_id'], $unique_lokalen)) {
                    $unique_lokalen[] = $lesson['lokaal_id'];
                    echo '<option value="' . $lesson['lokaal_id'] . '">' . htmlspecialchars($lesson['lokaal_naam']) . '</option>';
                }
            }
            $result_lesson_details->data_seek(0);
            echo '</select>';
            echo '</div>';
            echo '</div>';

            // Table for student names and status dropdowns
            echo '<table>';
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
                echo '<select name="aanwezigheid[' . $student['persoon_id'] . ']">';
                while ($status = $result_statuses->fetch_assoc()) {
                    echo '<option value="' . $status['status_id'] . '">' . htmlspecialchars($status['status_naam']) . '</option>';
                }
                $result_statuses->data_seek(0);
                echo '</select>';
                echo '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';

            echo '<button type="submit" class="submit-button">Aanwezigheid Opslaan</button>';
            echo '</form>';

            $stmt_students->close();
            $stmt_lesson_details->close();
        }
        ?>

        <!-- Footer -->
        <div class="footer">
            <p>&copy; 2023 jouwpresentie systeem</p>
        </div>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>