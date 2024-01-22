<?php
session_start();

include_once('translation/' . $_SESSION['lang'] . ".php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['turniejId']) && isset($_POST['kodTurnieju']) && $_POST['kodTurnieju'] > 0 and $_POST['kodTurnieju'] <= 9999) {
        require "connect.php"; // Assuming that the connect.php file contains the database connection configuration

        $turniejId = $_POST['turniejId'];
        $kodTurnieju = $_POST['kodTurnieju'];
        $userId = $_SESSION['userid'];

        // Query to check if the TurniejId belongs to the user
        $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
        $stmt->bind_param("i", $turniejId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $creatorId = $row['Creator'];
    
            // Check if the TurniejId's creator matches the user's ID - if yes do the rest
            if ($creatorId != $userId) {
                echo $lang["notFound"];
                exit();
            }
        } else {
            echo $lang["notFound"];
            exit();
        }


        // Check if the code already exists
        $checkSql = "SELECT TurniejId FROM turnieje WHERE Code = ? AND TurniejId != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $kodTurnieju, $turniejId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo $lang["codeExists"];
        } else {
            // Check if there are questions
            $countSql = "SELECT PytId FROM pytania WHERE TurniejId = ?";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $turniejId);
            $countStmt->execute();
            $resultcount = $countStmt->get_result();

            if ($resultcount->num_rows == 0) {
                echo $lang["noQuests"];
            } else {
                // Update the tournament code
                $updateSql = "UPDATE turnieje SET Code = ?, Status = 'A' WHERE TurniejId = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $kodTurnieju, $turniejId);

                if ($updateStmt->execute()) {
                    $_SESSION['leader'] = $turniejId;
                    $_SESSION['TurniejId'] = $turniejId;
                    echo "success";
                } else {
                    echo $lang["invalidCode"];
                    // For debugging: echo $updateStmt->error;
                }
                $updateStmt->close();
            }
            $countStmt->close();
        }

        $stmt->close();
        // Close the statements
        $checkStmt->close();
        // Close the connection
        $conn->close();
    } else {
        echo $lang["invalidCode"];
    }
} else {
    echo $lang["notFound"];
}
