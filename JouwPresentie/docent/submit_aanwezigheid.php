<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and is a docent
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: ../index.php");
    exit();
}

// Validate and sanitize input data
$klas_id = isset($_POST['klas_id']) ? (int)$_POST['klas_id'] : 0;
$vak_id = isset($_POST['vak_id']) ? (int)$_POST['vak_id'] : 0;
$periode_id = isset($_POST['periode_id']) ? (int)$_POST['periode_id'] : 0;
$Jaar_id = isset($_POST['Jaar_id']) ? (int)$_POST['Jaar_id'] : 0;
$Richting_ID = isset($_POST['Richting_ID']) ? (int)$_POST['Richting_ID'] : 0;
$dag_id = isset($_POST['dag_id']) ? (int)$_POST['dag_id'] : 0;
$lokaal_id = isset($_POST['lokaal_id']) ? (int)$_POST['lokaal_id'] : 0;
$datum = isset($_POST['datum']) ? $conn->real_escape_string($_POST['datum']) : '';
$aanwezigheid = isset($_POST['aanwezigheid']) ? $_POST['aanwezigheid'] : [];

// Validate required fields
if (
    empty($klas_id) || empty($vak_id) || empty($periode_id) ||
    empty($Jaar_id) || empty($Richting_ID) || empty($dag_id) ||  empty($lokaal_id) ||
    empty($datum) || empty($aanwezigheid)
) {
    header("Location: docent-aanwezigheid.php?error=missing_data");
    exit();
}

// Prepare SQL statement to insert attendance records into the `presentie` table
$sql_insert = "INSERT INTO presentie (persoon_id, klas_id, vak_id, periode_id, Jaar_id, Richting_ID, dag_id, lokaal_id, datum, status_id) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
$stmt_insert = $conn->prepare($sql_insert);

if (!$stmt_insert) {
    header("Location: docent-aanwezigheid.php?error=database_error");
    exit();
}

// Loop through each student's attendance data and insert it into the `presentie` table
foreach ($aanwezigheid as $persoon_id => $status_id) {
    $persoon_id = (int)$persoon_id;
    $status_id = (int)$status_id;

    // Bind parameters and execute the query
    $stmt_insert->bind_param(
        "iiiiiiiisi",
        $persoon_id,
        $klas_id,
        $vak_id,
        $periode_id,
        $Jaar_id,
        $Richting_ID,
        $dag_id,
        $lokaal_id, // Voeg lokaal_id toe
        $datum,
        $status_id
    );

    if (!$stmt_insert->execute()) {
        header("Location: docent-aanwezigheid.php?error=insert_failed");
        exit();
    }
}

// Close the statement
$stmt_insert->close();

// Redirect back to the attendance page with success message
header("Location: docent-aanwezigheid.php?success=attendance_saved");
exit();
?>