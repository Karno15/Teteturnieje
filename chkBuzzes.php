<?php
session_start();

if (isset($_SESSION['userid'], $_SESSION['TurniejId'])) {
    
    require('connect.php');

    $turniejId = $_SESSION['TurniejId'];

    $sql = "SELECT u.Login, MIN(Buzztime) as 'buzz' FROM `buzzes` b 
        JOIN `users` u ON u.UserId=b.UserId 
        JOIN `turnieje` t ON t.turniejId=b.TurniejId 
        WHERE t.CurrentQuest=b.PytId AND t.TurniejId=? 
        GROUP BY u.Login, b.TurniejId, b.PytId 
        ORDER BY buzz;";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $turniejId);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $login, $buzz);
    $response = array('buzzes' => array());

    while (mysqli_stmt_fetch($stmt)) {
        $buzz = array(
            'Login' => $login,
            'buzztime' => $buzz
        );
        array_push($response['buzzes'], $buzz);
    }
    mysqli_stmt_close($stmt);

    echo json_encode($response);
} else {
    echo "No data.";
}
mysqli_close($conn);
