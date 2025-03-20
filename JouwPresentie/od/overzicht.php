<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Controleer of de gebruiker ingelogd is en de rol 'rc' heeft
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'od') {
    header("Location: ../index.php");
    exit();
}

// Variabelen initialiseren
$selected_role = null;
$filter_naam = '';
$filter_vak = '';
$filter_klas = '';
$filtered_data = [];

// Haal alle klassen op voor het filter
$sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
$result_klassen = $conn->query($sql_klassen);

// Haal alle vakken op voor het filter
$sql_vakken = "SELECT vak_id, vak_naam FROM vakken ORDER BY vak_naam";
$result_vakken = $conn->query($sql_vakken);

// Namenlijst voor studenten en docenten
$sql_namen_studenten = "
    SELECT p.persoon_id, CONCAT(p.voornaam, ' ', p.naam) AS volledige_naam 
    FROM personen p 
    WHERE p.klas_id IS NOT NULL
    ORDER BY volledige_naam
";
$sql_namen_docenten = "
    SELECT DISTINCT p.persoon_id, CONCAT(p.voornaam, ' ', p.naam) AS volledige_naam 
    FROM personen p
    JOIN rooster r ON p.persoon_id = r.persoon_id
    WHERE p.klas_id IS NULL
    ORDER BY volledige_naam
";
$result_namen_studenten = $conn->query($sql_namen_studenten);
$result_namen_docenten = $conn->query($sql_namen_docenten);

// Verwerk het formulier indien het is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reset'])) {
        // Reset alle filters
        $selected_role = null;
        $filter_naam = '';
        $filter_vak = '';
        $filter_klas = '';
        $filtered_data = [];
    } elseif (isset($_POST['update_student'])) {
        // Verwerk het update formulier voor studenten
        $student_id = $_POST['student_id'];
        $new_klas_id = $_POST['new_klas_id'];

        // Update de klas_id van de student
        $sql_update = "UPDATE personen SET klas_id = ? WHERE persoon_id = ?";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param('ii', $new_klas_id, $student_id);
        $stmt_update->execute();
        $stmt_update->close();

        // Behoud de filterwaarden na het updaten
        $selected_role = $_POST['rol'];
        $filter_naam = $_POST['naam'] ?? '';
        $filter_vak = $_POST['vak'] ?? '';
        $filter_klas = $_POST['klas'] ?? '';
    } else {
        $selected_role = $_POST['rol'];
        $filter_naam = $_POST['naam'] ?? '';
        $filter_vak = $_POST['vak'] ?? '';
        $filter_klas = $_POST['klas'] ?? '';
    }

    // Filter de gegevens opnieuw na het updaten of filteren
    if ($selected_role === 'student') {
        // Query om studenten te filteren
        $sql = "
            SELECT p.persoon_id, p.voornaam, p.naam, k.klas_naam 
            FROM personen p
            LEFT JOIN klassen k ON p.klas_id = k.klas_id
            WHERE p.klas_id IS NOT NULL
        ";
        if (!empty($filter_naam)) {
            $sql .= " AND CONCAT(p.voornaam, ' ', p.naam) LIKE ?";
        }
        if (!empty($filter_klas)) {
            $sql .= " AND k.klas_id = ?";
        }

        $stmt = $conn->prepare($sql);
        $params = [];
        $types = '';

        if (!empty($filter_naam)) {
            $params[] = '%' . $filter_naam . '%';
            $types .= 's';
        }
        if (!empty($filter_klas)) {
            $params[] = $filter_klas;
            $types .= 'i';
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $filtered_data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } elseif ($selected_role === 'docent') {
        // Query om docenten te filteren
        $sql = "
            SELECT DISTINCT p.persoon_id, p.voornaam, p.naam,
                GROUP_CONCAT(DISTINCT k.klas_naam ORDER BY k.klas_naam SEPARATOR ', ') AS klassen,
                GROUP_CONCAT(DISTINCT v.vak_naam ORDER BY v.vak_naam SEPARATOR ', ') AS vakken
            FROM rooster r
            JOIN personen p ON r.persoon_id = p.persoon_id
            LEFT JOIN klassen k ON r.klas_id = k.klas_id
            LEFT JOIN vakken v ON r.vak_id = v.vak_id
            WHERE p.klas_id IS NULL
        ";
        if (!empty($filter_naam)) {
            $sql .= " AND CONCAT(p.voornaam, ' ', p.naam) LIKE ?";
        }
        if (!empty($filter_vak)) {
            $sql .= " AND v.vak_id = ?";
        }
        if (!empty($filter_klas)) {
            $sql .= " AND k.klas_id = ?";
        }

        $sql .= " GROUP BY p.persoon_id";

        $stmt = $conn->prepare($sql);
        $params = [];
        $types = '';

        if (!empty($filter_naam)) {
            $params[] = '%' . $filter_naam . '%';
            $types .= 's';
        }
        if (!empty($filter_vak)) {
            $params[] = $filter_vak;
            $types .= 'i';
        }
        if (!empty($filter_klas)) {
            $params[] = $filter_klas;
            $types .= 'i';
        }

        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $filtered_data = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studenten en Docenten Overzicht</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Quantum 2099 Theme */

  /* Quantum 2099 Theme */
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
            margin: 0;
            padding: 0;
        }

        /* Sidebar Styling */
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
    box-shadow: 2px 0 10px rgba(0, 0, 0, 0.5);
}

/* Main Content */
.main-content {
    margin-left: 250px; /* Gelijk aan de breedte van de sidebar */
    padding: 20px;
    transition: margin 0.3s ease;
}

/* Voor kleine schermen (mobiel) */

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

        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin 0.3s ease;
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

        .filter-form {
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(0, 255, 136, 0.3);
            margin-bottom: 20px;
        }

        .filter-form label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #00ff88;
        }

        .filter-form select, .filter-form input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            background: rgba(45, 45, 70, 0.8);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .filter-form select:focus, .filter-form input:focus {
            border-color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
            outline: none;
        }

        .filter-form button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            padding: 10px 20px;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .filter-form button:hover {
            background: linear-gradient(45deg, #00aaff, #00ff88);
            transform: translateY(-2px);
        }

        .filter-form button[type="reset"] {
            background: rgba(255, 0, 0, 0.7);
            margin-left: 10px;
        }

        .filter-form button[type="reset"]:hover {
            background: rgba(255, 0, 0, 0.9);
        }

        /* Table Styling */
        .table {
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 136, 0.3);
            color: white;
            width: 100%;
            margin-bottom: 1rem;
        }

        .table th, .table td {
            border-color: rgba(255, 255, 255, 0.1);
            padding: 12px;
            text-align: left;
        }

        .table th {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            font-weight: bold;
        }

        .table tbody tr:nth-child(odd) {
            background: rgba(30, 30, 47, 0.8);
        }

        .table tbody tr:nth-child(even) {
            background: rgba(45, 45, 70, 0.8);
        }

        .table tbody tr:hover {
            background: rgba(0, 255, 136, 0.05);
        }

        .table tbody tr td form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .table tbody tr td form select {
            background: rgba(45, 45, 70, 0.8);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 10px;
            color: white;
            padding: 5px;
            font-size: 1rem;
        }

        .table tbody tr td form button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            padding: 5px 10px;
            border-radius: 10px;
            color: white;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.3s ease;
        }

        .table tbody tr td form button:hover {
            background: linear-gradient(45deg, #00aaff, #00ff88);
            transform: translateY(-2px);
        }

        small {
            font-size: 0.8em;
            color: #ccc;
        }

        .hidden {
            display: none;
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
    <!-- Main Content -->
    <div class="main-content">
        <h1>Studenten en Docenten Overzicht</h1>

        <!-- Filterformulier -->
        <form action="" method="POST" class="filter-form">
            <label for="rol">Selecteer Rol:</label>
            <select name="rol" id="rol" required onchange="toggleFilters(this.value)">
                <option value="">-- Selecteer een rol --</option>
                <option value="student" <?php echo isset($selected_role) && $selected_role === 'student' ? 'selected' : ''; ?>>Student</option>
                <option value="docent" <?php echo isset($selected_role) && $selected_role === 'docent' ? 'selected' : ''; ?>>Docent</option>
            </select>

            <div id="filters" class="<?php echo empty($selected_role) ? 'hidden' : ''; ?>">
                <label for="naam">Filter op Naam:</label>
                <select name="naam" id="naam">
                    <option value="">-- Selecteer een naam --</option>
                    <?php
                    if ($selected_role === 'student') {
                        while ($row = $result_namen_studenten->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['volledige_naam']); ?>" 
                                <?php if (isset($filter_naam) && $filter_naam == $row['volledige_naam']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['volledige_naam']); ?>
                            </option>
                        <?php endwhile;
                    } elseif ($selected_role === 'docent') {
                        while ($row = $result_namen_docenten->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['volledige_naam']); ?>" 
                                <?php if (isset($filter_naam) && $filter_naam == $row['volledige_naam']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['volledige_naam']); ?>
                            </option>
                        <?php endwhile;
                    }
                    ?>
                </select>

                <label for="vak">Filter op Vak:</label>
                <select name="vak" id="vak">
                    <option value="">-- Selecteer een vak --</option>
                    <?php while ($vak = $result_vakken->fetch_assoc()): ?>
                        <option value="<?php echo $vak['vak_id']; ?>" 
                            <?php if (isset($filter_vak) && $filter_vak == $vak['vak_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($vak['vak_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="klas">Filter op Klas:</label>
                <select name="klas" id="klas">
                    <option value="">-- Selecteer een klas --</option>
                    <?php while ($klas = $result_klassen->fetch_assoc()): ?>
                        <option value="<?php echo $klas['klas_id']; ?>" 
                            <?php if (isset($filter_klas) && $filter_klas == $klas['klas_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($klas['klas_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Filter</button>
                <button type="submit" name="reset">Reset Filters</button>
            </div>
        </form>

        <!-- Tabel met gefilterde gegevens -->
        <?php if (!empty($filtered_data)): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Naam</th>
                        <?php if ($selected_role === 'student'): ?>
                            <th>Klas</th>
                            <th>Update Klas</th>
                        <?php elseif ($selected_role === 'docent'): ?>
                            <th>Klassen</th>
                            <th>Vakken</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filtered_data as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['voornaam'] . ' ' . $row['naam']); ?></td>
                            <?php if ($selected_role === 'student'): ?>
                                <td><?php echo htmlspecialchars($row['klas_naam'] ?? '-'); ?></td>
                                <td>
                                    <!-- Formulier om de klas van een student bij te werken -->
                                    <form action="" method="POST" style="display:inline;">
                                        <input type="hidden" name="student_id" value="<?php echo $row['persoon_id']; ?>">
                                        <input type="hidden" name="rol" value="<?php echo $selected_role; ?>">
                                        <input type="hidden" name="naam" value="<?php echo $filter_naam; ?>">
                                        <input type="hidden" name="vak" value="<?php echo $filter_vak; ?>">
                                        <input type="hidden" name="klas" value="<?php echo $filter_klas; ?>">
                                        <select name="new_klas_id" required>
                                            <option value="">-- Selecteer een klas --</option>
                                            <?php
                                            $result_klassen->data_seek(0); // Reset de klas pointer
                                            while ($klas = $result_klassen->fetch_assoc()): ?>
                                                <option value="<?php echo $klas['klas_id']; ?>">
                                                    <?php echo htmlspecialchars($klas['klas_naam']); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <button type="submit" name="update_student">Update</button>
                                    </form>
                                </td>
                            <?php elseif ($selected_role === 'docent'): ?>
                                <td><?php echo htmlspecialchars($row['klassen'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($row['vakken'] ?? '-'); ?></td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && !$filtered_data): ?>
            <p>Geen resultaten gevonden.</p>
        <?php endif; ?>
    </div>

    <script>
        // Sidebar toggle voor kleine schermen
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Filter toggle
        function toggleFilters(role) {
            const filters = document.getElementById('filters');
            if (role) {
                filters.classList.remove('hidden');
            } else {
                filters.classList.add('hidden');
            }
        }

        // Event listener voor de sidebar toggle
        document.getElementById('sidebarToggle').addEventListener('click', toggleSidebar);
    </script>
</body>
</html>