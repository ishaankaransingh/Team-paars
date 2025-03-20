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
    if (isset($_POST['add_klas'])) {
        // Voeg een nieuwe klas toe
        $klas_naam = trim($_POST['klas_naam']);

        if (!empty($klas_naam)) {
            $sql_insert = "INSERT INTO klassen (klas_naam) VALUES (?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("s", $klas_naam);
            if ($stmt_insert->execute()) {
                $success_message = "Klas succesvol toegevoegd!";
                // Redirect om te voorkomen dat het formulier opnieuw wordt ingediend
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het toevoegen van de klas.";
            }
            $stmt_insert->close();
        } else {
            $error_message = "De naam van de klas is verplicht.";
        }
    } elseif (isset($_POST['update_klas'])) {
        // Werk een bestaande klas bij
        $klas_id = $_POST['klas_id'];
        $klas_naam = trim($_POST['klas_naam']);

        if (!empty($klas_naam)) {
            $sql_update = "UPDATE klassen SET klas_naam = ? WHERE klas_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $klas_naam, $klas_id);
            if ($stmt_update->execute()) {
                $success_message = "Klas succesvol bijgewerkt!";
                // Redirect om te voorkomen dat het formulier opnieuw wordt ingediend
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het bijwerken van de klas.";
            }
            $stmt_update->close();
        } else {
            $error_message = "De naam van de klas is verplicht.";
        }
    } elseif (isset($_POST['delete_klas'])) {
        // Verwijder een klas
        $klas_id = $_POST['klas_id'];

        $sql_delete = "DELETE FROM klassen WHERE klas_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $klas_id);
        if ($stmt_delete->execute()) {
            $success_message = "Klas succesvol verwijderd!";
            // Redirect om te voorkomen dat het formulier opnieuw wordt ingediend
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Fout bij het verwijderen van de klas.";
        }
        $stmt_delete->close();
    }
}

// Haal alle klassen op voor weergave
$sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
$result_klassen = $conn->query($sql_klassen);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Klassen Beheer</title>
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

        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"], input[type="number"], textarea, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            background: rgba(30, 30, 47, 0.9);
            color: white;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease;
            margin-right: 10px;
        }

        button:hover {
            background-color: #0056b3;
        }

        .btn-success {
            background-color: #28a745;
        }

        .btn-success:hover {
            background-color: #218838;
        }

        .btn-danger {
            background-color: #dc3545;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        .btn-secondary {
            background-color: #6c757d;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
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

    <!-- Sidebar -->
    <!-- Sidebar -->
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
        <h1>Klassen Beheer</h1>

        <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Formulier om een nieuwe klas toe te voegen -->
        <h3>Nieuwe Klas Toevoegen</h3>
        <form action="" method="POST">
            <label for="klas_naam">Klas Naam:</label>
            <input type="text" name="klas_naam" id="klas_naam" required>
            <button type="submit" name="add_klas" class="btn btn-success">Toevoegen</button>
        </form>

        <!-- Lijst van alle klassen -->
        <h3>Lijst van Klassen</h3>
        <table>
            <thead>
                <tr>
                    <th>Klas ID</th>
                    <th>Klas Naam</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_klassen->num_rows > 0): ?>
                    <?php while ($klas = $result_klassen->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($klas['klas_id']); ?></td>
                            <td><?php echo htmlspecialchars($klas['klas_naam']); ?></td>
                            <td>
                                <div class="actions-container">
                                    <!-- Bewerkknop -->
                                    <button onclick="openEditModal(
                                        <?php echo $klas['klas_id']; ?>,
                                        '<?php echo addslashes($klas['klas_naam']); ?>'
                                    )" class="btn btn-primary">Bewerken</button>
                                    <!-- Verwijderknop -->
                                    <form action="" method="POST" style="display: inline;">
                                        <input type="hidden" name="klas_id" value="<?php echo $klas['klas_id']; ?>">
                                        <button type="submit" name="delete_klas" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je deze klas wilt verwijderen?')">Verwijderen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Geen klassen gevonden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Bewerkingsmodal -->
        <div id="edit-modal">
            <h2>Bewerk Klas</h2>
            <form action="" method="POST">
                <input type="hidden" id="edit-klas-id" name="klas_id" value="">
                <label for="edit-klas-naam">Klas Naam:</label>
                <input type="text" id="edit-klas-naam" name="klas_naam" required>
                <button type="submit" name="update_klas" class="btn btn-primary">Opslaan</button>
                <button type="button" onclick="document.getElementById('edit-modal').style.display = 'none';" class="btn btn-secondary">Annuleren</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(klasId, klasNaam) {
            document.getElementById('edit-klas-id').value = klasId;
            document.getElementById('edit-klas-naam').value = klasNaam;
            document.getElementById('edit-modal').style.display = 'block';
        }

        // Sidebar toggle voor kleine schermen
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>