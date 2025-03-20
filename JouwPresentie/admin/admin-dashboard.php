<?php
session_start();
include('../login/db_connect.php'); // Include je databaseverbinding

// Controleer of de gebruiker is ingelogd en een admin is
if (!isset($_SESSION['gebruiker_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit();
}

// Gebruikersgegevens ophalen
$gebruiker_id = $_SESSION['gebruiker_id'];

// Persoon_id, voornaam en naam van de ingelogde gebruiker ophalen
$sql_persoon = "SELECT p.persoon_id, p.voornaam, p.naam 
                FROM tgebruiker t 
                JOIN personen p ON t.persoon_id = p.persoon_id 
                WHERE t.gebruiker_id = ?";
$stmt_persoon = $conn->prepare($sql_persoon);
$stmt_persoon->bind_param("i", $gebruiker_id);
$stmt_persoon->execute();
$result_persoon = $stmt_persoon->get_result();

if ($result_persoon->num_rows === 1) {
    $user_persoon = $result_persoon->fetch_assoc();
    $persoon_id = $user_persoon['persoon_id'];
    $voornaam = $user_persoon['voornaam'] ?? 'Onbekend';
    $naam = $user_persoon['naam'] ?? 'Onbekend';
} else {
    header("Location: ../index.php?error=User not found");
    exit();
}

// Stel de actieve pagina in en include de admin-navigatie
$activePage = 'dashboard';
include('admin-nav.php');

// Sluit de statement en verbinding
$stmt_persoon->close();
$conn->close();
?>