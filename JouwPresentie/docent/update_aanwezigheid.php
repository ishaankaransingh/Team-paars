<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

// Check if user is logged in and is a docent
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'docent') {
    header("Location: ../index.php");
    exit();
}

// Validate and sanitize input
$presentie_id = isset($_POST['presentie_id']) ? (int)$_POST['presentie_id'] : 0;
$status_id = isset($_POST['status_id']) ? (int)$_POST['status_id'] : 0;

if (empty($presentie_id) || empty($status_id)) {
    header("Location: docent-aanwezigheid-overzicht.php?error=missing_data");
    exit();
}

// Update the attendance record
$sql_update = "UPDATE presentie SET status_id = ? WHERE presentie_id = ?";
$stmt_update = $conn->prepare($sql_update);
$stmt_update->bind_param("ii", $status_id, $presentie_id);

if ($stmt_update->execute()) {
    header("Location: docent-aanwezigheid-overzicht.php?success=status_updated");
} else {
    header("Location: docent-aanwezigheid-overzicht.php?error=update_failed");
}

$stmt_update->close();
$conn->close();
?>