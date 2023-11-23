<?php
session_start();
require('connect.php');

if (!isset($_SESSION['TurniejId'])) {
    // Nie udało się pobrać identyfikatora turnieju z sesji
    echo json_encode(array("error" => "Brak dostępu."));
    exit();
}
$turniejId = $_POST['turniejId'];

// Zapytanie SQL do pobrania pytania
$sql = "SELECT p.PytId, p.After FROM `pytania` p JOIN `turnieje` t ON t.TurniejId=p.TurniejId where t.TurniejId= ? and p.PytId=t.CurrentQuest;";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Pobrano dane z bazy danych
    $data = array(
        "PytId" => $row['PytId'],
        "Answer" => $row['After'],
    );
    echo json_encode($data);
} else {
    // Nie znaleziono danych w bazie danych
    echo json_encode(array("error" => "Brak danych."));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
