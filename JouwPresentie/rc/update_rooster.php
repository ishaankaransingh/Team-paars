<?php
session_start();
include('../login/db_connect.php'); // Include your database connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if all required fields are set
    if (isset($_POST['rooster_id'], $_POST['dag_id'], $_POST['vak_id'], $_POST['lesblok_id'], $_POST['lokaal_id'])) {
        $rooster_id = $_POST['rooster_id'];
        $dag_id = $_POST['dag_id'];
        $vak_id = $_POST['vak_id'];
        $lesblok_id = $_POST['lesblok_id'];
        $lokaal_id = $_POST['lokaal_id']; // Voeg lokaal_id toe

        // Prepare the update statement
        $sql_update = "
            UPDATE roosterstud 
            SET dag_id = ?, vak_id = ?, lesblok_id = ?, lokaal_id = ?
            WHERE rooster_id = ?
        ";
        $stmt_update = $conn->prepare($sql_update);

        if (!$stmt_update) {
            echo json_encode(['success' => false, 'error' => 'Query preparation failed: ' . $conn->error]);
            exit();
        }

        // Bind the parameters to the query
        $stmt_update->bind_param("iiiii", $dag_id, $vak_id, $lesblok_id, $lokaal_id, $rooster_id);

        // Execute the query
        if ($stmt_update->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => $stmt_update->error]);
        }

        // Close the statement
        $stmt_update->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid input']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
}

// Close the database connection
$conn->close();
?>