<?php
session_start();

if (isset($_SESSION['gebruiker_id']) && isset($_SESSION['user_name'])) {
    $activePage = 'docenten';
    include('../login/db_connect.php'); // Include your database connection

    // Handle the form submission for adding a new docent
    // Handle the form submission for adding a new docent
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['naam'], $_POST['voornaam'], $_POST['geboortedatum'])) {
        // Get the form data
        $naam = $_POST['naam'];
        $voornaam = $_POST['voornaam'];
        $geboortedatum = $_POST['geboortedatum'];
        $email = $_POST['email'];
        $password = $_POST['password']; // Store the password as plain text
        $role_id = 5; // Assuming role_id for docenten is 3
        $active = isset($_POST['active']) ? 1 : null; // Check if the active checkbox is checked
    
        // Hash the password before storing it
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
        // Prepare the SQL statement for inserting into `personen`
        $stmt1 = $conn->prepare("INSERT INTO personen (naam, voornaam, rol_id, `geboorte_datum`, active) VALUES (?, ?, ?, ?, ?)");
        if (!$stmt1) {
            echo "Error preparing statement: " . $conn->error;
            exit;
        }
        $stmt1->bind_param("ssisi", $naam, $voornaam, $role_id, $geboortedatum, $active);
    
        // Execute the statement for `personen`
        if ($stmt1->execute()) {
            $persoon_id = $stmt1->insert_id;
    
            // Prepare the SQL statement for inserting into `tgebruiker`
            $stmt2 = $conn->prepare("INSERT INTO tgebruiker (email, password, persoon_id) VALUES (?, ?, ?)");
            if (!$stmt2) {
                echo "Error preparing statement: " . $conn->error;
                exit;
            }
            $stmt2->bind_param("ssi", $email, $hashed_password, $persoon_id); // Use hashed password here
    
            // Execute the statement for `tgebruiker`
            if ($stmt2->execute()) {
                // Redirect to the same page to prevent resubmission
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<script>alert('Error: " . $stmt2->error . "');</script>";
            }
        } else {
            echo "<script>alert('Error: " . $stmt1->error . "');</script>";
        }
    
        // Close the statements
        $stmt1->close();
        $stmt2->close();
    }
// Handle the form submission for updating a docent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
    // Get the form data for updating
    $update_id = $_POST['update_id'];
    $update_voornaam = $_POST['update_voornaam']; // Get voornaam
    $update_naam = $_POST['update_naam']; // Get naam
    $update_email = $_POST['update_email'];
    $update_password = $_POST['update_password']; // Get password
    $update_active = isset($_POST['update_active']) ? 1 : null; // Check if the active checkbox is checked

    // Prepare the SQL statement for updating tgebruiker
    if (!empty($update_password)) {
        // Hash the password if it's provided
        $hashed_password = password_hash($update_password, PASSWORD_DEFAULT);
    } else {
        // If no new password is provided, fetch the existing password from the database
        $existing_user_query = $conn->prepare("SELECT password FROM tgebruiker WHERE persoon_id = ?");
        $existing_user_query->bind_param("i", $update_id);
        $existing_user_query->execute();
        $result = $existing_user_query->get_result();
        $existing_user = $result->fetch_assoc();
        $hashed_password = $existing_user['password']; // Use the existing password
    }

    // Update the user information
    $stmt = $conn->prepare("UPDATE tgebruiker SET email = ?, password = ? WHERE persoon_id = ?");
    $stmt->bind_param("ssi", $update_email, $hashed_password, $update_id);

    // Execute the statement
    if ($stmt->execute()) {
        // Prepare the SQL statement for updating personen
        $stmt = $conn->prepare("UPDATE personen SET naam = ?, voornaam = ?, active = ? WHERE persoon_id = ?");
        $stmt->bind_param("ssii", $update_naam, $update_voornaam, $update_active, $update_id);
        if ($stmt->execute()) {
            // Redirect to the same page to prevent resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "<script>alert('Error: " . $stmt->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error: " . $stmt->error . "');</script>";
    }

    // Close the statement
    $stmt->close();
}


  

if (isset($_SESSION['gebruiker_id']) && isset($_SESSION['user_name'])) {
    include('../login/db_connect.php'); // Include your database connection

    // Handle the form submission for deleting a docent
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_id'])) {
        $delete_id = $_POST['delete_id'];

        // Start a transaction
        $conn->begin_transaction();

        try {
            // Step 1: Delete from the child table (`tgebruiker`)
            $stmt1 = $conn->prepare("DELETE FROM tgebruiker WHERE persoon_id = ?");
            $stmt1->bind_param("i", $delete_id);

            if (!$stmt1->execute()) {
                throw new Exception("Error deleting from tgebruiker: " . $stmt1->error);
            }

            // Step 2: Delete from the parent table (`personen`)
            $stmt2 = $conn->prepare("DELETE FROM personen WHERE persoon_id = ?");
            $stmt2->bind_param("i", $delete_id);

            if (!$stmt2->execute()) {
                throw new Exception("Error deleting from personen: " . $stmt2->error);
            }

            // Commit the transaction if both deletions succeed
            $conn->commit();

            // Redirect to the same page to prevent resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } catch (Exception $e) {
            // Rollback the transaction if any error occurs
            $conn->rollback();
            echo "<script>alert('Error: " . $e->getMessage() . "');</script>";
        } finally {
            // Close the statements
            if (isset($stmt1)) $stmt1->close();
            if (isset($stmt2)) $stmt2->close();
        }
    }
}

// Fetch total docenten count
$totalodResult = $conn->query("SELECT COUNT(*) as total FROM personen WHERE rol_id = 5");
$totalod = $totalodResult->fetch_assoc()['total'];

// Fetch online docenten count
$onlineodResult = $conn->query("SELECT COUNT(*) as online FROM personen WHERE rol_id = 5 AND active = 1");
$onlineod = $onlineodResult->fetch_assoc()['online'];

// Fetch offline docenten count
$offlineod = $totalod - $onlineod;

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

/* Style the password field container */
.input-group div {
    position: relative;
}

/* Style the eye icon button */
#togglePassword {
    background: none;
    border: none;
    cursor: pointer;
    padding: 0;
    outline: none;
}

/* Style the eye icon */
#togglePassword i {
    color: #666; /* Icon color */
    font-size: 16px; /* Icon size */
}

/* Hover effect for the eye icon */
#togglePassword:hover i {
    color: #333; /* Darker icon color on hover */
}






.light {
    display: inline-block;
    width: 15px; /* Adjust size as needed */
    height: 15px; /* Adjust size as needed */
    border-radius: 50%; /* Makes it circular */
    margin-right: 5px; /* Space between light and text */
}

.green {
    background-color: green; /* Color for online */
}

.red {
    background-color: red; /* Color for offline */
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

.sidebar a.active {
    background: rgba(0, 255, 136, 0.2);
    color: #00ff88;
    box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
}

.sidebar a i {
    font-size: 1.2rem;
}

/* Sidebar toggle voor kleine schermen */
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
}

@media (max-width: 768px) {
    .main-content {
        margin-left: 0;
    }
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

/* Docent Table */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background: rgba(30, 30, 47, 0.9);
    border-radius: 10px;
    overflow: hidden;
}

th, td {
    padding: 15px;
    text-align: left;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
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
    padding: 10px 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.btn:hover {
    background: #00aaff;
}

/* Modal styles */
.modal {
    display : none;
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
    width: 80%;
    border-radius: 10px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
    animation: fadeIn 0.5s;
}

@keyframes fadeIn {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}

.card-container {
    display: flex;
    justify-content: space-around;
    margin-top: 20px;
}

.card {
    background: rgba(0, 255, 136, 0.2);
    padding: 20px;
    border-radius: 10px;
    flex: 1;
    margin: 10px;
    text-align: center;
    animation: glow 1.5s infinite alternate;
}

@keyframes glow {
    0% {
        box-shadow: 0 0 10px rgba(0, 255, 136, 0.5);
    }
    100% {
        box-shadow: 0 0 20px rgba(0, 255, 136, 1);
    }
}

.card h3 {
    font-size: 1.5rem;
    margin-bottom: 10px;
    color: #00ff88;
}

.card p {
    font-size: 2rem;
    font-weight: bold;
    color: white;
}





.close {
    color: #aaa;
    float: right;
    font-size: 28px;
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
}

.input-group input[type="text"],
.input-group input[type="email"],
.input-group input[type="password"],
.input-group input[type="date"] {
    width: 100%;
    padding: 10px;
    border: 1px solid #333;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    font-size: 16px;
    color: #333;
    background-color: #333;
    color: #fff;
    transition: border-color 0.3s;
}

.input-group input[type="text"]:focus,
.input-group input[type="email"]:focus,
.input-group input[type="password"]:focus,
.input-group input[type="date"]:focus {
    border-color: #aaa;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
}

.input-group input[type="checkbox"] {
    margin: 10px;
}

/* Modal title styles */
.modal-title {
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    color: #333;
    text-align: center;
    animation: fadeIn 0.5s;
}
    </style>
</head>
<body>
    <!-- Sidebar Toggle Knop -->
    <button id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

   <!-- Sidebar -->
<<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <header>systeembeheer Menu</header>
    <a href="systeembeheer-dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : ''; ?>">
        <i class="fas fa-tachometer-alt"></i> <!-- Dashboard icon -->
        <span>Dashboard</span>
    </a>
    <a href="systeembeheer-docenten.php" class="<?= ($activePage == 'docenten') ? 'active' : ''; ?>">
        <i class="fas fa-chalkboard-teacher"></i> <!-- Docenten icon -->
        <span>Docenten</span>
    </a>
    <a href="systeembeheer-studenten.php" class="<?= ($activePage == 'studenten') ? 'active' : ''; ?>">
        <i class="fas fa-users"></i> <!-- Studenten icon -->
        <span>Studenten</span>
    </a>
    <a href="systeembeheer-systeembeheerder.php" class="<?= ($activePage == 'systeembeheerder') ? 'active' : ''; ?>">
        <i class="fas fa-tools"></i> <!-- Systeembeheerder icon -->
        <span>Systeembeheerder</span>
    </a>
    <a href="systeembeheer-directeur.php" class="<?= ($activePage == 'directeur') ? 'active' : ''; ?>">
        <i class="fas fa-user-tie"></i> <!-- Directeur icon -->
        <span>Directeur</span>
    </a>
    <a href="systeembeheer-Richtingcoordinator.php" class="<?= ($activePage == 'Richtingcoordinator') ? 'active' : ''; ?>">
        <i class="fas fa-user-cog"></i> <!-- Richtingcoordinator icon -->
        <span>Richtingcoordinator</span>
    </a>
    <a href="systeembeheer_od.php" class="<?= ($activePage == 'od') ? 'active' : ''; ?>">
        <i class="fas fa-user-shield"></i> <!-- Onderdirecteur icon -->
        <span>Onderdirecteur</span>
    </a>
    <a href="../login/logout.php">
        <i class="fas fa-sign-out-alt"></i> <!-- Logout icon -->
        <span>Log out</span>
    </a>
</div>

    <!-- Main Content -->
   

        <div class="main-content">
    <h1 class="dashboard-title">Onderdirecteur Management</h1>

   



        <button class="btn" id="addDocentBtn">Add Onderdirecteur</button>

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
// Fetch all docenten from the database
$result = $conn->query("SELECT p.persoon_id, p.naam, p.voornaam, t.email, t.password, p.active FROM personen p JOIN tgebruiker t ON p.persoon_id = t.persoon_id WHERE p.rol_id = 5");
while ($od = $result->fetch_assoc()) {
    // Determine the status for the light indicator
    $statusClass = $od['active'] ? 'green' : 'red'; // 'green' for online, 'red' for offline
    echo "<tr>
        <td>{$od['naam']}</td>
        <td>{$od['voornaam']}</td>
        <td>{$od['email']}</td>
        <td>
            <span class='light $statusClass'></span> " . ($od['active'] ? 'Yes' : 'No') . "
        </td>
        <td>
            <button class='btn' onclick='openUpdateModal({$od['persoon_id']}, \"{$od['naam']}\", \"{$od['voornaam']}\", \"{$od['email']}\", \"{$od['password']}\", {$od['active']})'>Update</button>
            <form method='POST' style='display:inline;'>
                <input type='hidden' name='delete_id' value='{$od['persoon_id']}'>
                <button type='submit' class='btn' onclick=\"return confirm('Are you sure you want to delete this docent?');\">Delete</button>
            </form>
        </td>
    </tr>";
}
?>
    </tbody>





</table>

        <div class="card-container" style="display: flex; justify-content: space-around; margin-top: 20px;">
    <div class="card">
        <h3>Total Onderdirecteur</h3>
        <p><?php echo $totalod; ?></p>
    </div>
    <div class="card">
        <h3>Online Onderdirecteur</h3>
        <p><?php echo $onlineod; ?></p>
    </div>
    <div class="card">
        <h3>Offline Onderdirecteur</h3>
        <p><?php echo $offlineod; ?></p>
    </div>
</div>

    <div id="addDocentModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeAddModal">&times;</span>
        <h2 class="modal-title">Add Onderdirecteur</h2>
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
                <button type="submit" class="btn">Add Onderdirecteur</button>
            </form>
        </div>
    </div>
</div>

  <!-- Update Docent Modal -->
<!-- Update Docent Modal -->
<div id="updateDocentModal" class="modal">
    <div class="modal-content">
        <span class="close" id="closeUpdateModal">&times;</span>
        <h2 class="modal-title">Update Onderdirecteur</h2>
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
                            <i class="fas fa-eye"></i> <!-- FontAwesome eye icon -->
                        </button>
                    </div>
                </div>
                <div class="input-group">
                    <label for="update_active">Active:</label>
                    <input type="checkbox" name="update_active" id="update_active">
                </div>
                <button type="submit" class="btn">Update Onderdirecteur</button>
            </form>
        </div>
    </div>
</div>


    <script>
        // Sidebar toggle functionality
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Close modal functionality
        const closeAddModal = document.getElementById('closeAddModal');
        const closeUpdateModal = document.getElementById('closeUpdateModal');
        const addDocentModal = document.getElementById('addDocentModal');
        const updateDocentModal = document.getElementById('updateDocentModal');

        closeAddModal.onclick = () => {
            addDocentModal.style.display = "none";
        }

        closeUpdateModal.onclick = () => {
            updateDocentModal.style.display = "none";
        }

        // Show add docent modal
        document.getElementById('addDocentBtn').onclick = () => {
            addDocentModal.style.display = "block";
        }

    function openUpdateModal(id, naam, voornaam, email, password, active) {
    // Set the values in the modal form
    document.getElementById('update_id').value = id;
    document.getElementById('update_voornaam').value = voornaam; // Set voornaam
    document.getElementById('update_naam').value = naam; // Set naam
    document.getElementById('update_email').value = email;
    document.getElementById('update_password').value = password; // Set original password
    document.getElementById('update_active').checked = active;

    // Display the modal
    updateDocentModal.style.display = "block";
}

// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function () {
    const passwordField = document.getElementById('update_password');
    const icon = this.querySelector('i');

    // Toggle the type of the password field
    if (passwordField.type === 'password') {
        passwordField.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash'); // Change icon to "eye-slash"
    } else {
        passwordField.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye'); // Change icon back to "eye"
    }
});

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            if (event.target == addDocentModal) {
                addDocentModal.style.display = "none";
            }
            if (event.target == updateDocentModal) {
                updateDocentModal.style.display = "none";
            }
        }
    </script>
</body>
</html>

<?php
} else {
    header("Location: index.php");
    exit();
}
?>