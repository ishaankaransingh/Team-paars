<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker is ingelogd en de rol 'rc' heeft
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'od') {
    header("Location: ../index.php");
    exit();
}

// Initialiseer variabelen voor feedbackberichten
$success_message = '';
$error_message = '';

// Verwerk formulierinzendingen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_periode'])) {
        // Voeg een nieuwe periode toe
        $periode_naam = trim($_POST['periode_naam']);
        $start_datum = $_POST['start_datum'];
        $eind_datum = $_POST['eind_datum'];

        if (!empty($periode_naam) && !empty($start_datum) && !empty($eind_datum)) {
            $sql_insert = "INSERT INTO periode (periode_naam, start_datum, eind_datum) VALUES (?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("sss", $periode_naam, $start_datum, $eind_datum);
            if ($stmt_insert->execute()) {
                $success_message = "Periode succesvol toegevoegd!";
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het toevoegen van de periode.";
            }
            $stmt_insert->close();
        } else {
            $error_message = "Alle velden zijn verplicht.";
        }
    } elseif (isset($_POST['update_periode'])) {
        // Werk een bestaande periode bij
        $periode_id = $_POST['periode_id'];
        $periode_naam = trim($_POST['periode_naam']);
        $start_datum = $_POST['start_datum'];
        $eind_datum = $_POST['eind_datum'];

        if (!empty($periode_naam) && !empty($start_datum) && !empty($eind_datum)) {
            $sql_update = "UPDATE periode SET periode_naam = ?, start_datum = ?, eind_datum = ? WHERE periode_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sssi", $periode_naam, $start_datum, $eind_datum, $periode_id);
            if ($stmt_update->execute()) {
                $success_message = "Periode succesvol bijgewerkt!";
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het bijwerken van de periode.";
            }
            $stmt_update->close();
        } else {
            $error_message = "Alle velden zijn verplicht.";
        }
    } elseif (isset($_POST['delete_periode'])) {
        // Verwijder een periode
        $periode_id = $_POST['periode_id'];

        $sql_delete = "DELETE FROM periode WHERE periode_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $periode_id);
        if ($stmt_delete->execute()) {
            $success_message = "Periode succesvol verwijderd!";
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Fout bij het verwijderen van de periode.";
        }
        $stmt_delete->close();
    }
}

// Haal alle periodes op voor weergave
$sql_periodes = "SELECT periode_id, periode_naam, start_datum, eind_datum FROM periode ORDER BY start_datum";
$result_periodes = $conn->query($sql_periodes);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Periode Beheer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Quantum 2099 Theme */
        body {
            font-family: 'Arial', sans-serif;
            background: radial-gradient(circle, #0a0a1a, #000);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

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
            overflow-y: auto;
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

        h1 {
            text-align: center;
            margin: 1rem 0;
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }

        .form-select, .form-control, input[type="text"], input[type="date"] {
            background: rgba(30, 30, 47, 0.9);
            color: white;
            border: 1px solid rgba(0, 255, 136, 0.3);
            backdrop-filter: blur(20px);
        }

        .form-select:focus, .form-control:focus, input[type="text"]:focus, input[type="date"]:focus {
            background: rgba(30, 30, 47, 0.9);
            color: white;
            border-color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: white;
        }

        th, td {
            border-color: rgba(255, 255, 255, 0.1);
            padding: 12px;
            text-align: left;
        }

        th {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            font-weight: bold;
        }

        tbody tr:nth-child(odd) {
            background: rgba(30, 30, 47, 0.8);
        }

        tbody tr:nth-child(even) {
            background: rgba(45, 45, 70, 0.8);
        }

        tbody tr:hover {
            background: rgba(0, 255, 136, 0.05);
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        #overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        #edit-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            z-index: 1000;
            color: white;
        }

        button, input {
    margin: 10px;
    padding: 8px;
}

.container {
    display: flex;
    flex-direction: column;
    gap: 15px; /* Ruimte tussen elementen */
}

        #edit-modal button {
            margin-right: 10px;
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
    <a href="od-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> <!-- Dashboard icon -->
        <span>Dashboard</span>
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
        <h1>Periode Beheer</h1>

        <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Formulier om een nieuwe periode toe te voegen -->
        <h3>Nieuwe Periode Toevoegen</h3>
        <form action="" method="POST">
            <label for="periode_naam">Periode Naam:</label>
            <input type="text" name="periode_naam" id="periode_naam" class="form-control" required>
            <label for="start_datum">Start Datum:</label>
            <input type="date" name="start_datum" id="start_datum" class="form-control" required>
            <label for="eind_datum">Eind Datum:</label>
            <input type="date" name="eind_datum" id="eind_datum" class="form-control" required>
            <button type="submit" name="add_periode" class="btn btn-success">Toevoegen</button>
        </form>

        <!-- Lijst van alle periodes -->
        <h3>Lijst van Periodes</h3>
        <table>
            <thead>
                <tr>
                    <th>Periode Naam</th>
                    <th>Start Datum</th>
                    <th>Eind Datum</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_periodes->num_rows > 0): ?>
                    <?php while ($periode = $result_periodes->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($periode['periode_naam']); ?></td>
                            <td><?php echo htmlspecialchars($periode['start_datum']); ?></td>
                            <td><?php echo htmlspecialchars($periode['eind_datum']); ?></td>
                            <td>
                                <!-- Bewerkknop -->
                                <button onclick="openEditModal(
                                    <?php echo $periode['periode_id']; ?>,
                                    '<?php echo addslashes($periode['periode_naam']); ?>',
                                    '<?php echo $periode['start_datum']; ?>',
                                    '<?php echo $periode['eind_datum']; ?>'
                                )" class="btn btn-primary">Bewerken</button>
                                <!-- Verwijderformulier -->
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="periode_id" value="<?php echo $periode['periode_id']; ?>">
                                    <button type="submit" name="delete_periode" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je deze periode wilt verwijderen?')">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4">Geen periodes gevonden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Overlay -->
        <div id="overlay"></div>

        <!-- Bewerkingsmodal -->
        <div id="edit-modal">
            <h2>Bewerk Periode</h2>
            <form action="" method="POST">
                <input type="hidden" id="edit-periode-id" name="periode_id" value="">
                <label for="edit-periode-naam">Periode Naam:</label>
                <input type="text" id="edit-periode-naam" name="periode_naam" class="form-control" required>
                <label for="edit-start-datum">Start Datum:</label>
                <input type="date" id="edit-start-datum" name="start_datum" class="form-control" required>
                <label for="edit-eind-datum">Eind Datum:</label>
                <input type="date" id="edit-eind-datum" name="eind_datum" class="form-control" required>
                <button type="submit" name="update_periode" class="btn btn-primary">Opslaan</button>
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Annuleren</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(periodeId, periodeNaam, startDatum, eindDatum) {
            document.getElementById('edit-periode-id').value = periodeId;
            document.getElementById('edit-periode-naam').value = periodeNaam;
            document.getElementById('edit-start-datum').value = startDatum;
            document.getElementById('edit-eind-datum').value = eindDatum;
            document.getElementById('edit-modal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // Sidebar toggle voor kleine schermen
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>