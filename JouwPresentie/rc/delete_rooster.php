<?php
session_start();
include('../login/db_connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rooster_id = $_POST['rooster_id'];

    $sql_delete = "DELETE FROM roosterstud WHERE rooster_id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $rooster_id);
    $stmt_delete->execute();
    $stmt_delete->close();
}
?>