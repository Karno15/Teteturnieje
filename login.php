<?php

session_start();

if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en';
}

require "connect.php";

function isValidUsername($username)
{
    $allowedCharacters = "/^[a-zA-Z0-9_\-]+$/";

    return preg_match($allowedCharacters, $username);
}


function validateUser($username, $password)
{
    include_once('translation/' . $_SESSION['lang'] . ".php");

    require "connect.php";

    if ($username == '' || $password == '') {
        $_SESSION['info'] = $lang['invalidLogin'];
        header("Location: index.php");
        exit();
    }

    if (!isValidUsername($username)) {
        $_SESSION['info'] = $lang['invalidLogin'];
        return false;
    }

    $password = md5($password);

    $query = "SELECT masterId, Login FROM masters WHERE Login = UPPER(?) AND Pass= ? ;";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result) {
            $row = mysqli_fetch_assoc($result);

            if ($row) {
                $query = "UPDATE users SET LastLogged = CURRENT_TIMESTAMP() WHERE UserID = ?";
                $stmtUpdate = mysqli_prepare($conn, $query);

                if ($stmtUpdate) {
                    mysqli_stmt_bind_param($stmtUpdate, "i", $row['masterId']);
                    mysqli_stmt_execute($stmtUpdate);
                    mysqli_stmt_close($stmtUpdate);
                }

                $_SESSION['username'] = strtoupper($username);
                $_SESSION['userid'] = $row['masterId'];
                $_SESSION['info'] = $lang['loggedin'];
                return true;
            } else {
                $_SESSION['info'] = $lang['invalidLogin'];
                return false;
            }
        } else {
            echo "Error";
            return false;
        }
        mysqli_stmt_close($stmt);
    } else {
        echo "Error";
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['login'];
    $password = $_POST['pass'];

    if (validateUser($username, $password)) {
        header("Location: logged.php");
        exit();
    } else {
        header("Location: index.php");
        exit();
    }
}
