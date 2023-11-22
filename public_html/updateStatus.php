<?php
require('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'];
    $turniejId = $_POST['turniejId'];
    // Prepare and bind the statement
    $stmt = $conn->prepare("UPDATE turnieje SET status=? WHERE turniejId=?");
    $stmt->bind_param("si", $status, $turniejId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Status updated successfully";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
}
?>
