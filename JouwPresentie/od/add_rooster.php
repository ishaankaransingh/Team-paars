<?php
session_start();
include('../login/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Haal de gegevens op uit het formulier
    $dag_id = $_POST['dag_id'];
    $klas_id = $_POST['klas_id'];
    $vak_id = $_POST['vak_id'];
    $lesblok_id = $_POST['lesblok_id'];
    $lokaal_id = $_POST['lokaal_id']; // Voeg lokaal_id toe

    // Voeg de nieuwe roosterinvoer toe aan de database
    $sql_insert = "
        INSERT INTO roosterstud (dag_id, klas_id, vak_id, lesblok_id, lokaal_id)
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        die("Query preparation failed: " . $conn->error);
    }

    // Bind de parameters aan de query
    $stmt_insert->bind_param("iiiii", $dag_id, $klas_id, $vak_id, $lesblok_id, $lokaal_id);

    // Voer de query uit
    if ($stmt_insert->execute()) {
        echo "Roosterinvoer succesvol toegevoegd!";
    } else {
        echo "Er is een fout opgetreden bij het toevoegen van de roosterinvoer: " . $stmt_insert->error;
    }

    // Sluit de statement
    $stmt_insert->close();
} else {
    echo "Ongeldig verzoek.";
}

// Sluit de databaseverbinding
$conn->close();
?>