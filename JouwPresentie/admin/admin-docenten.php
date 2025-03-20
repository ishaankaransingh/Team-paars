<?php
session_start();

if (isset($_SESSION['gebruiker_id']) && isset($_SESSION['user_name'])) {
    $activePage = 'docenten';
    include('../login/db_connect.php'); // Include your database connection

    // Handle the form submission for adding a new docent
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['naam'], $_POST['voornaam'], $_POST['geboortedatum'])) {
        $naam = $_POST['naam'];
        $voornaam = $_POST['voornaam'];
        $geboortedatum = $_POST['geboortedatum'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $role_id = 3;
        $active = isset($_POST['active']) ? 1 : null;

        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $stmt1 = $conn->prepare("INSERT INTO personen (naam, voornaam, rol_id, `geboorte_datum`, active) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt1) {
            echo "Error preparing statement: " . $conn->error;
            exit;
        }
        $stmt1->bind_param("ssisi", $naam, $voornaam, $role_id, $geboortedatum, $active);

        if ($stmt1->execute()) {
            $persoon_id = $stmt1->insert_id;

            $stmt2 = $conn->prepare("INSERT INTO tgebruiker (email, password, persoon_id) VALUES (?, ?, ?)");
            if (!$stmt2) {
                echo "Error preparing statement: " . $conn->error;
                exit;
            }
            $stmt2->bind_param("ssi", $email, $hashed_password, $persoon_id);

            if ($stmt2->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt2->error . "');</script>";
            }
        } else {
            echo "<script>alert('Error: " . $stmt1->error . "');</script>";
        }

        $stmt1->close();
        $stmt2->close();
    }

    // Handle the form submission for updating a docent
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
        $update_id = $_POST['update_id'];
        $update_voornaam = $_POST['update_voornaam'];
        $update_naam = $_POST['update_naam'];
        $update_email = $_POST['update_email'];
        $update_password = $_POST['update_password'];
        $update_active = isset($_POST['update_active']) ? 1 : null;

        if (!empty($update_password)) {
            $hashed_password = password_hash($update_password, PASSWORD_DEFAULT);
        } else {
            $existing_user_query = $conn->prepare("SELECT password FROM tgebruiker WHERE persoon_id = ?");
            $existing_user_query->bind_param("i", $update_id);
            $existing_user_query->execute();
            $result = $existing_user_query->get_result();
            $existing_user = $result->fetch_assoc();
            $hashed_password = $existing_user['password'];
        }

        $stmt = $conn->prepare("UPDATE tgebruiker SET email = ?, password = ? WHERE persoon_id = ?");
        $stmt->bind_param("ssi", $update_email, $hashed_password, $update_id);

        if ($stmt->execute()) {
            $stmt = $conn->prepare("UPDATE personen SET naam = ?, voornaam = ?, active = ? WHERE persoon_id = ?");
            $stmt->bind_param("ssii", $update_naam, $update_voornaam, $update_active, $update_id);
            if ($stmt->execute()) {
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt->error . "');</script>";
            }
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }

        $stmt->close();
    }

    // Handle the form submission for deleting a docent
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        $conn->begin_transaction();

        try {
            $stmt1 = $conn->prepare("DELETE FROM tgebruiker WHERE persoon_id = ?");
            $stmt1->bind_param("i", $delete_id);

            if (!$stmt1->execute()) {
                throw new Exception("Error deleting from tgebruiker: " . $stmt1->error);
            }

            $stmt2 = $conn->prepare("DELETE FROM personen WHERE persoon_id = ?");
            $stmt2->bind_param("i", $delete_id);

            if (!$stmt2->execute()) {
                throw new Exception("Error deleting from personen: " . $stmt2->error);
            }

            $conn->commit();
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            $conn->rollback();
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        } finally {
            if (isset($stmt1)) $stmt1->close();
            if (isset($stmt2)) $stmt2->close();
        }
    }

    // Fetch counts
    $totalDocentenResult = $conn->query("SELECT COUNT(*) as total FROM personen WHERE rol_id = 3");
    $totalDocenten = $totalDocentenResult->fetch_assoc()['total'];

    $onlineDocentenResult = $conn->query("SELECT COUNT(*) as online FROM personen WHERE rol_id = 3 AND active = 1");
    $onlineDocenten = $onlineDocentenResult->fetch_assoc()['online'];

    $offlineDocenten = $totalDocenten - $onlineDocenten;
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Docenten Management</title>
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
        }

        .sidebar header {
            text-align: center;
            font-size: clamp(1.2rem, 4vw, 1.5rem);
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
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .sidebar a:hover {
            background: rgba(0, 255, 136, 0.1);
            transform: translateX(5px);
        }

        .sidebar a.active {
            background: rgba(0, 255, 136, 0.2);
            color: #00ff88;
            box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
        }

        .sidebar a i {
            font-size: clamp(1rem, 3vw, 1.2rem);
        }

        /* Sidebar toggle */
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

        /* Main content */
        .main-content {
            margin-left: 250px;
            padding: 20px;
            transition: margin 0.3s ease;
            width: calc(100% - 250px);
        }

        /* Titel */
        .dashboard-title {
            text-align: center;
            margin: 1rem 0;
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.3);
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background: rgba(30, 30, 47, 0.9);
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: clamp(10px, 2vw, 15px);
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: clamp(0.8rem, 2.5vw, 1rem);
        }

        th {
            background: rgba(0, 255, 136, 0.2);
        }

        tr:hover {
            background: rgba(0, 255, 136, 0.1);
        }

        /* Button styles */
        .btn {
            background: #00ff88;
            color: white;
            border: none;
            padding: clamp(8px, 2vw, 10px) clamp(10px, 3vw, 15px);
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: clamp(0.8rem, 2.5vw, 1rem);
        }

        .btn:hover {
            background: #00aaff;
        }

        /* Card container */
        .card-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            margin-top: 20px;
            gap: 10px;
        }

        .card {
            background: rgba(0, 255, 136, 0.2);
            padding: 20px;
            border-radius: 10px;
            flex: 1;
            min-width: 150px;
            margin: 10px;
            text-align: center;
            animation: glow 1.5s infinite alternate;
        }

        @keyframes glow {
            0% { box-shadow: 0 0 10px rgba(0, 255, 136, 0.5); }
            100% { box-shadow: 0 0 20px rgba(0, 255, 136, 1); }
        }

        .card h3 {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            margin-bottom: 10px;
            color: #00ff88;
        }

        .card p {
            font-size: clamp(1.5rem, 4vw, 2rem);
            font-weight: bold;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0, 0, 0, 0.7);
            padding-top: 60px;
        }

        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 20px;
            border: 1px solid #333;
            width: min(90%, 500px);
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.5s;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .close {
            color: #aaa;
            float: right;
            font-size: clamp(1.5rem, 4vw, 28px);
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }

        /* Form styles */
        .form-container {
            padding: 20px;
        }

        .input-group {
            margin-bottom: 20px;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
            color: #333;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
        }

        .input-group input[type="text"],
        .input-group input[type="email"],
        .input-group input[type="password"],
        .input-group input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #333;
            border-radius: 5px;
            font-size: clamp(0.9rem, 2.5vw, 1rem);
            background-color: #333;
            color: #fff;
            transition: border-color 0.3s;
        }

        .input-group input[type="checkbox"] {
            margin: 10px;
        }

        /* Status lights */
        .light {
            display: inline-block;
            width: clamp(10px, 2vw, 15px);
            height: clamp(10px, 2vw, 15px);
            border-radius: 50%;
            margin-right: 5px;
        }

        .green { background-color: green; }
        .red { background-color: red; }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            #sidebarToggle { display: block; }

            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                width: 100%;
            }

            table {
                display: block;
                overflow-x: auto;
                white-space: nowrap;
            }

            th, td {
                min-width: 100px;
            }

            .btn {
                width: 100%;
                margin: 5px 0;
            }
        }

        @media (max-width: 480px) {
            .card {
                min-width: 100%;
            }

            .dashboard-title {
                font-size: clamp(1.2rem, 4vw, 2rem);
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Knop -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <header>Admin Menu</header>
        <a href="admin-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        <a href="admin-docenten.php" class="<?= ($activePage == 'docenten') ? 'active' : ''; ?>">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>Docenten</span>
        </a>
        <a href="admin-studenten.php" class="<?= ($activePage == 'studenten') ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Studenten</span>
        </a>
        <a href="admin-systeembeheerder.php" class="<?= ($activePage == 'systeembeheerder') ? 'active' : ''; ?>">
            <i class="fas fa-tools"></i>
            <span>Systeembeheerder</span>
        </a>
        <a href="admin-directeur.php" class="<?= ($activePage == 'directeur') ? 'active' : ''; ?>">
            <i class="fas fa-user-tie"></i>
            <span>Directeur</span>
        </a>
        <a href="admin-Richtingcoordinator.php" class="<?= ($activePage == 'Richtingcoordinator') ? 'active' : ''; ?>">
            <i class="fas fa-user-cog"></i>
            <span>Richtingcoordinator</span>
        </a>
        <a href="admin_od.php" class="<?= ($activePage == 'od') ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i>
            <span>Onderdirecteur</span>
        </a>
        <a href="../login/logout.php">
            <i class="fas fa-sign-out-alt"></i>
            <span>Log out</span>
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <h1 class="dashboard-title">Docent Management</h1>
        <button class="btn" id="addDocentBtn">Add Docent</button>

        <!-- Docent Table -->
        <table>
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Voornaam</th>
                    <th>Email</th>
                    <th>Active</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="docentTableBody">
                <?php
                $result = $conn->query("SELECT p.persoon_id, p.naam, p.voornaam, t.email, t.password, p.active FROM personen p JOIN tgebruiker t ON p.persoon_id = t.persoon_id WHERE p.rol_id = 3");
                while ($docent = $result->fetch_assoc()) {
                    $statusClass = $docent['active'] ? 'green' : 'red';
                    echo "<tr>
                        <td>{$docent['naam']}</td>
                        <td>{$docent['voornaam']}</td>
                        <td>{$docent['email']}</td>
                        <td>
                            <span class='light $statusClass'></span> " . ($docent['active'] ? 'Yes' : 'No') . "
                        </td>
                        <td>
                            <button class='btn' onclick='openUpdateModal({$docent['persoon_id']}, \"{$docent['naam']}\", \"{$docent['voornaam']}\", \"{$docent['email']}\", \"{$docent['password']}\", {$docent['active']})'>Update</button>
                            <form method='POST' style='display:inline;'>
                                <input type='hidden' name='delete_id' value='{$docent['persoon_id']}'>
                                <button type='submit' class='btn' onclick=\"return confirm('Are you sure you want to delete this docent?');\">Delete</button>
                            </form>
                        </td>
                    </tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- Cards -->
        <div class="card-container">
            <div class="card">
                <h3>Total Docenten</h3>
                <p><?php echo $totalDocenten; ?></p>
            </div>
            <div class="card">
                <h3>Online Docenten</h3>
                <p><?php echo $onlineDocenten; ?></p>
            </div>
            <div class="card">
                <h3>Offline Docenten</h3>
                <p><?php echo $offlineDocenten; ?></p>
            </div>
        </div>

        <!-- Add Docent Modal -->
        <div id="addDocentModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeAddModal">×</span>
                <h2 class="modal-title">Add Docent</h2>
                <div class="form-container">
                    <form id="addDocentForm" method="POST">
                        <div class="input-group">
                            <label for="naam">Naam:</label>
                            <input type="text" name="naam" required>
                        </div>
                        <div class="input-group">
                            <label for="voornaam">Voornaam:</label>
                            <input type="text" name="voornaam" required>
                        </div>
                        <div class="input-group">
                            <label for="geboortedatum">Geboortedatum:</label>
                            <input type="date" name="geboortedatum" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email:</label>
                            <input type="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Password:</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="input-group">
                            <label for="active">Active:</label>
                            <input type="checkbox" name="active" checked>
                        </div>
                        <button type="submit" class="btn">Add Docent</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Update Docent Modal -->
        <div id="updateDocentModal" class="modal">
            <div class="modal-content">
                <span class="close" id="closeUpdateModal">×</span>
                <h2 class="modal-title">Update Docent</h2>
                <div class="form-container">
                    <form id="updateDocentForm" method="POST">
                        <input type="hidden" name="update_id" id="update_id">
                        <div class="input-group">
                            <label for="update_voornaam">Voornaam:</label>
                            <input type="text" name="update_voornaam" id="update_voornaam" required>
                        </div>
                        <div class="input-group">
                            <label for="update_naam">Naam:</label>
                            <input type="text" name="update_naam" id="update_naam" required>
                        </div>
                        <div class="input-group">
                            <label for="update_email">Email:</label>
                            <input type="email" name="update_email" id="update_email" required>
                        </div>
                        <div class="input-group">
                            <label for="update_password">Password:</label>
                            <div style="position: relative;">
                                <input type="password" name="update_password" id="update_password" required>
                                <button type="button" id="togglePassword" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer;">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="update_active">Active:</label>
                            <input type="checkbox" name="update_active" id="update_active">
                        </div>
                        <button type="submit" class="btn">Update Docent</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const addDocentModal = document.getElementById('addDocentModal');
        const updateDocentModal = document.getElementById('updateDocentModal');
        const closeAddModal = document.getElementById('closeAddModal');
        const closeUpdateModal = document.getElementById('closeUpdateModal');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.getElementById('addDocentBtn').onclick = () => {
            addDocentModal.style.display = "block";
        };

        closeAddModal.onclick = () => {
            addDocentModal.style.display = "none";
        };

        closeUpdateModal.onclick = () => {
            updateDocentModal.style.display = "none";
        };

        function openUpdateModal(id, naam, voornaam, email, password, active) {
            document.getElementById('update_id').value = id;
            document.getElementById('update_voornaam').value = voornaam;
            document.getElementById('update_naam').value = naam;
            document.getElementById('update_email').value = email;
            document.getElementById('update_password').value = password;
            document.getElementById('update_active').checked = active;
            updateDocentModal.style.display = "block";
        }

        document.getElementById('togglePassword').addEventListener('click', function () {
            const passwordField = document.getElementById('update_password');
            const icon = this.querySelector('i');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });

        window.onclick = function(event) {
            if (event.target == addDocentModal) {
                addDocentModal.style.display = "none";
            }
            if (event.target == updateDocentModal) {
                updateDocentModal.style.display = "none";
            }
        };
    </script>
</body>
</html>

<?php
} else {
    header("Location: index.php");
    exit();
}
?>