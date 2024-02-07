<?php

session_start();

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

require('connect.php');

function usernameExists($username, $conn)
{
    $checkQuery = "SELECT COUNT(*) FROM masters WHERE UPPER(Login) = UPPER(?) AND UPPER(Login) IN (SELECT UPPER(Login) FROM users);";
    $checkStmt = mysqli_prepare($conn, $checkQuery);
    mysqli_stmt_bind_param($checkStmt, 's', $username);
    mysqli_stmt_execute($checkStmt);
    mysqli_stmt_bind_result($checkStmt, $userCount);
    mysqli_stmt_fetch($checkStmt);
    mysqli_stmt_close($checkStmt);

    return $userCount > 0;
}

function registerUser($username, $password, $conn)
{
    include_once('translation/' . $_SESSION['lang'] . ".php");
    if ($username == '' || $password == '') {
        $_SESSION['info'] = $lang['registerError'];
        header("Location: index.php");
        exit();
    }

    if (!isValidUsername($username)) {
        $_SESSION['info'] = $lang['invalidLogin'];
        return false;
    }

    if (usernameExists($username, $conn)) {
        $_SESSION['info'] = $lang['userExistsInfo'];
        return false;
    }
    $hashedPassword = md5($password);

    $insertMasterQuery = "INSERT INTO masters (Login, Pass) VALUES (UPPER(?), ?);";
    $stmtMaster = mysqli_prepare($conn, $insertMasterQuery);
    mysqli_stmt_bind_param($stmtMaster, 'ss', $username, $hashedPassword);
    $resultMaster = mysqli_stmt_execute($stmtMaster);

    if (!$resultMaster) {
        $_SESSION['info'] = $lang['registerError'];
        error_log("Error inserting");
        mysqli_stmt_close($stmtMaster);
        return false;
    }

    $masterId = mysqli_insert_id($conn);

    $insertUserQuery = "INSERT INTO users (Login, Pass, masterId) VALUES (UPPER(?), ?, ?);";
    $stmtUser = mysqli_prepare($conn, $insertUserQuery);
    mysqli_stmt_bind_param($stmtUser, 'ssi', $username, $hashedPassword, $masterId);
    $resultUser = mysqli_stmt_execute($stmtUser);

    if (!$resultUser) {
        $_SESSION['info'] = $lang['registerError'];
        error_log("Error inserting into users");
        mysqli_stmt_close($stmtUser);
        return false;
    }

    $_SESSION['info'] = $lang['registered'];

    mysqli_stmt_close($stmtMaster);
    mysqli_stmt_close($stmtUser);

    return true;
}

function isValidUsername($username)
{
    $allowedCharacters = "/^[a-zA-Z0-9_\-]+$/";

    return preg_match($allowedCharacters, $username);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['login'];
    $password = $_POST['pass'];

    if (registerUser($username, $password, $conn)) {
        header("Location: index.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}
