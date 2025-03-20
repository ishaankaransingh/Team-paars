<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and has the role of rc (role_id = 4)
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'rc') {
    header("Location: ../index.php");
    exit();
}

// Initialize variables
$success_message = '';
$error_message = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schooljaar'])) {
        // Add a new school year
        $schooljaar = trim($_POST['schooljaar']);
        if (!empty($schooljaar)) {
            $sql_insert = "INSERT INTO schooljaar (Schooljaar) VALUES (?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("s", $schooljaar);
            if ($stmt_insert->execute()) {
                $success_message = "Schooljaar succesvol toegevoegd!";
                // Redirect to prevent resubmission
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het toevoegen van het schooljaar.";
            }
            $stmt_insert->close();
        } else {
            $error_message = "Het schooljaar is verplicht.";
        }
    } elseif (isset($_POST['update_schooljaar'])) {
        // Update an existing school year
        $jaar_id = $_POST['jaar_id'];
        $schooljaar = trim($_POST['schooljaar']);
        if (!empty($schooljaar)) {
            $sql_update = "UPDATE schooljaar SET Schooljaar = ? WHERE Jaar_id = ?";
            $stmt_update = $conn->prepare($sql_update);
            $stmt_update->bind_param("si", $schooljaar, $jaar_id);
            if ($stmt_update->execute()) {
                $success_message = "Schooljaar succesvol bijgewerkt!";
                // Redirect to prevent resubmission
                header("Location: ".$_SERVER['PHP_SELF']);
                exit();
            } else {
                $error_message = "Fout bij het bijwerken van het schooljaar.";
            }
            $stmt_update->close();
        } else {
            $error_message = "Het schooljaar is verplicht.";
        }
    } elseif (isset($_POST['delete_schooljaar'])) {
        // Delete a school year
        $jaar_id = $_POST['jaar_id'];
        $sql_delete = "DELETE FROM schooljaar WHERE Jaar_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $jaar_id);
        if ($stmt_delete->execute()) {
            $success_message = "Schooljaar succesvol verwijderd!";
            // Redirect to prevent resubmission
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Fout bij het verwijderen van het schooljaar.";
        }
        $stmt_delete->close();
    }
}

// Fetch all school years
$sql_schooljaar = "SELECT Jaar_id, Schooljaar FROM schooljaar ORDER BY Schooljaar";
$result_schooljaar = $conn->query($sql_schooljaar);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schooljaar Beheer</title>
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
        <h1>Schooljaar Beheer</h1>

        <?php if (!empty($success_message)): ?>
            <p style="color: green;"><?php echo htmlspecialchars($success_message); ?></p>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error_message); ?></p>
        <?php endif; ?>

        <!-- Form to add a new school year -->
        <h3>Nieuw Schooljaar Toevoegen</h3>
        <form action="" method="POST">
            <label for="schooljaar">Schooljaar:</label>
            <input type="text" name="schooljaar" id="schooljaar" required>
            <button type="submit" name="add_schooljaar" class="btn btn-success">Toevoegen</button>
        </form>

        <!-- List of all school years -->
        <h3>Lijst van Schooljaren</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Schooljaar</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result_schooljaar->num_rows > 0): ?>
                    <?php while ($row = $result_schooljaar->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Jaar_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['Schooljaar']); ?></td>
                            <td>
                                <div class="actions-container">
                                    <!-- Edit button -->
                                    <button onclick="openEditModal(
                                        <?php echo $row['Jaar_id']; ?>,
                                        '<?php echo addslashes($row['Schooljaar']); ?>'
                                    )" class="btn btn-primary">Bewerken</button>
                                    <!-- Delete form -->
                                    <form action="" method="POST" style="display: inline;">
                                        <input type="hidden" name="jaar_id" value="<?php echo $row['Jaar_id']; ?>">
                                        <button type="submit" name="delete_schooljaar" class="btn btn-danger" onclick="return confirm('Weet je zeker dat je dit schooljaar wilt verwijderen?')">Verwijderen</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">Geen schooljaren gevonden.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Modal for editing a school year -->
        <div id="edit-modal">
            <h2>Bewerk Schooljaar</h2>
            <form action="" method="POST">
                <input type="hidden" id="edit-jaar-id" name="jaar_id" value="">
                <label for="edit-schooljaar">Schooljaar:</label>
                <input type="text" id="edit-schooljaar" name="schooljaar" required>
                <button type="submit" name="update_schooljaar" class="btn btn-success">Opslaan</button>
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary">Annuleren</button>
            </form>
        </div>
    </div>

    <script>
        // JavaScript for opening and closing the edit modal
        function openEditModal(jaarId, schooljaar) {
            document.getElementById('edit-jaar-id').value = jaarId;
            document.getElementById('edit-schooljaar').value = schooljaar;
            document.getElementById('edit-modal').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
        }

        // JavaScript for toggling the sidebar on mobile devices
        document.getElementById('sidebarToggle').addEventListener('click', function() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        });
    </script>
</body>
</html>