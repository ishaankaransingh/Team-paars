<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

if (isset($_GET['rooster_id'])) {
    $rooster_id = $_GET['rooster_id'];

    $sql = "
        SELECT rooster_id, persoon_id, klas_id, vak_id, dag_id, start_tijd, eind_tijd, 
               lokaal_id, periode_id, jaar_id, richting_id
        FROM rooster
        WHERE rooster_id = ?
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $rooster_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    echo json_encode($row);
}
?>