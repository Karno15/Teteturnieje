<?php
session_start();

require "connect.php";

include_once('translation/' . $_SESSION['lang'] . ".php");

if (isset($_POST["delete_question"], $_POST["turniejid"], $_POST["question_id"], $_SESSION['userid'])) {
    $question_id = filter_var($_POST["question_id"], FILTER_SANITIZE_NUMBER_INT);
    $turniejid = filter_var($_POST["turniejid"], FILTER_SANITIZE_NUMBER_INT);

    $userid = $_SESSION['userid'];

    $checkPermissionStmt = $conn->prepare("SELECT t.Creator FROM turnieje t
        JOIN pytania p ON t.TurniejId = p.TurniejId WHERE p.PytId = ? AND t.TurniejId = ?");
    $checkPermissionStmt->bind_param("ii", $question_id, $turniejid);
    $checkPermissionStmt->execute();
    $checkPermissionStmt->bind_result($creator);
    $checkPermissionStmt->fetch();
    $checkPermissionStmt->close();

    if ($userid != $creator) {
        $_SESSION['info'] = $lang["noAccess"];
        header("Location: edit.php?turniejid=" . $turniejid);
        exit();
    }

    try {
        $stmt = $conn->prepare("DELETE pytania, pytaniapoz, prawiodpo FROM pytania
            LEFT JOIN pytaniapoz ON pytania.PytId = pytaniapoz.PytId
            LEFT JOIN prawiodpo ON pytania.PytId = prawiodpo.PytId
            WHERE pytania.PytId = ?");
        $stmt->bind_param("i", $question_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['info'] = $lang["questionDeleted"];
        header("Location: edit.php?turniejid=" . $turniejid);
        exit();
    } catch (mysqli_sql_exception $e) {
        $_SESSION['info'] = "Error" . $e->getMessage();
        header("Location: edit.php?turniejid=" . $turniejid);
        exit();
    }
} else {
    $_SESSION['info'] = $lang["noAccess"];
    header("Location: edit.php?turniejid=" . $turniejid);
    exit();
}
