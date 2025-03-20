<?php
include('../login/db_connect.php'); // Include your database connection

$role_id = isset($_GET['role_id']) ? intval($_GET['role_id']) : 3;

$stmt = $conn->prepare("SELECT p.persoon_id, p.naam, p.active, t.email FROM personen p JOIN tgebruiker t ON p.persoon_id = t.persoon_id WHERE p.rol_id = ?");
$stmt->bind_param("i", $role_id);
$stmt->execute();
$result = $stmt->get_result();

$docenten = [];
while ($row = $result->fetch_assoc()) {
    $docenten[] = $row;
}

echo json_encode($docenten);
$stmt->close();
$conn->close();
?>