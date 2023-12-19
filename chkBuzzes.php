<?php
// Plik sprawdz_status.php
session_start();

require('connect.php');
if (isset($_POST['userId']) && isset($_POST['turniejId'])) {
$turniejId = $_POST['turniejId'];

$sql = "SELECT u.Login, MIN(Buzztime) as 'buzz' FROM `buzzes` b 
        JOIN `users` u ON u.UserId=b.UserId 
        JOIN `turnieje` t ON t.turniejId=b.TurniejId 
        WHERE t.CurrentQuest=b.PytId AND t.TurniejId=? 
        GROUP BY u.Login, b.TurniejId, b.PytId 
        ORDER BY buzz;";


$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$response = array('buzzes' => array());

while ($row = mysqli_fetch_assoc($result)) {
    $buzz = array(
        'Login' => $row['Login'],
        'buzztime' => $row['buzz']
    );
    array_push($response['buzzes'], $buzz);
}

mysqli_stmt_close($stmt);

mysqli_close($conn);

echo json_encode($response);
}else {
    echo "Błąd danych.";
}
