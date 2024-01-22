<?php

session_start();

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}


require ('connect.php');
// Function to check if a username already exists in masters or users (case insensitive)
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

// Function to register a new user
function registerUser($username, $password, $conn)
{
    include_once( 'translation/'. $_SESSION['lang'] . ".php");
    echo $lang['userExistsInfo'];
    // Check if the username already exists
    if (usernameExists($username, $conn)) {
        $_SESSION['info'] = $lang['userExistsInfo'];
        return false;
    }

    // Hash the password using md5 (this is just for demonstration, consider using more secure methods like password_hash)
    $hashedPassword = md5($password);

    // Insert into masters table
    $insertMasterQuery = "INSERT INTO masters (Login, Pass) VALUES (UPPER(?), ?);";
    $stmtMaster = mysqli_prepare($conn, $insertMasterQuery);
    mysqli_stmt_bind_param($stmtMaster, 'ss', $username, $hashedPassword);
    $resultMaster = mysqli_stmt_execute($stmtMaster);

    if (!$resultMaster) {
        $_SESSION['info'] = $lang['registerError'];
        error_log("Error inserting: " . mysqli_error($conn));
        mysqli_stmt_close($stmtMaster);
        return false;
    }

    // Get the masterId of the inserted master
    $masterId = mysqli_insert_id($conn);

    // Insert into users table
    $insertUserQuery = "INSERT INTO users (Login, Pass, masterId) VALUES (UPPER(?), ?, ?);";
    $stmtUser = mysqli_prepare($conn, $insertUserQuery);
    mysqli_stmt_bind_param($stmtUser, 'ssi', $username, $hashedPassword, $masterId);
    $resultUser = mysqli_stmt_execute($stmtUser);

    if (!$resultUser) {
        $_SESSION['info'] = $lang['registerError'];
        error_log("Error inserting into users: " . mysqli_error($conn));
        mysqli_stmt_close($stmtUser);
        return false;
    }

    // Registration successful, set up the session
    $_SESSION['info'] = $lang['registered'];

    // Close the statements
    mysqli_stmt_close($stmtMaster);
    mysqli_stmt_close($stmtUser);

    return true;
}


// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['login'];
    $password = $_POST['pass'];

    if (registerUser($username, $password, $conn)) {
        // Registration successful, you can redirect to a different page if needed
        header("Location: index.php");
        exit();
    } else {
        // Show an appropriate error message
        header("Location: index.php");
        exit();
    }
}
