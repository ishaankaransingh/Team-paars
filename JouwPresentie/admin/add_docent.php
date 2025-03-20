<?php
session_start();
if (!isset($_SESSION['persoon_id'])) {
    http_response_code(403);
    exit();
}

include 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $subject = $_POST['subject'];

    $stmt = $conn->prepare("INSERT INTO docenten (naam, email, vakgebied) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $name, $email, $subject);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
    
    $stmt->close();
    $conn->close();
}
?>