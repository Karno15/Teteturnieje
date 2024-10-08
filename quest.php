<?php
session_start();

require('connect.php');

include_once('translation/' . $_SESSION['lang'] . ".php");

if (!isset($_SESSION['TurniejId']) || !isset($_POST['turniejId']) || !isset($_SESSION['userid'])) {
    echo json_encode(array("error" => $lang["noAccess"]));
    exit();
}

$turniejId = mysqli_real_escape_string($conn, $_POST["turniejId"]);
$userId = $_SESSION['userid'];

$sql = "SELECT t.Creator, u.masterId as 'UserId' 
FROM turnieje t 
JOIN turuserzy tu ON t.TurniejId = tu.TurniejId
JOIN users u ON u.UserId=tu.UserId
WHERE t.TurniejId = ?;";

$stmtacc = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmtacc, "i", $turniejId);
mysqli_stmt_execute($stmtacc);
$resultacc = mysqli_stmt_get_result($stmtacc);

$creator = null;
$found = false;
while ($row = mysqli_fetch_assoc($resultacc)) {
    if ($row['Creator'] == $userId || $row['UserId'] == $userId) {
        $found = true;
        break;
    }
    $creator = $row['Creator'];
}

if ($found) {
    $sql = "SELECT p.PytId, p.Quest, p.TypeId, p.Category, p.Rewards, p.IsBid FROM 
    `pytania` p JOIN `turnieje` t ON t.TurniejId=p.TurniejId
    where p.TurniejId=? and p.PytId=t.CurrentQuest";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $turniejId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        $data = array(
            "PytId" => $row['PytId'],
            "Quest" => $row['Quest'],
            "Rewards" => $row['Rewards'],
            "TypeId" => $row['TypeId'],
            "Category" => $row['Category'],
            "IsBid" => $row['IsBid']
        );

        if ($row['TypeId'] == 1) {
            $sql2 = "SELECT pp.pytpozId,pp.pozId, pp.Value,p.Done FROM `pytaniapoz` pp
            JOIN `pytania` p ON pp.PytId=p.PytId
            JOIN `turnieje` t ON t.TurniejId=p.TurniejId
            WHERE p.TurniejId = ? and p.PytId=t.CurrentQuest order by pp.pytpozId;";

            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "i", $turniejId);
            mysqli_stmt_execute($stmt2);
            $result2 = mysqli_stmt_get_result($stmt2);

            $additionalData = array();
            while ($row2 = mysqli_fetch_assoc($result2)) {
                $additionalData[] = array(
                    "pozId" => $row2['pozId'],
                    "Value" =>  base64_decode($row2['Value'])
                );
            }
            mysqli_stmt_close($stmt2);

            $data["pozycje"] = $additionalData;
        }
        $_SESSION['currentQuest'] = $row['PytId'];
        mysqli_stmt_close($stmt);

        echo json_encode($data);
    } else {
        echo json_encode(array("error" => 'No data'));
    }
} else {
    echo json_encode(array("error" => $lang["noAccess"]));
}

mysqli_close($conn);
