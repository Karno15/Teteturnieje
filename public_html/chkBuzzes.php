<?php
// Plik sprawdz_status.php
session_start();

require('connect.php');

$pytId=$_POST['pytId'];

// Wykonaj zapytanie w celu pobrania pierwszych buzzerów
$buzzesQuery = mysqli_query($conn, "SELECT u.Login, MIN(Buzztime) as 'buzz'
FROM `buzzes` b JOIN `users` u ON u.UserId=b.UserId where PytId=".$pytId ." group by u.Login,TurniejId, PytId
ORDER BY buzz;"
);

$response = array('buzzes' => array());

// Pętla po wynikach zapytania
while ($row = mysqli_fetch_assoc($buzzesQuery)) {
    // Dodawanie wyników do tablicy
    $buzz = array(
        'Login' => $row['Login'],
        'buzztime' => $row['buzz']
    );
    array_push($response['buzzes'], $buzz);
}

// Konwersja do formatu JSON
$jsonResponse = json_encode($response);


mysqli_close($conn);

// Zwróć dane w formie JSON
echo json_encode($response);


//to do