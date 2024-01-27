<?php
require('connect.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'];
    $turniejId = $_POST['turniejId'];
    $currentQuest = $_POST['currentQuest'];

    // Check if all questions for the specified turniejId have Done=1 and the incoming status is 'K'
    if ($status === 'K') {
        $checkQuery = "SELECT COUNT(*) AS total FROM pytania WHERE turniejId=? AND Done=0";
        $checkStmt = $conn->prepare($checkQuery);
        $checkStmt->bind_param("i", $turniejId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();
        $checkRow = $checkResult->fetch_assoc();

        if ($checkRow['total'] == 0) {
            // All questions are done, change status to 'X'
            $status = 'X';
        }
    } elseif ($status === 'O') {
        // If the status is 'O', set CurrentQuest as done in the database
        $updateQuery = "UPDATE pytania p JOIN turnieje t ON t.TurniejId=p.TurniejId SET p.Done = 1 WHERE p.PytId=t.CurrentQuest
         and p.TurniejId= ? ";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bind_param("i", $turniejId);
        $updateStmt->execute();
        echo "done";
    } else if ($status === 'Z') {
        $sql = "CALL UpdateTournamentStatus(?)";
        $stmtproc = $conn->prepare($sql);
        $stmtproc->bind_param("i", $turniejId);
        $stmtproc->execute();
        $stmtproc->close();
    }

    // Prepare and bind the statement
    $stmt = $conn->prepare("UPDATE turnieje SET status=?, CurrentQuest=? WHERE turniejId=?");
    $stmt->bind_param("sii", $status, $currentQuest, $turniejId);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Completed successfully";
    } else {
        echo "Error updating status: " . $stmt->error;
    }

    // Close the statement and connection
    $stmt->close();
} else {
    echo 'No data';
}
