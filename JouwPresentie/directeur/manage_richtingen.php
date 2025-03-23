<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker is ingelogd en een directeur is
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'directeur') {
    header("Location: ../index.php");
    exit();
}

// Gebruikersgegevens ophalen
$gebruiker_id = $_SESSION['gebruiker_id'];
$sql_persoon = "SELECT p.voornaam, p.naam 
                FROM tgebruiker t 
                JOIN personen p ON t.persoon_id = p.persoon_id 
                WHERE t.gebruiker_id = ?";
$stmt_persoon = $conn->prepare($sql_persoon);
$stmt_persoon->bind_param("i", $gebruiker_id);
$stmt_persoon->execute();
$result_persoon = $stmt_persoon->get_result();

if ($result_persoon->num_rows === 1) {
    $user_persoon = $result_persoon->fetch_assoc();
    $voornaam = $user_persoon['voornaam'] ?? 'Onbekend';
    $naam = $user_persoon['naam'] ?? 'Onbekend';
} else {
    header("Location: ../index.php?error=User not found");
    exit();
}
$stmt_persoon->close();

// Haal alle richtingen op
$sql_richtingen = "SELECT Richting_ID, Richting, Complex FROM richting";
$result_richtingen = $conn->query($sql_richtingen);
if (!$result_richtingen) {
    die("Fout bij ophalen richtingen: " . $conn->error);
}

// Richting aanmaken
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_richting'])) {
    $richting_naam = $_POST['richting_naam'];
    $complex = $_POST['complex'];

    $sql_create = "INSERT INTO richting (Richting, Complex) VALUES (?, ?)";
    $stmt_create = $conn->prepare($sql_create);
    $stmt_create->bind_param("ss", $richting_naam, $complex);
    $stmt_create->execute();
    $stmt_create->close();
    header("Location: manage_richtingen.php");
    exit();
}

// Richting updaten
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_richting'])) {
    $richting_id = $_POST['richting_id'];
    $richting_naam = $_POST['richting_naam'];
    $complex = $_POST['complex'];

    $sql_update = "UPDATE richting SET Richting = ?, Complex = ? WHERE Richting_ID = ?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("ssi", $richting_naam, $complex, $richting_id);
    $stmt_update->execute();
    $stmt_update->close();
    header("Location: manage_richtingen.php");
    exit();
}

// Richting verwijderen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_richting'])) {
    $richting_id = $_POST['delete_richting'];

    $sql_delete = "DELETE FROM richting WHERE Richting_ID = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $richting_id);
    $stmt_delete->execute();
    $stmt_delete->close();
    header("Location: manage_richtingen.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, minimum-scale=0.5">
    <title>Richtingen Beheer</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
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
            font-size: clamp(8px, 1.8vw, 18px);
            width: 100vw;
        }
        .sidebar {
            width: clamp(120px, 18vw, 300px);
            height: 100vh;
            background: rgba(30, 30, 47, 0.95);
            backdrop-filter: blur(20px);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            transition: transform 0.3s ease;
            padding: clamp(8px, 1.5vw, 20px);
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar header {
            text-align: center;
            font-size: clamp(1rem, 2.5vw, 2rem);
            color: #00ff88;
            padding: clamp(8px, 1.5vw, 20px);
            text-shadow: 0 0 10px #00ff88;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: clamp(5px, 1vw, 10px);
            padding: clamp(6px, 1vw, 12px);
            border-radius: 8px;
            transition: background 0.3s ease, transform 0.3s ease;
            font-size: clamp(8px, 1.5vw, 16px);
            white-space: normal;
            word-wrap: break-word;
            overflow-wrap: break-word;
            width: 100%;
        }
        .sidebar a:hover {
            background: rgba(0, 255, 136, 0.2);
            transform: translateX(3px);
        }
        #sidebarToggle {
            position: fixed;
            top: clamp(5px, 1vh, 10px);
            left: clamp(5px, 1vw, 10px);
            z-index: 1001;
            background: rgba(30, 30, 47, 0.9);
            border: none;
            color: white;
            padding: clamp(5px, 1vw, 10px);
            border-radius: 5px;
            cursor: pointer;
            font-size: clamp(10px, 2vw, 18px);
            display: block;
        }
        .sidebar.open {
            transform: translateX(0);
        }
        @media (min-width: 1024px) {
            #sidebarToggle { display: none; }
            .sidebar { transform: translateX(0); }
        }
        @media (max-width: 1023px) {
            .sidebar { transform: translateX(-100%); }
            .main-content { margin-left: 0; width: 100vw; }
        }
        .main-content {
            margin-left: clamp(120px, 18vw, 300px);
            padding: clamp(10px, 2vw, 40px);
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            width: calc(100vw - clamp(120px, 18vw, 300px));
            overflow-x: hidden;
        }
        @media (max-width: 1023px) {
            .main-content { margin-left: 0; width: 100vw; }
        }
        .dashboard-title {
            text-align: center;
            margin-bottom: clamp(6px, 1.5vw, 20px);
            font-size: clamp(1rem, 3.5vw, 3.5rem);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(0, 255, 136, 0.5);
        }
        .welcome-text {
            text-align: center;
            font-size: clamp(0.7rem, 1.8vw, 1.5rem);
            margin-bottom: clamp(10px, 2vw, 40px);
            color: #00ff88;
            text-shadow: 0 0 10px rgba(0, 255, 136, 0.3);
        }
        .section-container {
            margin-top: clamp(6px, 1.5vw, 20px);
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            border-radius: 10px;
            padding: clamp(8px, 1.5vw, 20px);
            box-shadow: 0 8px 32px rgba(0, 255, 136, 0.1);
            width: 100%;
            max-width: clamp(200px, 95vw, 1400px);
            text-align: center;
            overflow-x: hidden;
        }
        h2 {
            font-size: clamp(0.9rem, 2vw, 2rem);
            margin-bottom: clamp(6px, 1.5vw, 20px);
            color: #00ff88;
        }
        h3 {
            font-size: clamp(0.8rem, 1.5vw, 1.5rem);
            margin-bottom: clamp(4px, 1vw, 15px);
            color: #00aaff;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: clamp(6px, 1.5vw, 20px);
            table-layout: fixed;
        }
        table, th, td {
            border: 1px solid #00ff88;
            text-align: left;
            padding: clamp(4px, 0.8vw, 10px);
            font-size: clamp(6px, 1.2vw, 16px);
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        th {
            background-color: #00ff88;
            color: black;
            white-space: normal;
            min-width: clamp(30px, 10vw, 150px);
        }
        td {
            color: #e0e0e0;
            min-width: clamp(30px, 10vw, 150px);
            white-space: normal;
        }
        tr:nth-child(even) {
            background-color: rgba(255, 255, 255, 0.05);
        }
        tr:hover {
            background-color: rgba(0, 255, 136, 0.1);
        }
        button, input[type="text"] {
            padding: clamp(4px, 0.8vw, 12px) clamp(6px, 1vw, 16px);
            background: linear-gradient(45deg, #00ff88, #00aaff);
            border: none;
            border-radius: 5px;
            color: black;
            cursor: pointer;
            transition: background 0.3s ease;
            font-size: clamp(6px, 1.2vw, 16px);
            width: 100%;
            max-width: clamp(60px, 12vw, 150px);
            margin: clamp(2px, 0.5vw, 5px);
        }
        button:hover, input[type="text"]:focus {
            background: linear-gradient(45deg, #00aaff, #00ff88);
            outline: none;
        }
        .delete-btn {
            background: #ff4444;
        }
        .delete-btn:hover {
            background: #ff6666;
        }
        .footer {
            margin-top: clamp(10px, 2vw, 40px);
            padding: clamp(6px, 1vw, 15px);
            text-align: center;
            background: rgba(30, 30, 47, 0.9);
            backdrop-filter: blur(20px);
            width: 100%;
            font-size: clamp(6px, 1.2vw, 14px);
        }
        .footer p {
            color: #00ff88;
            margin: 0;
            text-shadow: 0 0 5px rgba(0, 255, 136, 0.3);
        }
        /* Modal stijlen */
        .modal {
            display: none;
            position: fixed;
            z-index: 1002;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background: rgba(30, 30, 47, 0.95);
            padding: clamp(10px, 2vw, 20px);
            border-radius: 10px;
            width: clamp(200px, 50vw, 400px);
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 255, 136, 0.1);
        }
        .modal-content h3 {
            color: #00ff88;
            margin-bottom: clamp(8px, 1.5vw, 15px);
        }
        .modal-content input {
            display: block;
            margin: clamp(5px, 1vw, 10px) auto;
            width: 80%;
        }
        .modal-content button {
            margin: clamp(5px, 1vw, 10px) auto;
        }
        .close {
            color: #00ff88;
            float: right;
            font-size: clamp(12px, 2vw, 20px);
            cursor: pointer;
        }
        .close:hover {
            color: #00aaff;
        }
    </style>
</head>
<body>
    <button id="sidebarToggle"><i class="fas fa-bars"></i></button>
    <div class="sidebar" id="sidebar">
        <header>Directeur Menu</header>
        <a href="directeur-dashboard.php"><i class="fas fa-qrcode"></i><span>Dashboard</span></a>


        <a href="directeur-dashboard.php" id="roosterButton">
            <i class="fas fa-calendar-alt"></i>
            <span>Rooster Goedkeuring</span>
        </a>
        <a href="directeur-dashboard.php" id="periodeButton">
            <i class="fas fa-clock"></i>
            <span>Periodes</span>
        </a>

        
        <a href="manage_richtingen.php" class="active"><i class="fas fa-map-signs"></i><span>Richtingen Beheer</span></a>
        <a href="../login/logout.php"><i class="fas fa-sign-out-alt"></i><span>Log out</span></a>
    </div>

    <div class="main-content">
        <h1 class="dashboard-title">Richtingen Beheer</h1>
        <div class="welcome-text">Welkom, <?php echo htmlspecialchars($voornaam . ' ' . $naam); ?>!</div>

        <div class="section-container">
            <h2>Richtingen Beheer</h2>

            <!-- Formulier om nieuwe richting aan te maken -->
            <h3>Nieuwe Richting Aanmaken</h3>
            <form action="" method="POST" style="margin-bottom: clamp(10px, 2vw, 20px); display: flex; flex-wrap: wrap; justify-content: center; gap: clamp(5px, 1vw, 10px);">
                <input type="text" name="richting_naam" placeholder="Richting naam" required>
                <input type="text" name="complex" placeholder="Complex" required>
                <button type="submit" name="create_richting">Aanmaken</button>
            </form>

            <!-- Tabel met bestaande richtingen -->
            <?php if ($result_richtingen->num_rows > 0): ?>
                <table>
                    <tr>
                        <th>Richting</th>
                        <th>Complex</th>
                        <th>Acties</th>
                    </tr>
                    <?php while ($row = $result_richtingen->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['Richting']); ?></td>
                            <td><?php echo htmlspecialchars($row['Complex']); ?></td>
                            <td>
                                <button class="edit-btn" data-id="<?php echo $row['Richting_ID']; ?>" data-richting="<?php echo htmlspecialchars($row['Richting']); ?>" data-complex="<?php echo htmlspecialchars($row['Complex']); ?>">Bewerken</button>
                                <form action="" method="POST" style="display: inline;">
                                    <input type="hidden" name="delete_richting" value="<?php echo $row['Richting_ID']; ?>">
                                    <button type="submit" class="delete-btn" onclick="return confirm('Weet je zeker dat je deze richting wilt verwijderen?');">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </table>
            <?php else: ?>
                <p>Geen richtingen beschikbaar.</p>
            <?php endif; ?>
        </div>

        <!-- Modal voor het bewerken -->
        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close">&times;</span>
                <h3>Richting Bewerken</h3>
                <form id="editForm" action="" method="POST">
                    <input type="hidden" name="richting_id" id="modal_richting_id">
                    <input type="text" name="richting_naam" id="modal_richting_naam" placeholder="Richting naam" required>
                    <input type="text" name="complex" id="modal_complex" placeholder="Complex" required>
                    <button type="submit" name="update_richting">Opslaan</button>
                </form>
            </div>
        </div>

     

    <script>
        // Sidebar toggle functionaliteit
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');

        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (event) => {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Modal functionaliteit
        const modal = document.getElementById('editModal');
        const closeBtn = document.querySelector('.close');
        const editButtons = document.querySelectorAll('.edit-btn');

        editButtons.forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                const richting = button.getAttribute('data-richting');
                const complex = button.getAttribute('data-complex');

                document.getElementById('modal_richting_id').value = id;
                document.getElementById('modal_richting_naam').value = richting;
                document.getElementById('modal_complex').value = complex;

                modal.style.display = 'flex';
            });
        });

        closeBtn.addEventListener('click', () => {
            modal.style.display = 'none';
        });

        window.addEventListener('click', (event) => {
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html>
<?php
// Sluit de databaseverbinding aan het einde
$conn->close();
?>