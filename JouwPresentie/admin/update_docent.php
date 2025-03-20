<?php
// Database connection
$host = 'localhost';
$db = 'aanwezigheids_db';
$user = 'root';
$pass = ''; // No password

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle the form submission for updating a docent
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_id'])) {
    // Get the form data
    $update_id = $_POST['update_id'];
    $naam = $_POST['naam'];
    $voornaam = $_POST['voornaam'];
    $geboortedatum = $_POST['geboortedatum'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Store the password as plain text
    $active = isset($_POST['active']) ? 1 : 0; // Check if the active checkbox is checked

    // Prepare the SQL statement for updating the `personen` table
    $stmt1 = $conn->prepare("UPDATE personen SET naam = ?, voornaam = ?, `geboorte_datum` = ?, active = ? WHERE persoon_id = ?");
    if (!$stmt1) {
        die("Error preparing statement: " . $conn->error);
    }
    $stmt1->bind_param("sssii", $naam, $voornaam, $geboortedatum, $active, $update_id);

    // Execute the statement for `personen`
    if ($stmt1->execute()) {
        // Prepare the SQL statement for updating the `tgebruiker` table
        $stmt2 = $conn->prepare("UPDATE tgebruiker SET email = ?, password = ? WHERE persoon_id = ?");
        if (!$stmt2) {
            die("Error preparing statement: " . $conn->error);
        }
        $stmt2->bind_param("ssi", $email, $password, $update_id);

        // Execute the statement for `tgebruiker`
        if ($stmt2->execute()) {
            // Redirect to the same page to prevent resubmission
            header("Location: " . $_SERVER['PHP_SELF'] . "?id=" . $update_id);
            exit();
        } else {
            echo "<script>alert('Error updating tgebruiker: " . $stmt2->error . "');</script>";
        }
    } else {
        echo "<script>alert('Error updating personen: " . $stmt1->error . "');</script>";
    }

    // Close the statements
    $stmt1->close();
    $stmt2->close();
}

// Fetch the docent's details based on the ID passed in the URL
if (isset($_GET['id'])) {
    $docent_id = $_GET['id'];
    $query = "SELECT p.persoon_id, p.naam, p.voornaam, p.geboorte_datum, t.email, p.active 
              FROM personen p 
              JOIN tgebruiker t ON p.persoon_id = t.persoon_id 
              WHERE p.persoon_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $docent_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $docent = $result->fetch_assoc();
    $stmt->close();
} else {
    // If no ID is provided, redirect back to the docenten management page
    header("Location: docenten_management.php"); // Change this to your desired redirect page
    exit();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Docent</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            padding: 20px;
        }
        .form-container {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        .input-group {
            margin-bottom: 15px;
        }
        .input-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .input-group input[type="text"],
        .input-group input[type="email"],
        .input-group input[type="password"],
        .input-group input[type="date"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .input-group input[type="checkbox"] {
            margin-right: 10px;
        }
        .btn {
            background: #00ff88;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        }
        .btn:hover {
            background: #00aaff;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Update Docent</h2>
        <form method="POST" action="">
            <input type="hidden" name="update_id" value="<?= $docent['persoon_id'] ?>">
            <div class="input-group">
                <label for="naam">Naam:</label>
                <input type="text" name="naam" value="<?= $docent['naam'] ?>" required>
            </div>
            <div class="input-group">
                <label for="voornaam">Voornaam:</label>
                <input type="text" name="voornaam" value="<?= $docent['voornaam'] ?>" required>
            </div>
            <div class="input-group">
                <label for="geboortedatum">Geboortedatum:</label>
                <input type="date" name="geboortedatum" value="<?= $docent['geboorte_datum'] ?>" required>
            </div>
            <div class="input-group">
                <label for="email">Email:</label>
                <input type="email" name="email" value="<?= $docent['email'] ?>" required>
            </div>
            <div class="input-group">
                <label for="password">Password:</label>
                <input type="password" name="password" placeholder="Leave blank to keep current password">
            </div>
            <div class="input-group">
                <label for="active">Active:</label>
                <input type="checkbox" name="active" <?= $docent['active'] ? 'checked' : '' ?>>
            </div>
            <button type="submit" class="btn">Update Docent</button>
        </form>
    </div>
</body>
</html>