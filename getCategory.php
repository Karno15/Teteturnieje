<?php
session_start();
require('connect.php');

if (!isset($_SESSION['TurniejId'])) {
    // Nie udało się pobrać identyfikatora turnieju z sesji
    echo json_encode(array("error" => "Brak dostępu."));
    exit();
}

$turniejId = $_SESSION['TurniejId'];

// Zapytanie SQL do pobrania kategorii
$sql = "SELECT PytId, Category, Rewards, Done, IsBid FROM `pytania` WHERE TurniejId = ? order by Category,Rewards";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = array(); // Initialize an array to store rows

while ($row = mysqli_fetch_assoc($result)) {
    // Pobrano dane z bazy danych
    $data[] = array(
        "PytId" => $row['PytId'],
        "Category" => $row['Category'],
        "Rewards" => $row['Rewards'],
        "Done" => $row['Done'],
        "IsBid" => $row['IsBid']
    );
}

if (!empty($data)) {
    // Jeśli znaleziono dane w bazie danych, zwróć je
    echo json_encode($data);
} else {
    // Nie znaleziono danych w bazie danych
    echo json_encode(array("error" => "Brak danych."));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
