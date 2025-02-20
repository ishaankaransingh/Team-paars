<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and is a docent
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $klas_id = $_POST['klas_id'];
    $vak_id = $_POST['vak_id'];
    $periode_id = $_POST['periode_id'];
    $Jaar_id = $_POST['Jaar_id'];
    $Richting_ID = $_POST['Richting_ID'];
    $dag = $_POST['dag'];
    $datum = $_POST['datum'];
    $aanwezigheid = $_POST['aanwezigheid'];

    foreach ($aanwezigheid as $persoon_id => $status_id) {
        // Insert into presentie table
        $sql_insert = "INSERT INTO presentie (persoon_id, vak_id, klas_id, status_id, periode_id, Jaar_id, datum, dag, Richting_ID)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("iiiiissss", $persoon_id, $vak_id, $klas_id, $status_id, $periode_id, $Jaar_id, $datum, $dag, $Richting_ID);
        $stmt_insert->execute();
    }

    header("Location: docent-aanwezigheid.php?success=Aanwezigheid opgeslagen");
    exit();
}
?>