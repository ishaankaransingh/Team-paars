<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and has the role of rc (role_id = 4)
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'od') {
    header("Location: ../index.php");
    exit();
}

// Initialize variables
$selected_klas_id = null;
$rooster_data = [];

// Fetch all classes for selection
$sql_klassen = "SELECT klas_id, klas_naam FROM klassen ORDER BY klas_naam";
$result_klassen = $conn->query($sql_klassen);

// Handle form submission when a class is selected
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['klas_id'])) {
        $selected_klas_id = $_POST['klas_id'];

        // Fetch rooster data for the selected class
        $sql_rooster = "
            SELECT r.rooster_id, d.dag, v.vak_naam, l.lesblok, lo.lokaal_naam, r.dag_id, r.vak_id, r.lesblok_id 
            FROM roosterstud r
            JOIN dagen d ON r.dag_id = d.dag_id
            JOIN vakken v ON r.vak_id = v.vak_id
            JOIN lesblok l ON r.lesblok_id = l.lesblok_id
            LEFT JOIN lokaal lo ON r.lokaal_id = lo.lokaal_id
            WHERE r.klas_id = ?
            ORDER BY d.dag ASC, l.lesblok ASC
        ";
        $stmt_rooster = $conn->prepare($sql_rooster);
        $stmt_rooster->bind_param("i", $selected_klas_id);
        $stmt_rooster->execute();
        $result_rooster = $stmt_rooster->get_result();
        $rooster_data = $result_rooster->fetch_all(MYSQLI_ASSOC);
        $stmt_rooster->close();
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rooster Student</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Quantum 2099 Theme */
        {
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
            cursor: button;
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

        .form-select, .form-control {
            background: rgba(30, 30, 47, 0.9);
            color: white;
            border: 1px solid rgba(0, 255, 136, 0.3);
            backdrop-filter: blur(20px);
        }

        .form-select:focus, .form-control:focus {
            background: rgba(30, 30, 47, 0.9);
            color: white;
            border-color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
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
        <h1>Rooster Student</h1>
        <form id="classForm" action="" method="POST" class="mb-4">
            <label for="klas" class="form-label">Selecteer een klas:</label>
            <select name="klas_id" id="klas" class="form-select">
                <option value="">-- Selecteer een klas --</option>
                <?php while ($row = $result_klassen->fetch_assoc()): ?>
                    <option value="<?php echo $row['klas_id']; ?>" 
                        <?php if (isset($_POST['klas_id']) && $_POST['klas_id'] == $row['klas_id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['klas_naam']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if ($selected_klas_id): ?>
            <h2>Rooster voor Klas <?php echo htmlspecialchars($_POST['klas_naam'] ?? ''); ?></h2>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Dag</th>
                        <th>Vak</th>
                        <th>Lesblok</th>
                        <th>Lokaal</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody id="roosterTableBody">
                    <?php foreach ($rooster_data as $row): ?>
                        <tr data-rooster-id="<?php echo $row['rooster_id']; ?>" 
                            data-dag-id="<?php echo $row['dag_id']; ?>" 
                            data-vak-id="<?php echo $row['vak_id']; ?>" 
                            data-lesblok-id="<?php echo $row['lesblok_id']; ?>">
                            <td><?php echo htmlspecialchars($row['dag']); ?></td>
                            <td><?php echo htmlspecialchars($row['vak_naam']); ?></td>
                            <td><?php echo htmlspecialchars($row['lesblok']); ?></td>
                            <td><?php echo htmlspecialchars($row['lokaal_naam'] ?? 'Geen lokaal'); ?></td>
                            <td>
                                <button class="btn btn-danger btn-sm delete-btn" data-rooster-id="<?php echo $row['rooster_id']; ?>">Verwijderen</button>
                                <button class="btn btn-primary btn-sm edit-btn" onclick="openEditModal(<?php echo $row['rooster_id']; ?>)">Bewerken</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <h3>Nieuw Rooster Toevoegen</h3>
            <form id="addForm" class="mb-4">
                <input type="hidden" name="klas_id" value="<?php echo $selected_klas_id; ?>">
                <div class="mb-3">
                    <label for="dag_id" class="form-label">Dag:</label>
                    <select name="dag_id" class="form-select" required>
                        <option value="">-- Selecteer een dag --</option>
                        <?php
                        $sql_dagen = "SELECT dag_id, dag FROM dagen";
                        $result_dagen = $conn->query($sql_dagen);
                        while ($dag = $result_dagen->fetch_assoc()) {
                            echo '<option value="' . $dag['dag_id'] . '">' . htmlspecialchars($dag['dag']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="vak_id" class="form-label">Vak:</label>
                    <select name="vak_id" class="form-select" required>
                        <option value="">-- Selecteer een vak --</option>
                        <?php
                        $sql_vakken = "SELECT vak_id, vak_naam FROM vakken";
                        $result_vakken = $conn->query($sql_vakken);
                        while ($vak = $result_vakken->fetch_assoc()) {
                            echo '<option value="' . $vak['vak_id'] . '">' . htmlspecialchars($vak['vak_naam']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="lesblok_id" class="form-label">Lesblok:</label>
                    <select name="lesblok_id" class="form-select" required>
                        <option value="">-- Selecteer een lesblok --</option>
                        <?php
                        $sql_lesblok = "SELECT lesblok_id, lesblok FROM lesblok";
                        $result_lesblok = $conn->query($sql_lesblok);
                        while ($lesblok = $result_lesblok->fetch_assoc()) {
                            echo '<option value="' . $lesblok['lesblok_id'] . '">' . htmlspecialchars($lesblok['lesblok']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="lokaal_id" class="form-label">Lokaal:</label>
                    <select name="lokaal_id" class="form-select">
                        <option value="">-- Selecteer een lokaal --</option>
                        <?php
                        $sql_lokaal = "SELECT lokaal_id, lokaal_naam FROM lokaal";
                        $result_lokaal = $conn->query($sql_lokaal);
                        while ($lokaal = $result_lokaal->fetch_assoc()) {
                            echo '<option value="' . $lokaal['lokaal_id'] . '">' . htmlspecialchars($lokaal['lokaal_naam']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">Toevoegen</button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Bewerk Rooster</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editForm">
                        <input type="hidden" id="edit-rooster-id" name="rooster_id" value="">
                        <div class="mb-3">
                            <label for="edit-dag_id" class="form-label">Dag:</label>
                            <select name="dag_id" id="edit-dag_id" class="form-select" required>
                                <option value="">-- Selecteer een dag --</option>
                                <?php
                                $sql_dagen = "SELECT dag_id, dag FROM dagen";
                                $result_dagen = $conn->query($sql_dagen);
                                while ($dag = $result_dagen->fetch_assoc()) {
                                    echo '<option value="' . $dag['dag_id'] . '">' . htmlspecialchars($dag['dag']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-vak_id" class="form-label">Vak:</label>
                            <select name="vak_id" id="edit-vak_id" class="form-select" required>
                                <option value="">-- Selecteer een vak --</option>
                                <?php
                                $sql_vakken = "SELECT vak_id, vak_naam FROM vakken";
                                $result_vakken = $conn->query($sql_vakken);
                                while ($vak = $result_vakken->fetch_assoc()) {
                                    echo '<option value="' . $vak['vak_id'] . '">' . htmlspecialchars($vak['vak_naam']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-lesblok_id" class="form-label">Lesblok:</label>
                            <select name="lesblok_id" id="edit-lesblok_id" class="form-select" required>
                                <option value="">-- Selecteer een lesblok --</option>
                                <?php
                                $sql_lesblok = "SELECT lesblok_id, lesblok FROM lesblok";
                                $result_lesblok = $conn->query($sql_lesblok);
                                while ($lesblok = $result_lesblok->fetch_assoc()) {
                                    echo '<option value="' . $lesblok['lesblok_id'] . '">' . htmlspecialchars($lesblok['lesblok']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-lokaal_id" class="form-label">Lokaal:</label>
                            <select name="lokaal_id" id="edit-lokaal_id" class="form-select">
                                <option value="">-- Selecteer een lokaal --</option>
                                <?php
                                $sql_lokaal = "SELECT lokaal_id, lokaal_naam FROM lokaal";
                                $result_lokaal = $conn->query($sql_lokaal);
                                while ($lokaal = $result_lokaal->fetch_assoc()) {
                                    echo '<option value="' . $lokaal['lokaal_id'] . '">' . htmlspecialchars($lokaal['lokaal_naam']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary">Opslaan</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle class selection without page reload
        $('#classForm').on('change', function () {
            this.submit();
        });

        // Add new rooster entry via AJAX
        $('#addForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'add_rooster.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    alert('Nieuwe roosterinvoer toegevoegd!');
                    location.reload(); // Reload to reflect changes
                },
                error: function () {
                    alert('Er is een fout opgetreden bij het toevoegen van de roosterinvoer.');
                }
            });
        });

        // Delete rooster entry via AJAX
        $('.delete-btn').on('click', function () {
            if (confirm('Weet u zeker dat u deze roosterinvoer wilt verwijderen?')) {
                var roosterId = $(this).data('rooster-id');
                $.ajax({
                    url: 'delete_rooster.php',
                    type: 'POST',
                    data: { rooster_id: roosterId },
                    success: function (response) {
                        alert('Roosterinvoer succesvol verwijderd!');
                        location.reload(); // Reload to reflect changes
                    },
                    error: function () {
                        alert('Er is een fout opgetreden bij het verwijderen van de roosterinvoer.');
                    }
                });
            }
        });

        // Open edit modal and populate form with existing data
        function openEditModal(roosterId) {
            var row = $('tr[data-rooster-id="' + roosterId + '"]');
            $('#edit-rooster-id').val(roosterId);
            $('#edit-dag_id').val(row.data('dag-id'));
            $('#edit-vak_id').val(row.data('vak-id'));
            $('#edit-lesblok_id').val(row.data('lesblok-id'));
            $('#edit-lokaal_id').val(row.data('lokaal-id'));
            $('#editModal').modal('show');
        }

        // Edit rooster entry via AJAX
        $('#editForm').on('submit', function (e) {
            e.preventDefault();
            $.ajax({
                url: 'update_rooster.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function (response) {
                    alert('Roosterinvoer succesvol bijgewerkt!');
                    location.reload(); // Reload to reflect changes
                },
                error: function () {
                    alert('Er is een fout opgetreden bij het bijwerken van de roosterinvoer.');
                }
            });
        });
    </script>
</body>
</html>