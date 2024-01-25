<?php
session_start();

require 'connect.php';

if (isset($_POST['login'], $_POST['pts'], $_POST['answer'], $_POST['turniejId'],$_SESSION['userid'])) {
    $login = html_entity_decode(urldecode(htmlspecialchars($_POST['login'])));
    $pts = floatval($_POST['pts']);
    $answer = intval($_POST['answer']);
    $turniejId = intval($_POST['turniejId']);
    
    if ($answer == 0) {
        $pts = -1 * abs($pts);
    }
    $checkCreatorQuery = "SELECT Creator FROM turnieje WHERE TurniejId = ?";
    $stmtCheckCreator = mysqli_prepare($conn, $checkCreatorQuery);
    mysqli_stmt_bind_param($stmtCheckCreator, 'i', $turniejId);
    mysqli_stmt_execute($stmtCheckCreator);
    mysqli_stmt_bind_result($stmtCheckCreator, $turniejCreator);
    mysqli_stmt_fetch($stmtCheckCreator);
    mysqli_stmt_close($stmtCheckCreator);

    if ($_SESSION['userid'] != $turniejCreator) {
        echo "Access denied.";
    } else {
        $sql = "INSERT INTO answers (Login, Points, Answer, TurniejId) VALUES (?, ?, ?, ?)";
        try {
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'sdii', $login, $pts, $answer, $turniejId);
            $execute = mysqli_stmt_execute($stmt);

            if ($execute) {
                echo "Answered!";
            } else {
                echo "Error answer other!";
            }
            mysqli_stmt_close($stmt);
        } catch (mysqli_sql_exception $exception) {
            echo "Error answer sql!";
        } finally {
            mysqli_close($conn);
        }
    }
} else {
    echo "No access!";
}

