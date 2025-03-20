<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker is ingelogd en de rol 'rc' heeft
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'rc') {
    header("Location: ../index.php");
    exit();
}

// Initialiseer variabelen
$success_message = '';
$error_message = '';

// Verwerk formulierinzendingen
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_vak'])) {
        // Voeg een nieuw vak toe
        $vak_naam = trim($_POST['vak_naam']);
        $periode_id = $_POST['periode_id'];

        if (!empty($vak_naam) && !empty($periode_id)) {
            $sql_insert = "INSERT INTO vakken (vak_naam, periode_id) VALUES (?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("si", $vak_naam, $periode_id);
            if ($stmt_insert->execute()) {
                $success_message = "Vak succesvol toegevoegd!";
                // Redirect om te voorkomen dat het formulier opnieuw wordt ingediend
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het toevoegen van het vak.";
            }
            $stmt_insert->close();
        } else {
            $error_message = "Alle velden zijn verplicht.";
        }
    } elseif (isset($_POST['update_vak'])) {
        // Werk een bestaand vak bij
        $vak_id = $_POST['vak_id'];
        $vak_naam = trim($_POST['vak_naam']);
        $periode_id = $_POST['periode_id'];

        if (!empty($vak_naam) && !empty($periode_id)) {
            $sql_update = "UPDATE vakken SET vak_naam = ?, periode_id = ? WHERE vak_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("sii", $vak_naam, $periode_id, $vak_id);
            if ($stmt_update->execute()) {
                $success_message = "Vak succesvol bijgewerkt!";
                // Redirect om te voorkomen dat het formulier opnieuw wordt ingediend
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het bijwerken van het vak.";
            }
            $stmt_update->close();
        } else {
            $error_message = "Alle velden zijn verplicht.";
        }
    } elseif (isset($_POST['delete_vak'])) {
        // Verwijder een vak
        $vak_id = $_POST['vak_id'];

        $sql_delete = "DELETE FROM vakken WHERE vak_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $vak_id);
        if ($stmt_delete->execute()) {
            $success_message = "Vak succesvol verwijderd!";
            // Redirect om te voorkomen dat het formulier opnieuw wordt ingediend
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Fout bij het verwijderen van het vak.";
        }
        $stmt_delete->close();
    }
}

// Haal alle periodes op voor de dropdown
$sql_periodes = "SELECT periode_id, periode_naam FROM periode";
$result_periodes = $conn->query($sql_periodes);

// Haal alle vakken op voor weergave
$sql_vakken = "SELECT v.vak_id, v.vak_naam, p.periode_naam 
               FROM vakken v 
               JOIN periode p ON v.periode_id = p.periode_id 
               ORDER BY v.vak_naam";
$result_vakken = $conn->query($sql_vakken);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vakken Beheer</title>
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

        .form-select, .form-control {
            background: rgba(30, 30, 47, 0.9);
            color: white;
            border: 1px solid rgba(0, 255, 136, 0.3);
            backdrop-filter: blur(20px);
            margin-bottom: 10px;
        }

        .form-select:focus, .form-control:focus {
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

        /* Additional styles for form alignment */
        form {
            margin-bottom: 20px;
        }

        td form {
    display: inline-block;
    margin: 0;
}

/* Stijl voor actieknoppen in een rij */
.actions-container {
    display: flex;
    gap: 10px; /* Ruimte tussen de knoppen */
    align-items: center; /* Centreer knoppen verticaal */
}

/* Stijl voor knoppen */
.actions-container button {
    margin: 0; /* Zorg ervoor dat er geen extra marges zijn */
    padding: 8px 16px; /* Gelijkmatige padding voor alle knoppen */
    border-radius: 5px;
    border: none;
    cursor: pointer;
    transition: background 0.3s ease;
}

.actions-container .btn-primary {
    background-color: #007bff;
    color: white;
}

.actions-container .btn-primary:hover {
    background-color: #0056b3;
}

.actions-container .btn-danger {
    background-color: #dc3545;
    color: white;
}

.actions-container .btn-danger:hover {
    background-color: #c82333;
}









td button {
    margin-right: 5px;
    vertical-align: middle; /* Voeg deze regel toe om de knoppen verticaal uit te lijnen */
}

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        form input[type="text"], form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 5px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            background: rgba(30, 30, 47, 0.9);
            color: white;
        }

        form button {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>
    <div class="sidebar" id="sidebar">
    <header>RC Menu</header>
    <a href="rc-dashboard.php">
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
        <h1>Vakken Beheer</h1>

        <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Form to add a new vak -->
        <h3>Nieuw Vak Toevoegen</h3>
        <form action="" method="POST">
            <label for="vak_naam">Vak Naam:</label>
            <input type="text" name="vak_naam" id="vak_naam" class="form-control" required>
            <label for="periode_id">Periode:</label>
            <select name="periode_id" id="periode_id" class="form-select" required>
                <option value="">-- Selecteer een periode --</option>
                <?php while ($periode = $result_periodes->fetch_assoc()): ?>
                    <option value="<?php echo $periode['periode_id']; ?>"><?php echo htmlspecialchars($periode['periode_naam']); ?></option>
                <?php endwhile; ?>
            </select>
            <button type="submit" name="add_vak" class="btn btn-success">Toevoegen</button>
        </form>

        <!-- List of all vakken -->
        <h3>Lijst van Vakken</h3>
        <table>
            <thead>
                <tr>
                    <th>Vak Naam</th>
                    <th>Periode</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_vakken->num_rows > 0): ?>
                    <?php while ($vak = $result_vakken->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($vak['vak_naam']); ?></td>
                            <td><?php echo htmlspecialchars($vak['periode_naam']); ?></td>
                            <td>
    <div class="actions-container">
        <!-- Bewerken knop -->
        <button onclick='openEditModal(<?php echo json_encode($vak); ?>)' class="btn btn-primary">Bewerken</button>
        <!-- Verwijderen formulier -->
        <form action="" method="POST" style="display: inline;">
            <input type="hidden" name="vak_id" value="<?php echo $vak['vak_id']; ?>">
            <button type="submit" name="delete_vak" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je dit vak wilt verwijderen?')">Verwijderen</button>
        </form>
    </div>
</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Geen vakken gevonden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Edit Modal -->
        <div id="edit-modal">
            <h2>Bewerk Vak</h2>
            <form action="" method="POST">
                <input type="hidden" id="edit-vak-id" name="vak_id" value="">
                <label for="edit-vak-naam">Vak Naam:</label>
                <input type="text" id="edit-vak-naam" name="vak_naam" class="form-control" required>
                <label for="edit-periode-id">Periode:</label>
                <select name="periode_id" id="edit-periode-id" class="form-select" required>
                    <option value="">-- Selecteer een periode --</option>
                    <?php $result_periodes->data_seek(0); // Reset pointer ?>
                    <?php while ($periode = $result_periodes->fetch_assoc()): ?>
                        <option value="<?php echo $periode['periode_id']; ?>"><?php echo htmlspecialchars($periode['periode_naam']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="update_vak" class="btn btn-primary">Opslaan</button>
                <button type="button" onclick="document.getElementById('edit-modal').style.display = 'none';" class="btn btn-secondary">Annuleren</button>
            </form>
        </div>
    </div>




    <script>
        function openEditModal(vakData) {
            document.getElementById('edit-vak-id').value = vakData.vak_id;
            document.getElementById('edit-vak-naam').value = vakData.vak_naam;
            document.getElementById('edit-periode-id').value = vakData.periode_id;
            document.getElementById('edit-modal').style.display = 'block';
        }

        // Sidebar toggle for small screens
        document.getElementById('sidebarToggle').addEventListener('click', function () {
            var sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>