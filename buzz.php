<?php
session_start();

if (isset($_POST['username'], $_POST['turniejId'], $_SESSION['username'])) {

    require 'connect.php';

    $username = $_POST['username'];
    $turniejId = filter_var($_POST['turniejId'], FILTER_SANITIZE_NUMBER_INT);

    if ($username !== $_SESSION['username']) {
        echo "No Access!";
        exit;
    }
    $sql = "INSERT INTO buzzes (UserId, TurniejId, PytId) SELECT u.UserId, t.turniejId, t.CurrentQuest from turnieje t 
    JOIN turuserzy tu ON tu.turniejId=t.TurniejId JOIN users u ON u.UserId=tu.UserId where t.TurniejId= ?  and u.Login= ? ; ";

    try {
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, 'is', $turniejId, $username);
        $execute = mysqli_stmt_execute($stmt);
        $affectedRows = mysqli_stmt_affected_rows($stmt);

        if ($execute && $affectedRows > 0) {
            echo "Buzzed!";
        } else {
            echo "You can't buzz on this tournament!";
        }
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $exception) {
        echo "Error buzz sql!";
    } finally {
        mysqli_close($conn);
    }
} else {
    echo "No Access!";
}
