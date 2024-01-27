<?php
session_start();
require('connect.php');

include_once('translation/' . $_SESSION['lang'] . ".php");

if (isset($_SESSION['userid'])) {
    $userId = $_SESSION['userid'];

    if (isset($_SESSION['TurniejId'], $_GET['turniejid'])) {
        if (isCreator($_GET['turniejid'], $userId)) {
            $turniejId = $_GET['turniejid'];
        } elseif (isParticipant($_SESSION['TurniejId'], $userId)) {
            $turniejId = $_SESSION['TurniejId'];
        } else {
            echo $lang["noAccess"];
            exit();
        }
    } elseif (isset($_GET['turniejid'])) {
        if (isCreator($_GET['turniejid'], $userId)) {
            $turniejId = $_GET['turniejid'];
        } else {
            echo $lang["noAccess"];
            exit();
        }
    } elseif (isset($_SESSION['TurniejId'])) {
        if (isParticipant($_SESSION['TurniejId'], $userId) || isCreator($_SESSION['TurniejId'], $userId)) {
            $turniejId = $_SESSION['TurniejId'];
        } else {
            echo $lang["noAccess"];
            exit();
        }
    } else {
        echo $lang["noAccess"];
        exit();
    }
    getCategory($turniejId);
    
} else {
    echo 'No data';
}

function isParticipant($turniejId, $userId)
{
    require "connect.php";

    $checkParticipantQuery = "SELECT t.turniejId FROM turuserzy t JOIN users u ON u.UserId=t.UserId
         JOIN masters m ON m.masterId=u.masterId WHERE t.TurniejId = ? AND m.masterId = ?;";
    $stmtCheckParticipant = mysqli_prepare($conn, $checkParticipantQuery);
    mysqli_stmt_bind_param($stmtCheckParticipant, "ii", $turniejId, $userId);
    mysqli_stmt_execute($stmtCheckParticipant);
    $resultCheckParticipant = mysqli_stmt_get_result($stmtCheckParticipant);
    mysqli_stmt_close($stmtCheckParticipant);
    if (!$resultCheckParticipant || mysqli_num_rows($resultCheckParticipant) === 0) {
        return false;
    } else {
        return true;
    }
}

function isCreator($turniejId, $userId)
{
    require "connect.php";

    $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
    $stmt->bind_param("i", $turniejId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creatorId = $row['Creator'];

        if ($creatorId != $userId) {
            return false;
        } else {
            return true;
        }
    } else {
        return false;
    }
    $stmt->close();
}

function getCategory($turniejId)
{
    require "connect.php";

    $sql = "SELECT p.PytId, p.Category, p.Rewards, p.Done, p.IsBid, t.Columns 
    FROM `pytania` p JOIN turnieje t ON t.TurniejId=p.TurniejId WHERE p.TurniejId = ? ORDER BY `Order`;";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $turniejId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $data = array();
    $columns = null;

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = array(
            "PytId" => $row['PytId'],
            "Category" => $row['Category'],
            "Rewards" => $row['Rewards'],
            "Done" => $row['Done'],
            "IsBid" => $row['IsBid'],
            "Columns" => $row['Columns']
        );
    }

    if (!empty($data)) {
        echo json_encode($data);
    } else {
        echo 'No data';
    }
    mysqli_stmt_close($stmt);
}

mysqli_close($conn);
