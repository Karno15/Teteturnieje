<?php
session_start();

include_once('translation/' . $_SESSION['lang'] . ".php");

$response = array();

if (isset($_SESSION['userid'], $_SESSION['TurniejId'])) {

    require('connect.php');

    $turniejId = $_SESSION['TurniejId'];

    $statusQuery = "SELECT t.Status, m.Login AS 'Creator', t.CurrentQuest FROM turnieje t
    JOIN masters m ON m.masterId=t.Creator 
    WHERE t.TurniejId= ?";
    $statusStmt = $conn->prepare($statusQuery);
    $statusStmt->bind_param("i", $turniejId);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    $statusRow = $statusResult->fetch_assoc();

    $statusStmt->close();

    if ($statusResult->num_rows > 0) {
        $status = $statusRow['Status'];
        $creator = $statusRow['Creator'];
        $currentQuest = $statusRow['CurrentQuest'];

        $participantsQuery = "SELECT Login, ROUND(CurrentScore, 3) as 'CurrentScore' FROM
        turuserzy t JOIN users u ON u.UserId=t.UserId WHERE turniejid = ?";
        $participantsStmt = $conn->prepare($participantsQuery);
        $participantsStmt->bind_param("i", $turniejId);
        $participantsStmt->execute();
        $participantsResult = $participantsStmt->get_result();
        $participants = array();

        while ($participantsRow = $participantsResult->fetch_assoc()) {
            $participants[] = $participantsRow;
        }

        $participantsStmt->close();

        $response = array(
            "status" => $status,
            "participants" => $participants,
            "creator" => $creator,
            "currentQuest" => $currentQuest
        );
    } else {

        $response['error'] = $lang["noQuests"];
    }
    mysqli_close($conn);
    echo json_encode($response);
} else {
    echo json_encode($response);
}
