<?php
session_start();
require('connect.php');

///BIG issue - need to check wheather all of the question are within this one turniejid, if not error!!!


include_once('translation/' . $_SESSION['lang'] . ".php");
// Check if GET parameter is set
if (isset($_GET['turniejid'])) {
    $turniejId = $_GET['turniejid'];
} elseif (isset($_SESSION['TurniejId'])) {
    // Check if session variable is set
    $turniejId = $_SESSION['TurniejId'];
} else {
    // Neither GET nor session variable is set
    echo json_encode(array("error" => $lang["noAccess"]));
    exit();
}

// If both GET and session variables are set, use the value from GET
if (isset($_GET['turniejid']) && isset($_SESSION['TurniejId'])) {
    $turniejId = $_GET['turniejid'];
}

$sql = "SELECT p.PytId, p.Category, p.Rewards, p.Done, p.IsBid, t.Columns FROM `pytania` p JOIN turnieje t ON t.TurniejId=p.TurniejId WHERE p.TurniejId = ? ORDER BY `Order`;";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $turniejId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$data = array(); // Initialize an array to store rows
$columns = null; // Initialize variable to store Columns value

while ($row = mysqli_fetch_assoc($result)) {
    // Pobrano dane z bazy danych
    $data[] = array(
        "PytId" => $row['PytId'],
        "Category" => $row['Category'],
        "Rewards" => $row['Rewards'],
        "Done" => $row['Done'],
        "IsBid" => $row['IsBid'],
        "Columns" => $row['Columns']
    );
}


if (!empty($data)) {
    // Jeśli znaleziono dane w bazie danych, zwróć je
    echo json_encode($data);
} else {
    // Nie znaleziono danych w bazie danych
    echo json_encode(array("error" => "No data"));
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
