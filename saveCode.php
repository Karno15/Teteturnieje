<?php
session_start();

include_once('translation/' . $_SESSION['lang'] . ".php");

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['userid'])) {
    if (isset($_POST['turniejId'], $_POST['kodTurnieju']) && $_POST['kodTurnieju'] > 0 and $_POST['kodTurnieju'] <= 9999) {

        require "connect.php";

        $turniejId = filter_var($_POST['turniejId'], FILTER_SANITIZE_NUMBER_INT);
        $kodTurnieju = filter_var($_POST['kodTurnieju'], FILTER_SANITIZE_NUMBER_INT);
        $kodTurnieju = $_POST['kodTurnieju'];
        $userId = $_SESSION['userid'];

        $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
        $stmt->bind_param("i", $turniejId);
        $stmt->execute();
        $result = $stmt->get_result();
    
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $creatorId = $row['Creator'];
    
            if ($creatorId != $userId) {
                echo $lang["noAccess"];
                exit();
            }
        } else {
            echo $lang["notFound"];
            exit();
        }

        $checkSql = "SELECT TurniejId FROM turnieje WHERE Code = ? AND TurniejId != ?";
        $checkStmt = $conn->prepare($checkSql);
        $checkStmt->bind_param("ii", $kodTurnieju, $turniejId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            echo $lang["codeExists"];
        } else {
            $countSql = "SELECT PytId FROM pytania WHERE TurniejId = ?";
            $countStmt = $conn->prepare($countSql);
            $countStmt->bind_param("i", $turniejId);
            $countStmt->execute();
            $resultcount = $countStmt->get_result();

            if ($resultcount->num_rows == 0) {
                echo $lang["noQuests"];
            } else {
                $updateSql = "UPDATE turnieje SET Code = ?, Status = 'A' WHERE TurniejId = ?";
                $updateStmt = $conn->prepare($updateSql);
                $updateStmt->bind_param("ii", $kodTurnieju, $turniejId);

                if ($updateStmt->execute()) {
                    $_SESSION['leader'] = $turniejId;
                    $_SESSION['TurniejId'] = $turniejId;
                    echo "success";
                } else {
                    echo $lang["invalidCode"];
                }
                $updateStmt->close();
            }
            $countStmt->close();
        }
        $stmt->close();
        $checkStmt->close();

        $conn->close();
    } else {
        echo $lang["invalidCode"];
    }
} else {
    echo $lang["notFound"];
}
