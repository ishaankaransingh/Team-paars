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

// Initialize variables
$selected_klas_id = null;
$selected_student_id = null;
$attendance_data = [];
$filter_date = null;

// Handle form submission when a class or student is selected or filtered
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_klas_id = $_POST['klas_id'] ?? null;
    $selected_student_id = $_POST['student_id'] ?? null;
    $filter_date = $_POST['filter_date'] ?? null;

    // Build the base query
    $sql_presentie = "SELECT pr.presentie_id, p.voornaam, p.naam, k.klas_naam, v.vak_naam, s.status_naam, pr.datum, pr.dag,
                              pe.periode_naam, r.start_tijd, r.eind_tijd, sj.Schooljaar
                       FROM presentie pr
                       JOIN personen p ON pr.persoon_id = p.persoon_id
                       JOIN klassen k ON pr.klas_id = k.klas_id
                       JOIN vakken v ON pr.vak_id = v.vak_id
                       JOIN status s ON pr.status_id = s.status_id
                       JOIN rooster r ON pr.klas_id = r.klas_id AND pr.vak_id = r.vak_id
                       JOIN periode pe ON r.periode_id = pe.periode_id
                       JOIN schooljaar sj ON r.Jaar_id = sj.Jaar_id
                       WHERE pr.klas_id = ?";

    // Add student filter if provided
    if ($selected_student_id) {
        $sql_presentie .= " AND pr.persoon_id = ?";
    }

    // Add date filter if provided
    if ($filter_date) {
        $sql_presentie .= " AND pr.datum = ?";
    }

    $sql_presentie .= " ORDER BY pr.datum DESC";

    // Prepare and execute the query
    $stmt_presentie = $conn->prepare($sql_presentie);
    if ($selected_student_id && $filter_date) {
        $stmt_presentie->bind_param("iis", $selected_klas_id, $selected_student_id, $filter_date);
    } elseif ($selected_student_id) {
        $stmt_presentie->bind_param("ii", $selected_klas_id, $selected_student_id);
    } elseif ($filter_date) {
        $stmt_presentie->bind_param("is", $selected_klas_id, $filter_date);
    } else {
        $stmt_presentie->bind_param("i", $selected_klas_id);
    }
    $stmt_presentie->execute();
    $result_presentie = $stmt_presentie->get_result();
    $attendance_data = $result_presentie->fetch_all(MYSQLI_ASSOC);

    $stmt_presentie->close();
}

$stmt_persoon->close();
$stmt_klassen->close();
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aanwezigheid Overzicht</title>
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
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.5);
            margin-bottom: 20px;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        form label {
            color: #00ff88;
            font-weight: bold;
        }

        form select, form input[type="date"], form button {
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #00ff88;
            background: rgba(0, 0, 0, 0.5);
            color: white;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        form select:hover, form input[type="date"]:hover, form button:hover {
            border-color: #00aaff;
            background: rgba(0, 255, 136, 0.1);
        }

        form button {
            background-color: #00ff88;
            color: black;
            cursor: pointer;
        }

        form button:hover {
            background-color: #00aaff;
        }

        /* Dropdown hover effect */
        form select option:hover {
            background-color: #00ff88 !important;
            color: black !important;
            font-weight: bold !important;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #00ff88;
        }

        th {
            background-color: rgba(0, 255, 136, 0.2);
        }

        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.1);
        }

        tr:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .export-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
        }

        .export-buttons button {
            padding: 10px;
            background-color: #00ff88;
            border: none;
            color: black;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .export-buttons button:hover {
            background-color: #00aaff;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            form {
                flex-direction: column;
            }
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

        <a href="../login /logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <div class="main-content">
        <h1 class="dashboard-title">Aanwezigheid Overzicht</h1>

        <!-- Dropdown and Filter Form -->
        <form method="POST">
            <label for="klas">Selecteer een klas:</label>
            <select name="klas_id" id="klas" required onchange="this.form.submit()">
                <option value="">-- Selecteer een klas --</option>
                <?php while ($row = $result_klassen->fetch_assoc()): ?>
                    <option value="<?php echo $row['klas_id']; ?>" 
                        <?php if ($selected_klas_id == $row['klas_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['klas_naam']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <?php if ($selected_klas_id): ?>
                <!-- Fetch students in the selected class -->
                <?php
                $sql_students = "SELECT p.persoon_id, p.voornaam, p.naam 
                                 FROM personen p 
                                 WHERE p.klas_id = ?
                                 ORDER BY p.voornaam, p.naam";
                $stmt_students = $conn->prepare($sql_students);
                $stmt_students->bind_param("i", $selected_klas_id);
                $stmt_students->execute();
                $result_students = $stmt_students->get_result();
                ?>

                <label for="student">Selecteer een student:</label>
                <select name="student_id" id="student">
                    <option value="">-- Selecteer een student --</option>
                    <?php while ($row = $result_students->fetch_assoc()): ?>
                        <option value="<?php echo $row['persoon_id']; ?>" 
                            <?php if ($selected_student_id == $row['persoon_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($row['voornaam'] . ' ' . $row['naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>

                <?php $stmt_students->close(); ?>
            <?php endif; ?>

            <label for="filter_date">Filter op datum:</label>
            <input type="date" name="filter_date" id="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">

            <button type="submit">Toon Aanwezigheid</button>
        </form>

        <!-- Export Buttons -->
        <div class="export-buttons">
            <button onclick="exportToExcel()">Exporteren naar Excel</button>
            <button onclick="exportToPDF()">Exporteren naar PDF</button>
        </div>

        <!-- Display attendance data if a class is selected -->
        <?php if ($selected_klas_id && !empty($attendance_data)): ?>
            <table id="attendanceTable">
                <tr>
                    <th>Student</th>
                    <th>Klas</th>
                    <th>Vak</th>
                    <th>Status</th>
                    <th>Datum</th>
                    <th>Dag</th>
                    <th>Periode</th>
                    <th>Schooljaar</th>
                    <th>Start Tijd</th>
                    <th>Eind Tijd</th>
                </tr>
                <?php foreach ($attendance_data as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['voornaam'] . ' ' . $row['naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['klas_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['vak_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['status_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['datum']); ?></td>
                        <td><?php echo htmlspecialchars($row['dag']); ?></td>
                        <td><?php echo htmlspecialchars($row['periode_naam']); ?></td>
                        <td><?php echo htmlspecialchars($row['Schooljaar']); ?></td>
                        <td><?php echo htmlspecialchars($row['start_tijd']); ?></td>
                        <td><?php echo htmlspecialchars($row['eind_tijd']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php elseif ($selected_klas_id && empty($attendance_data)): ?>
            <p>Er zijn geen aanwezigheidsgegevens gevonden voor deze klas of student.</p>
        <?php endif; ?>
    </div>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Export to Excel
        function exportToExcel() {
            const table = document.getElementById('attendanceTable');
            const rows = table.querySelectorAll('tr');
            let csvContent = "data:text/csv;charset=utf-8,";

            // Voeg de headers toe
            const headers = Array.from(rows[0].querySelectorAll('th'))
                .map(header => `"${header.textContent.replace(/"/g, '""')}"`)
                .join(';');
            csvContent += headers + "\r\n";

            // Voeg de rijen toe
            for (let i = 1; i < rows.length; i++) {
                const rowData = Array.from(rows[i].querySelectorAll('td'))
                    .map(cell => `"${cell.textContent.replace(/"/g, '""')}"`)
                    .join(';');
                csvContent += rowData + "\r\n";
            }

            // Download het CSV-bestand
            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "aanwezigheid_overzicht.csv");
            document.body.appendChild(link);
            link.click();
        }

        // Export to PDF
        function exportToPDF() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            const table = document.getElementById('attendanceTable');
            const rows = table.querySelectorAll('tr');

            // Definieer kolombreedtes
            const columnWidths = [40, 30, 30, 30, 25, 25, 30, 30, 25, 25]; // Aanpasbare breedtes voor elke kolom
            const pageHeight = doc.internal.pageSize.height || doc.internal.pageSize.getHeight();
            let yPos = 10; // Beginpositie op de pagina

            // Functie om een rij toe te voegen aan de PDF
            function addRow(row, isHeader = false) {
                let xPos = 10; // Beginpositie links
                const cells = row.querySelectorAll('th, td');

                cells.forEach((cell, index) => {
                    doc.setFont(isHeader ? 'bold' : 'normal'); // Vetgedrukt voor headers
                    doc.text(cell.textContent, xPos, yPos);
                    xPos += columnWidths[index]; // Verplaats naar de volgende kolom
                });

                yPos += 10; // Verplaats naar de volgende rij
                if (yPos > pageHeight - 10) { // Nieuwe pagina als de inhoud de pagina overschrijdt
                    doc.addPage();
                    yPos = 10;
                }
            }

            // Voeg de headers toe
            addRow(rows[0], true);

            // Voeg de rijen toe
            for (let i = 1; i < rows.length; i++) {
                addRow(rows[i]);
            }

            // Sla de PDF op
            doc.save('aanwezigheid_overzicht.pdf');
        }
    </script>

    <!-- Include jsPDF library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
</body>
</html>