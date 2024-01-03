<?php
// Include your database connection file (connect.php)
require 'connect.php';

$turniejid = $_POST['turniejid'];
$order = $_POST['order'];
$columns = $_POST['columns'];

try {
    // Update the order for each pytId in the pytania table
    foreach ($order as $index => $pytId) {
        $stmt = $conn->prepare("UPDATE pytania SET `Order` = ? WHERE pytId = ?");
        $stmt->bind_param('ii', $index, $pytId);
        $stmt->execute();
        $stmt->close();
    }

    // Update the columns value in the turnieje table
    $stmt = $conn->prepare("UPDATE turnieje SET `Columns` = ? WHERE turniejid = ?");
    $stmt->bind_param('ii', $columns, $turniejid);
    $stmt->execute();
    $stmt->close();

    // Respond with a success message or appropriate status
    echo json_encode(['message' => 'Grid order and columns saved successfully']);
} catch (Exception $e) {
    // Handle database errors
    echo json_encode(['error' => 'Error saving grid order and columns: ' . $e->getMessage()]);
} finally {
    // Close the database connection
    $conn->close();
}
?>
