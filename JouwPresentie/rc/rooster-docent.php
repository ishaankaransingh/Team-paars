<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker ingelogd is en de rol 'rc' heeft
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'rc') {
    header("Location: ../index.php");
    exit();
}

// Haal alle docenten op uit de personen tabel met rol_id = 3 (docenten)
$sql_docenten = "
    SELECT p.persoon_id, CONCAT(p.voornaam, ' ', p.naam) AS volledige_naam 
    FROM personen p
    WHERE p.rol_id = 3
";
$result_docenten = $conn->query($sql_docenten);

// Variabelen initialiseren
$selected_docent_id = null;
$rooster_data = [];
$filter_klas_id = null;
$filter_vak_id = null;
$filter_periode_id = null;

// Verwerk het formulier indien het is ingediend
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_rooster'])) {
        // Voeg een nieuw roosteritem toe
        $persoon_id = $_POST['persoon_id'];
        $klas_id = $_POST['klas_id'];
        $vak_id = $_POST['vak_id'];
        $dag_id = $_POST['dag_id'];
        $start_tijd = $_POST['start_tijd'];
        $eind_tijd = $_POST['eind_tijd'];
        $lokaal_id = $_POST['lokaal_id'];
        $periode_id = $_POST['periode_id'];
        $jaar_id = $_POST['jaar_id'];
        $richting_id = $_POST['richting_id'];

        $sql_insert = "
            INSERT INTO rooster (
                persoon_id, klas_id, vak_id, dag_id, start_tijd, eind_tijd, lokaal_id, 
                periode_id, jaar_id, richting_id
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param(
            "iiisssiiii", 
            $persoon_id, $klas_id, $vak_id, $dag_id, $start_tijd, $eind_tijd, 
            $lokaal_id, $periode_id, $jaar_id, $richting_id
        );
        $stmt_insert->execute();
        $stmt_insert->close();

        // Redirect om formulier resubmit te voorkomen
        header("Location: rooster-docent.php?persoon_id=$persoon_id");
        exit();
    } elseif (isset($_POST['update_rooster'])) {
        // Update een bestaand roosteritem
        $rooster_id = $_POST['rooster_id'];
        $persoon_id = $_POST['persoon_id'];
        $klas_id = $_POST['klas_id'];
        $vak_id = $_POST['vak_id'];
        $dag_id = $_POST['dag_id'];
        $start_tijd = $_POST['start_tijd'];
        $eind_tijd = $_POST['eind_tijd'];
        $lokaal_id = $_POST['lokaal_id'];
        $periode_id = $_POST['periode_id'];
        $jaar_id = $_POST['jaar_id'];
        $richting_id = $_POST['richting_id'];

        $sql_update = "
            UPDATE rooster 
            SET persoon_id = ?, klas_id = ?, vak_id = ?, dag_id = ?, start_tijd = ?, 
                eind_tijd = ?, lokaal_id = ?, periode_id = ?, jaar_id = ?, richting_id = ?
            WHERE rooster_id = ?
        ";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->bind_param(
            "iiisssiiiii", 
            $persoon_id, $klas_id, $vak_id, $dag_id, $start_tijd, $eind_tijd, 
            $lokaal_id, $periode_id, $jaar_id, $richting_id, $rooster_id
        );
        $stmt_update->execute();
        $stmt_update->close();

        // Redirect om formulier resubmit te voorkomen
        header("Location: rooster-docent.php?persoon_id=$persoon_id");
        exit();
    } elseif (isset($_POST['delete_rooster'])) {
        // Verwijder een roosteritem
        $rooster_id = $_POST['rooster_id'];

        $sql_delete = "DELETE FROM rooster WHERE rooster_id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $rooster_id);
        $stmt_delete->execute();
        $stmt_delete->close();

        // Redirect om formulier resubmit te voorkomen
        header("Location: rooster-docent.php?persoon_id=$selected_docent_id");
        exit();
    }
}

// Filter roosterdata als een docent is geselecteerd
if (isset($_GET['persoon_id'])) {
    $selected_docent_id = $_GET['persoon_id'];

    // Filtercriteria ophalen
    $filter_klas_id = isset($_GET['filter_klas_id']) ? $_GET['filter_klas_id'] : null;
    $filter_vak_id = isset($_GET['filter_vak_id']) ? $_GET['filter_vak_id'] : null;
    $filter_periode_id = isset($_GET['filter_periode_id']) ? $_GET['filter_periode_id'] : null;

    $sql_rooster = "
        SELECT r.rooster_id, d.dag, v.vak_naam, k.klas_naam, r.start_tijd, r.eind_tijd, 
               l.lokaal_naam, pr.periode_naam, j.Schooljaar, ri.Richting
        FROM rooster r
        JOIN dagen d ON r.dag_id = d.dag_id
        JOIN vakken v ON r.vak_id = v.vak_id
        JOIN klassen k ON r.klas_id = k.klas_id
        JOIN lokaal l ON r.lokaal_id = l.lokaal_id
        JOIN periode pr ON r.periode_id = pr.periode_id
        JOIN schooljaar j ON r.jaar_id = j.jaar_id
        JOIN richting ri ON r.richting_id = ri.richting_id
        WHERE r.persoon_id = ?
    ";

    // Voeg filtercriteria toe aan de query
    if ($filter_klas_id) {
        $sql_rooster .= " AND r.klas_id = $filter_klas_id";
    }
    if ($filter_vak_id) {
        $sql_rooster .= " AND r.vak_id = $filter_vak_id";
    }
    if ($filter_periode_id) {
        $sql_rooster .= " AND r.periode_id = $filter_periode_id";
    }

    $stmt_rooster = $conn->prepare($sql_rooster);
    $stmt_rooster->bind_param("i", $selected_docent_id);
    $stmt_rooster->execute();
    $result_rooster = $stmt_rooster->get_result();
    $rooster_data = $result_rooster->fetch_all(MYSQLI_ASSOC);
    $stmt_rooster->close();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roosterbeheer voor Docenten</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Algemene reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* Body styling */
        body {
            font-family: 'Arial', sans-serif;
            background: radial-gradient(circle, #0a0a1a, #000);
            color: white;
            min-height: 100vh;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }

        /* Sidebar styling */
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

        /* Main content styling */
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

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 15px;
            overflow: hidden;
            border: 1px solid rgba(0, 255, 136, 0.3);
        }

        th, td {
            border-color: rgba(255, 255, 255, 0.1);
            padding: 12px;
            text-align: left;
            color: white;
        }

        th {
            background: rgba(0, 255, 136, 0.1);
            color: #00ff88;
            font-weight: bold;
        }

        tr:nth-child(odd) {
            background: rgba(30, 30, 47, 0.8);
        }

        tr:nth-child(even) {
            background: rgba(45, 45, 70, 0.8);
        }

        tr:hover {
            background: rgba(0, 255, 136, 0.05);
        }

        /* Form styling */
        form {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 5px;
        }

        select, input[type="time"], input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            background: rgba(30, 30, 47, 0.9);
            color: white;
            border: 1px solid rgba(0, 255, 136, 0.3);
            backdrop-filter: blur(20px);
        }

        select:focus, input[type="time"]:focus, input[type="text"]:focus {
            border-color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        button {
            padding: 10px 20px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: rgba(30, 30, 47, 0.9);
            padding: 20px;
            border-radius: 5px;
            width: 300px;
            color: white;
        }

        small {
            font-size: 0.8em;
            color: #ccc;
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
        <h1>Roosterbeheer voor Docenten</h1>

        <!-- Formulier om een docent te selecteren -->
        <form action="" method="GET">
            <label for="persoon_id">Selecteer een docent:</label>
            <select name="persoon_id" id="persoon_id" onchange="this.form.submit()" required>
                <option value="">-- Selecteer een docent --</option>
                <?php while ($docent = $result_docenten->fetch_assoc()): ?>
                    <option value="<?php echo $docent['persoon_id']; ?>" 
                        <?php if (isset($_GET['persoon_id']) && $_GET['persoon_id'] == $docent['persoon_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($docent['volledige_naam']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($selected_docent_id): ?>
            <!-- Filterformulier -->
            <h3>Filter Rooster</h3>
            <form action="" method="GET">
                <input type="hidden" name="persoon_id" value="<?php echo $selected_docent_id; ?>">
                <label for="filter_klas_id">Klas:</label>
                <select name="filter_klas_id" id="filter_klas_id">
                    <option value="">-- Selecteer een klas --</option>
                    <?php
                    $sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
                    $result_klassen = $conn->query($sql_klassen);
                    while ($klas = $result_klassen->fetch_assoc()): ?>
                        <option value="<?php echo $klas['klas_id']; ?>" 
                            <?php if (isset($_GET['filter_klas_id']) && $_GET['filter_klas_id'] == $klas['klas_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($klas['klas_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label for="filter_vak_id">Vak:</label>
                <select name="filter_vak_id" id="filter_vak_id">
                    <option value="">-- Selecteer een vak --</option>
                    <?php
                    $sql_vakken = "SELECT vak_id, vak_naam FROM vakken ORDER BY vak_naam";
                    $result_vakken = $conn->query($sql_vakken);
                    while ($vak = $result_vakken->fetch_assoc()): ?>
                        <option value="<?php echo $vak['vak_id']; ?>" 
                            <?php if (isset($_GET['filter_vak_id']) && $_GET['filter_vak_id'] == $vak['vak_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($vak['vak_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <label for="filter_periode_id">Periode:</label>
                <select name="filter_periode_id" id="filter_periode_id">
                    <option value="">-- Selecteer een periode --</option>
                    <?php
                    $sql_periodes = "SELECT periode_id, periode_naam FROM periode";
                    $result_periodes = $conn->query($sql_periodes);
                    while ($periode = $result_periodes->fetch_assoc()): ?>
                        <option value="<?php echo $periode['periode_id']; ?>" 
                            <?php if (isset($_GET['filter_periode_id']) && $_GET['filter_periode_id'] == $periode['periode_id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($periode['periode_naam']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <button type="submit">Filter Toepassen</button>
            </form>

            <!-- Overzicht van het rooster -->
            <h2>Rooster voor Docent</h2>
            <table>
                <thead>
                    <tr>
                        <th>Dag</th>
                        <th>Klas</th>
                        <th>Vak</th>
                        <th>Starttijd</th>
                        <th>Eindtijd</th>
                        <th>Lokaal</th>
                        <th>Periode</th>
                        <th>Schooljaar</th>
                        <th>Richting</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($rooster_data)): ?>
                        <tr>
                            <td colspan="10">Geen roosteritems gevonden.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($rooster_data as $row): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['dag']); ?></td>
                                <td><?php echo htmlspecialchars($row['klas_naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['vak_naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['start_tijd']); ?></td>
                                <td><?php echo htmlspecialchars($row['eind_tijd']); ?></td>
                                <td><?php echo htmlspecialchars($row['lokaal_naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['periode_naam']); ?></td>
                                <td><?php echo htmlspecialchars($row['Schooljaar']); ?></td>
                                <td><?php echo htmlspecialchars($row['Richting']); ?></td>
                                <td>
                                    <form action="" method="POST" style="display: inline;">
                                        <input type="hidden" name="rooster_id" value="<?php echo $row['rooster_id']; ?>">
                                        <button type="submit" name="delete_rooster">Verwijderen</button>
                                    </form>
                                    <button onclick="openEditModal(<?php echo $row['rooster_id']; ?>)">Bewerken</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Formulier om een nieuw roosteritem toe te voegen -->
            <h3>Nieuw Roosteritem Toevoegen</h3>
            <form action="" method="POST">
                <input type="hidden" name="persoon_id" value="<?php echo $selected_docent_id; ?>">
                <label for="klas_id">Klas:</label>
                <select name="klas_id" required>
                    <option value="">-- Selecteer een klas --</option>
                    <?php
                    $sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
                    $result_klassen = $conn->query($sql_klassen);
                    while ($klas = 
                    $result_klassen->fetch_assoc()): ?>
                        <option value="<?php echo $klas['klas_id']; ?>"><?php echo htmlspecialchars($klas['klas_naam']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="vak_id">Vak:</label>
                <select name="vak_id" required>
                    <option value="">-- Selecteer een vak --</option>
                    <?php
                    $sql_vakken = "SELECT vak_id, vak_naam FROM vakken ORDER BY vak_naam";
                    $result_vakken = $conn->query($sql_vakken);
                    while ($vak = $result_vakken->fetch_assoc()): ?>
                        <option value="<?php echo $vak['vak_id']; ?>"><?php echo htmlspecialchars($vak['vak_naam']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="dag_id">Dag:</label>
                <select name="dag_id" required>
                    <option value="">-- Selecteer een dag --</option>
                    <?php
                    $sql_dagen = "SELECT dag_id, dag FROM dagen";
                    $result_dagen = $conn->query($sql_dagen);
                    while ($dag = $result_dagen->fetch_assoc()): ?>
                        <option value="<?php echo $dag['dag_id']; ?>"><?php echo htmlspecialchars($dag['dag']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="start_tijd">Starttijd:</label>
                <input type="time" name="start_tijd" required>
                <label for="eind_tijd">Eindtijd:</label>
                <input type="time" name="eind_tijd" required>
                <label for="lokaal_id">Lokaal:</label>
                <select name="lokaal_id" required>
                    <option value="">-- Selecteer een lokaal --</option>
                    <?php
                    $sql_lokalen = "SELECT lokaal_id, lokaal_naam FROM lokaal";
                    $result_lokalen = $conn->query($sql_lokalen);
                    while ($lokaal = $result_lokalen->fetch_assoc()): ?>
                        <option value="<?php echo $lokaal['lokaal_id']; ?>"><?php echo htmlspecialchars($lokaal['lokaal_naam']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="periode_id">Periode:</label>
                <select name="periode_id" required>
                    <option value="">-- Selecteer een periode --</option>
                    <?php
                    $sql_periodes = "SELECT periode_id, periode_naam FROM periode";
                    $result_periodes = $conn->query($sql_periodes);
                    while ($periode = $result_periodes->fetch_assoc()): ?>
                        <option value="<?php echo $periode['periode_id']; ?>"><?php echo htmlspecialchars($periode['periode_naam']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="jaar_id">Schooljaar:</label>
                <select name="jaar_id" required>
                    <option value="">-- Selecteer een schooljaar --</option>
                    <?php
                    $sql_jaren = "SELECT jaar_id, Schooljaar FROM schooljaar";
                    $result_jaren = $conn->query($sql_jaren);
                    while ($jaar = $result_jaren->fetch_assoc()): ?>
                        <option value="<?php echo $jaar['jaar_id']; ?>"><?php echo htmlspecialchars($jaar['Schooljaar']); ?></option>
                    <?php endwhile; ?>
                </select>
                <label for="richting_id">Richting:</label>
                <select name="richting_id" required>
                    <option value="">-- Selecteer een richting --</option>
                    <?php
                    $sql_richtingen = "SELECT richting_id, Richting FROM richting";
                    $result_richtingen = $conn->query($sql_richtingen);
                    while ($richting = $result_richtingen->fetch_assoc()): ?>
                        <option value="<?php echo $richting['richting_id']; ?>"><?php echo htmlspecialchars($richting['Richting']); ?></option>
                    <?php endwhile; ?>
                </select>
                <button type="submit" name="add_rooster">Toevoegen</button>
            </form>
        <?php endif; ?>

        <!-- Modal voor bewerken -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <h2>Roosteritem Bewerken</h2>
                <form id="editForm" action="" method="POST">
                    <input type="hidden" name="rooster_id" id="edit_rooster_id">
                    <input type="hidden" name="persoon_id" value="<?php echo $selected_docent_id; ?>">
                    <label for=" edit_klas_id">Klas:</label>
                    <select name="klas_id" id="edit_klas_id" required>
                        <option value="">-- Selecteer een klas --</option>
                        <?php
                        $sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
                        $result_klassen = $conn->query($sql_klassen);
                        while ($klas = $result_klassen->fetch_assoc()): ?>
                            <option value="<?php echo $klas['klas_id']; ?>"><?php echo htmlspecialchars($klas['klas_naam']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="edit_vak_id">Vak:</label>
                    <select name="vak_id" id="edit_vak_id" required>
                        <option value="">-- Selecteer een vak --</option>
                        <?php
                        $sql_vakken = "SELECT vak_id, vak_naam FROM vakken ORDER BY vak_naam";
                        $result_vakken = $conn->query($sql_vakken);
                        while ($vak = $result_vakken->fetch_assoc()): ?>
                            <option value="<?php echo $vak['vak_id']; ?>"><?php echo htmlspecialchars($vak['vak_naam']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="edit_dag_id">Dag:</label>
                    <select name="dag_id" id="edit_dag_id" required>
                        <option value="">-- Selecteer een dag --</option>
                        <?php
                        $sql_dagen = "SELECT dag_id, dag FROM dagen";
                        $result_dagen = $conn->query($sql_dagen);
                        while ($dag = $result_dagen->fetch_assoc()): ?>
                            <option value="<?php echo $dag['dag_id']; ?>"><?php echo htmlspecialchars($dag['dag']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="edit_start_tijd">Starttijd:</label>
                    <input type="time" name="start_tijd" id="edit_start_tijd" required>
                    <label for="edit_eind_tijd">Eindtijd:</label>
                    <input type="time" name="eind_tijd" id="edit_eind_tijd" required>
                    <label for="edit_lokaal_id">Lokaal:</label>
                    <select name="lokaal_id" id="edit_lokaal_id" required>
                        <option value="">-- Selecteer een lokaal --</option>
                        <?php
                        $sql_lokalen = "SELECT lokaal_id, lokaal_naam FROM lokaal";
                        $result_lokalen = $conn->query($sql_lokalen);
                        while ($lokaal = $result_lokalen->fetch_assoc()): ?>
                            <option value="<?php echo $lokaal['lokaal_id']; ?>"><?php echo htmlspecialchars($lokaal['lokaal_naam']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="edit_periode_id">Periode:</label>
                    <select name="periode_id" id="edit_periode_id" required>
                        <option value="">-- Selecteer een periode --</option>
                        <?php
                        $sql_periodes = "SELECT periode_id, periode_naam FROM periode";
                        $result_periodes = $conn->query($sql_periodes);
                        while ($periode = $result_periodes->fetch_assoc()): ?>
                            <option value="<?php echo $periode['periode_id']; ?>"><?php echo htmlspecialchars($periode['periode_naam']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="edit_jaar_id">Schooljaar:</label>
                    <select name="jaar_id" id="edit_jaar_id" required>
                        <option value="">-- Selecteer een schooljaar --</option>
                        <?php
                        $sql_jaren = "SELECT jaar_id, Schooljaar FROM schooljaar";
                        $result_jaren = $conn->query($sql_jaren);
                        while ($jaar = $result_jaren->fetch_assoc()): ?>
                            <option value="<?php echo $jaar['jaar_id']; ?>"><?php echo htmlspecialchars($jaar['Schooljaar']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <label for="edit_richting_id">Richting:</label>
                    <select name="richting_id" id="edit_richting_id" required>
                        <option value="">-- Selecteer een richting --</option>
                        <?php
                        $sql_richtingen = "SELECT richting_id, Richting FROM richting";
                        $result_richtingen = $conn->query($sql_richtingen);
                        while ($richting = $result_richtingen->fetch_assoc()): ?>
                            <option value="<?php echo $richting['richting_id']; ?>"><?php echo htmlspecialchars($richting['Richting']); ?></option>
                        <?php endwhile; ?>
                    </select>
                    <button type="submit" name="update_rooster">Bijwerken</button>
                </form>
                <button onclick="closeEditModal()">Sluiten</button>
            </div>
        </div>

        <script>
            // Functie om de modal te openen
            function openEditModal(rooster_id) {
                // Haal de roostergegevens op via AJAX
                fetch(`get_rooster.php?rooster_id=${rooster_id}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('edit_rooster_id').value = data.rooster_id;
                        document.getElementById('edit_klas_id').value = data.klas_id;
                        document.getElementById('edit_vak_id').value = data.vak_id;
                        document.getElementById('edit_dag_id').value = data.dag_id;
                        document.getElementById('edit_start_tijd').value = data.start_tijd;
                        document.getElementById('edit_eind_tijd').value = data.eind_tijd;
                        document.getElementById('edit_lokaal_id').value = data.lokaal_id;
                        document.getElementById('edit_periode_id').value = data.periode_id;
                        document.getElementById('edit_jaar_id').value = data.jaar_id;
                        document.getElementById('edit_richting_id').value = data.richting_id;
                        document.getElementById('editModal').style.display = 'flex';
                    });
            }

            // Functie om de modal te sluiten
            function closeEditModal() {
                document.getElementById('editModal').style.display = 'none';
            }
        </script>
    </div>
</body>
</html>