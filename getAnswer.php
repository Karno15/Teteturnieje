<?php
session_start();
require('connect.php');

include_once('translation/' . $_SESSION['lang'] . ".php");

if (!isset($_SESSION['TurniejId']) || !isset($_POST['turniejId']) || !isset($_SESSION['userid'])) {
    echo $lang["noAccess"];
    exit();
}
$turniejId = filter_var($_POST['turniejId'], FILTER_SANITIZE_NUMBER_INT);
$userId = $_SESSION['userid'];

$checkParticipantQuery = "SELECT t.turniejId FROM turuserzy t JOIN users u ON u.UserId=t.UserId
 JOIN masters m ON m.masterId=u.masterId WHERE t.TurniejId = ? AND m.masterId = ?;";
$stmtCheckParticipant = mysqli_prepare($conn, $checkParticipantQuery);
mysqli_stmt_bind_param($stmtCheckParticipant, "ii", $turniejId, $userId);
mysqli_stmt_execute($stmtCheckParticipant);
$resultCheckParticipant = mysqli_stmt_get_result($stmtCheckParticipant);

if (!$resultCheckParticipant || mysqli_num_rows($resultCheckParticipant) === 0) {
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
        $stmt->close();
    }
}
mysqli_stmt_close($stmtCheckParticipant);

$sql = "SELECT distinct p.PytId, p.After, po.PozId FROM `pytania` p JOIN `turnieje` t ON t.TurniejId=p.TurniejId 
LEFT JOIN `prawiodpo` po ON p.PytId=po.PytId LEFT JOIN `pytaniapoz` pp ON p.PytId=pp.PytId
 where t.TurniejId= ? and p.PytId=t.CurrentQuest and t.Status='O';";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $data = array(
        "PytId" => $row['PytId'],
        "Answer" => $row['After'],
        "PozId" => intval($row['PozId'])
    );
    echo json_encode($data);

    if (!isset($_COOKIE['EE_Larvolcarona']) && (preg_match("/volcarona/i", $row['After']) || preg_match("/larvesta/i", $row['After']))) {
        setcookie("EE_Larvolcarona", 1, time() + 30);
    }
} else {
    echo "No data";
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
