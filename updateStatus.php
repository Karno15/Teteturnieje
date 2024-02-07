<?php

session_start();

require('connect.php');

include_once('translation/' . $_SESSION['lang'] . ".php");


if (isset($_SESSION['userid']) && isset($_SESSION['TurniejId'])) {
    $userId = $_SESSION['userid'];
    $turniejId = $_SESSION['TurniejId'];

    if (isset($_POST['status'], $_POST['currentQuest'])) {
        $status = $_POST['status'];
        $currentQuest = $_POST['currentQuest'];

        $checkCreatorQuery = "SELECT Creator FROM turnieje WHERE TurniejId = ?";
        $stmtCheckCreator = mysqli_prepare($conn, $checkCreatorQuery);
        mysqli_stmt_bind_param($stmtCheckCreator, 'i', $turniejId);

        if (mysqli_stmt_execute($stmtCheckCreator)) {
            mysqli_stmt_store_result($stmtCheckCreator);

            if (mysqli_stmt_num_rows($stmtCheckCreator) > 0) {
                mysqli_stmt_bind_result($stmtCheckCreator, $creatorId);
                mysqli_stmt_fetch($stmtCheckCreator);

                if ($currentQuest != 0) {
                    $checkQuestionQuery = "SELECT pytId FROM pytania WHERE pytId = ? AND TurniejId = ?";
                    $stmtCheckQuestion = mysqli_prepare($conn, $checkQuestionQuery);
                    mysqli_stmt_bind_param($stmtCheckQuestion, 'ii', $currentQuest, $turniejId);

                    if (mysqli_stmt_execute($stmtCheckQuestion)) {
                        mysqli_stmt_store_result($stmtCheckQuestion);

                        if (mysqli_stmt_num_rows($stmtCheckQuestion) <= 0) {
                            echo json_encode(array("error" => "Invalid question ID"));
                            exit();
                        }
                    } else {
                        echo json_encode(array("Error"));
                        exit();
                    }

                    mysqli_stmt_close($stmtCheckQuestion);
                }

                if ($creatorId != $userId) {
                    echo json_encode(array("error" => $lang["noAccess"]));
                    exit();
                }
            } else {
                echo json_encode(array("error" => $lang["notFound"]));
                exit();
            }
        } else {
            echo json_encode(array("Error"));
            exit();
        }

        mysqli_stmt_close($stmtCheckCreator);

        $validStatusArray = ['N', 'A', 'K', 'P', 'O', 'X', 'Z'];

        if (!in_array($status, $validStatusArray)) {
            echo json_encode(array("error" => "Invalid Status"));
            exit();
        }
    } else {
        echo json_encode(array("error" => $lang["noAccess"]));
        exit();
    }
} else {
    echo json_encode(array("error" => $lang["noAccess"]));
    exit();
}

if ($status === 'K') {
    $checkQuery = "SELECT COUNT(*) AS total FROM pytania WHERE turniejId=? AND Done=0";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->bind_param("i", $turniejId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $checkRow = $checkResult->fetch_assoc();

    if ($checkRow['total'] == 0) {
        $status = 'X';
    }
} elseif ($status === 'O') {
    $updateQuery = "UPDATE pytania p JOIN turnieje t ON t.TurniejId=p.TurniejId SET p.Done = 1 WHERE p.PytId=t.CurrentQuest
         and p.TurniejId= ? ";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $turniejId);
    $updateStmt->execute();
} elseif ($status === 'Z') {
    $sql = "CALL UpdateTournamentStatus(?)";
    $stmtproc = $conn->prepare($sql);
    $stmtproc->bind_param("i", $turniejId);
    $stmtproc->execute();
    $stmtproc->close();
}

$stmt = $conn->prepare("UPDATE turnieje SET status=?, CurrentQuest=? WHERE turniejId=?");
$stmt->bind_param("sii", $status, $currentQuest, $turniejId);

if ($stmt->execute()) {
    echo "Completed successfully";
} else {
    echo "Error updating status";
}

$stmt->close();

mysqli_close($conn);
