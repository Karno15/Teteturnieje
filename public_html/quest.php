<?php
session_start();
require('connect.php');

if (!isset($_SESSION['TurniejId'])) {
    // Nie udało się pobrać identyfikatora turnieju z sesji
    echo json_encode(array("error" => "Brak dostępu."));
    exit();
}

$turniejId = $_SESSION['TurniejId'];

// Zapytanie SQL do pobrania pytania
$sql = "SELECT PytId,Quest, TypeId,Category, whoFirst,Rewards FROM `pytania`
where TurniejId= ? and Done=0 order by PytId limit 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Pobrano dane z bazy danych
    $data = array(
        "PytId" => $row['PytId'],
        "Quest" => $row['Quest'],
        "Rewards" => $row['Rewards'],
        "TypeId" => $row['TypeId'],
        "whoFirst" => $row['whoFirst'],
        "Category" => $row['Category'],
    );

    if ($row['TypeId'] == 1) {
        // Jeśli TypeId = 1, wykonaj dodatkowe zapytanie

        $sql2 = "SELECT pp.pytpozId,pp.pozId, Value,p.Done FROM pytaniapoz pp JOIN pytania p
        ON pp.PytId=p.PytId WHERE TurniejId = ? and Done=0 order by pp.pytpozId limit 4;";

        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "i", $turniejId);
        mysqli_stmt_execute($stmt2);
        $result2 = mysqli_stmt_get_result($stmt2);

        // Pobierz dodatkowe dane i dodaj do tablicy $data
        $additionalData = array();
        while ($row2 = mysqli_fetch_assoc($result2)) {
            $additionalData[] = array(
                "pozId" => $row2['pozId'],
                "Value" =>  base64_decode($row2['Value'])
            );
        }

        $data["pozycje"] = $additionalData;
    }

    echo json_encode($data);
} else {
    // Nie znaleziono danych w bazie danych
    echo json_encode(array("error" => "Brak danych."));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
