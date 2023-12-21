<?php
session_start();
require('connect.php');

if (!isset($_SESSION['TurniejId'])|| !isset($_POST['turniejId'])) {
    // Nie udało się pobrać identyfikatora turnieju z sesji
    echo json_encode(array("error" => "Brak dostępu."));
    exit();
}
$turniejId = $_POST['turniejId'];

// Zapytanie SQL do pobrania pytania
$sql = "SELECT distinct p.PytId, p.After, po.PozId FROM `pytania` p JOIN `turnieje` t ON t.TurniejId=p.TurniejId 
LEFT JOIN `prawiodpo` po ON p.PytId=po.PytId LEFT JOIN `pytaniapoz` pp ON p.PytId=pp.PytId
 where t.TurniejId= ? and p.PytId=t.CurrentQuest;";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);



if ($row = mysqli_fetch_assoc($result)) {
    // Pobrano dane z bazy danych
    $data = array(
        "PytId" => $row['PytId'],
        "Answer" => $row['After'],
        "PozId" => intval($row['PozId'])
    );
    echo json_encode($data);

    if(!isset($_COOKIE['EE_Larvolcarona']) && ( preg_match("/volcarona/i", $row['After']) || preg_match("/larvesta/i", $row['After'])) ){
        setcookie ("EE_Larvolcarona", 1 ,time()+30); 
    }

} else {
    // Nie znaleziono danych w bazie danych
    echo json_encode(array("error" => "Brak danych."));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
