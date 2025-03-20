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

// Fetch all classes (klassen) where the teacher gives lessons
$sql_klassen = "SELECT DISTINCT k.klas_id, k.klas_naam 
                FROM rooster r
                JOIN klassen k ON r.klas_id = k.klas_id
                WHERE r.persoon_id = ?
                ORDER BY k.klas_naam";
$stmt_klassen = $conn->prepare($sql_klassen);
$stmt_klassen->bind_param("i", $persoon_id);
$stmt_klassen->execute();
$result_klassen = $stmt_klassen->get_result();

// Fetch all periods (periodes) for filtering
$sql_periodes = "SELECT DISTINCT periode_id, periode_naam FROM periode";
$result_periodes = $conn->query($sql_periodes);

// Fetch vakken (subjects) assigned to the logged-in docent from the rooster table
$sql_vakken = "
    SELECT DISTINCT v.vak_id, v.vak_naam 
    FROM rooster r
    JOIN vakken v ON r.vak_id = v.vak_id
    WHERE r.persoon_id = ?
    ORDER BY v.vak_naam";
$stmt_vakken = $conn->prepare($sql_vakken);
$stmt_vakken->bind_param("i", $persoon_id);
$stmt_vakken->execute();
$result_vakken = $stmt_vakken->get_result();

// Initialize variables
$selected_klas_id = null;
$selected_student_id = null;
$filter_date = null;
$filter_periode_id = null;
$filter_vak_id = null;
$students = [];
$attendance_data = [];

// Handle form submission when a class or student is selected or filtered
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_klas_id = $_POST['klas_id'] ?? null;

    // Fetch students in the selected class
    if ($selected_klas_id) {
        $sql_students = "SELECT persoon_id, voornaam, naam FROM personen WHERE klas_id = ?";
        $stmt_students = $conn->prepare($sql_students);
        $stmt_students->bind_param("i", $selected_klas_id);
        $stmt_students->execute();
        $result_students = $stmt_students->get_result();
        $students = $result_students->fetch_all(MYSQLI_ASSOC);
        $stmt_students->close();
    }

    // If a student, date, periode, or vak filter is applied
    $selected_student_id = $_POST['student_id'] ?? null;
    $filter_date = $_POST['filter_date'] ?? null;
    $filter_periode_id = $_POST['periode_id'] ?? null;
    $filter_vak_id = $_POST['vak_id'] ?? null;

    if ($selected_student_id || $filter_date || $filter_periode_id || $filter_vak_id) {
        $sql_presentie = "SELECT pr.presentie_id, p.persoon_id, p.voornaam, p.naam, k.klas_naam, v.vak_naam, s.status_naam, pr.datum, d.dag, lo.lokaal_naam
                          FROM presentie pr
                          JOIN personen p ON pr.persoon_id = p.persoon_id
                          JOIN klassen k ON pr.klas_id = k.klas_id
                          JOIN vakken v ON pr.vak_id = v.vak_id
                          JOIN status s ON pr.status_id = s.status_id
                          JOIN dagen d ON pr.dag_id = d.dag_id
                          JOIN lokaal lo ON pr.lokaal_id = lo.lokaal_id
                          WHERE pr.klas_id = ?";

        if ($selected_student_id) {
            $sql_presentie .= " AND pr.persoon_id = ?";
        }
        if ($filter_date) {
            $sql_presentie .= " AND pr.datum = ?";
        }
        if ($filter_periode_id) {
            $sql_presentie .= " AND pr.periode_id = ?";
        }
        if ($filter_vak_id) {
            $sql_presentie .= " AND pr.vak_id = ?";
        }

        $stmt_presentie = $conn->prepare($sql_presentie);
        $params = [$selected_klas_id];
        $types = "i";

        if ($selected_student_id) {
            $params[] = $selected_student_id;
            $types .= "i";
        }
        if ($filter_date) {
            $params[] = $filter_date;
            $types .= "s";
        }
        if ($filter_periode_id) {
            $params[] = $filter_periode_id;
            $types .= "i";
        }
        if ($filter_vak_id) {
            $params[] = $filter_vak_id;
            $types .= "i";
        }

        $stmt_presentie->bind_param($types, ...$params);
        $stmt_presentie->execute();
        $result_presentie = $stmt_presentie->get_result();
        $attendance_data = $result_presentie->fetch_all(MYSQLI_ASSOC);
        $stmt_presentie->close();
    }
}

$stmt_persoon->close();
$stmt_klassen->close();
$stmt_vakken->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanwezigheidsoverzicht</title>
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

        /* Filters */
        .filters {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }

        .filters select, .filters input[type="date"], .filters button {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(30, 30, 47, 0.9);
            color: white;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .filters select:focus, .filters input[type="date"]:focus {
            outline: none;
            border-color: #00ff88;
        }

        .filters button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            color: black;
            cursor: pointer;
        }

        .filters button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
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

        /* Bewerk button */
        .bewerk-button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            color: black;
            padding: 5px 10px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .bewerk-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
        }

        /* Export buttons */
        .export-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .export-buttons button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            color: black;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .export-buttons button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
        }

        /* Edit modal */
        .overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .edit-modal {
            display: none;
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(30, 30, 47, 0.9);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.2);
            z-index: 1000;
        }

        .edit-modal h2 {
            margin-bottom: 20px;
            color: #00ff88;
        }

        .edit-modal select, .edit-modal button {
            padding: 8px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(30, 30, 47, 0.9);
            color: white;
            font-size: 0.9rem;
            transition: border-color 0.3s ease;
        }

        .edit-modal button {
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            color: black;
            cursor: pointer;
            margin-right: 10px;
        }

        .edit-modal button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 255, 136, 0.4);
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
        <h1 class="dashboard-title">Aanwezigheidsoverzicht</h1>

        <!-- Class Selection -->
        <form action="" method="POST" class="filters">
            <label for="klas">Selecteer een klas:</label>
            <select name="klas_id" id="klas" onchange="this.form.submit()" required>
                <option value="">-- Selecteer een klas --</option>
                <?php while ($row = $result_klassen->fetch_assoc()): ?>
                    <option value="<?php echo $row['klas_id']; ?>" 
                        <?php if (isset($selected_klas_id) && $selected_klas_id == $row['klas_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['klas_naam']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($selected_klas_id): ?>
            <!-- Student, Date, Periode, and Vak Filters -->
            <form action="" method="POST" class="filters">
                <input type="hidden" name="klas_id" value="<?php echo $selected_klas_id; ?>">
                <label for="student">Selecteer een student:</label>
                <select name="student_id" id="student">
                    <option value="">-- Selecteer een student --</option>
                    <?php foreach ($students as $student): ?>
                        <option value="<?php echo $student['persoon_id']; ?>" 
                            <?php if (isset($selected_student_id) && $selected_student_id == $student['persoon_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($student['voornaam'] . ' ' . $student['naam']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="filter_date">Filter op datum:</label>
                <input type="date" name="filter_date" value="<?php echo $filter_date ?? ''; ?>">

                <label for="periode_id">Filter op periode:</label>
                <select name="periode_id" id="periode_id">
                    <option value="">-- Selecteer een periode --</option>
                    <?php while ($periode = $result_periodes->fetch_assoc()): ?>
                        <option value="<?php echo $periode['periode_id']; ?>" 
                            <?php if (isset($filter_periode_id) && $filter_periode_id == $periode['periode_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($periode['periode_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <label for="vak_id">Filter op vak:</label>
                <select name="vak_id" id="vak_id">
                    <option value="">-- Selecteer een vak --</option>
                    <?php while ($vak = $result_vakken->fetch_assoc()): ?>
                        <option value="<?php echo $vak['vak_id']; ?>" 
                            <?php if (isset($filter_vak_id) && $filter_vak_id == $vak['vak_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($vak['vak_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <button type="submit">Toepassen</button>
            </form>

            <!-- Attendance Overview Table -->
            <table>
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Klas</th>
                        <th>Vak</th>
                        <th>Datum</th>
                        <th>Dag</th>
                        <th>Lokaal</th> <!-- Nieuwe kolom voor lokaal_naam -->
                        <th>Status</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($attendance_data)): ?>
                        <tr>
                            <td colspan="8" class="no-data">Geen aanwezigheidsgegevens gevonden.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($attendance_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['voornaam'] . ' ' . $row['naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['klas_naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['vak_naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['datum']); ?></td>
                                <td><?php echo htmlspecialchars($row['dag']); ?></td>
                                <td><?php echo htmlspecialchars($row['lokaal_naam']); ?></td> <!-- Toon lokaal_naam -->
                                <td><?php echo htmlspecialchars($row['status_naam']); ?></td>
                                <td>
                                    <button class="bewerk-button" onclick="openEditModal(<?php echo $row['presentie_id']; ?>)">Bewerken</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Export Buttons -->
            <div class="export-buttons">
                <button onclick="exportToExcel()">Exporteer naar Excel</button>
                <button onclick="exportToPDF()">Exporteer naar PDF</button>
            </div>

            <!-- Edit Modal -->
            <div class="overlay" id="overlay"></div>
            <div class="edit-modal" id="edit-modal">
                <h2>Bewerk Aanwezigheid</h2>
                <form action="update_aanwezigheid.php" method="POST">
                    <input type="hidden" id="edit-presentie-id" name="presentie_id" value="">
                    <label for="edit-status">Nieuwe status:</label>
                    <select name="status_id" id="edit-status" required>
                        <?php
                        $sql_statuses = "SELECT status_id, status_naam FROM status";
                        $result_statuses = $conn->query($sql_statuses);
                        while ($status = $result_statuses->fetch_assoc()) {
                            echo '<option value="' . $status['status_id'] . '">' . htmlspecialchars($status['status_naam']) . '</option>';
                        }
                        ?>
                    </select>
                    <button type="submit">Opslaan</button>
                    <button type="button" onclick="closeEditModal()">Annuleren</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Edit modal functionality
        function openEditModal(presentieId) {
            document.getElementById('edit-presentie-id').value = presentieId;
            document.getElementById('edit-modal').style.display = 'block';
            document.getElementById('overlay').style.display = 'block';
        }

        function closeEditModal() {
            document.getElementById('edit-modal').style.display = 'none';
            document.getElementById('overlay').style.display = 'none';
        }

        // Export to Excel
        function exportToExcel() {
            const table = document.querySelector('table');
            const rows = table.querySelectorAll('tr');
            let csv = [];
            for (const row of rows) {
                const rowData = [];
                for (const cell of row.querySelectorAll('th, td')) {
                    rowData.push(cell.innerText);
                }
                csv.push(rowData.join(','));
            }
            const csvContent = "data:text/csv;charset=utf-8," + csv.join('\n');
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement('a');
            link.setAttribute('href', encodedUri);
            link.setAttribute('download', 'aanwezigheidsoverzicht.csv');
            document.body.appendChild(link);
            link.click();
        }

        // Export to PDF
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');
            doc.autoTable({ html: 'table' });
            doc.save('aanwezigheidsoverzicht.pdf');
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
</body>
</html>