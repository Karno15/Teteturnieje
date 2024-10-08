<?php
require "connect.php";

session_start();

function isValidUsername($username)
{
    $allowedCharacters = "/^[a-zA-Z0-9_\-]+$/";

    return preg_match($allowedCharacters, $username);
}


if (isset($_POST["login"]) && isset($_POST["gamecode"])) {

    include_once( 'translation/'. $_SESSION['lang'] . ".php");
    
    $gc = htmlspecialchars($_POST["gamecode"]);
    $login = htmlspecialchars($_POST["login"]);

    $sql = "SELECT TurniejId,Name FROM turnieje WHERE Status='A' AND Code=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $gc);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);

        $_SESSION['TurniejId'] = $row['TurniejId'];
        $_SESSION['Name'] = $row['Name'];
        $masterid = $_SESSION['userid'];

        $sql = "SELECT UserId, Login, masterId FROM users WHERE Login=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $login);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result->num_rows == 0) {

            if (!isValidUsername($login)) {
                $_SESSION['info'] = $lang['invalidLogin'];
                header("Location: logged.php");
                exit();
            }

            $sql = "INSERT INTO users (Login, LastLogged, masterId) VALUES (UPPER(?), CURRENT_TIMESTAMP(), ? )";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "si", $login, $masterid);
            $execute = mysqli_stmt_execute($stmt);

            $userid = mysqli_insert_id($conn);

            $usernamemaster = $masterid;
        } else {
            $sql = "UPDATE users SET LastLogged=CURRENT_TIMESTAMP() WHERE Login=?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $login);
            $execute = mysqli_stmt_execute($stmt);

            $row = mysqli_fetch_assoc($result);
            $userid = $row['UserId'];
            $usernamemaster = $row['masterId'];
        }
        if ($usernamemaster != $masterid || $usernamemaster == null) {

            $_SESSION['info'] = $lang['nicknameExists'];
            header("Location: logged.php");
            exit();
        } else {
            $_SESSION["username"] = strtoupper($login);

            mysqli_begin_transaction($conn);

            $sql_check = "SELECT COUNT(*) FROM turuserzy t JOIN users u ON u.UserId=t.UserId WHERE t.turniejId = ? AND u.Login = ?;";
            $stmt_check = mysqli_prepare($conn, $sql_check);
            mysqli_stmt_bind_param($stmt_check, "is", $_SESSION['TurniejId'], $_SESSION['username']);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_bind_result($stmt_check, $existingCount);
            mysqli_stmt_fetch($stmt_check);
            mysqli_stmt_close($stmt_check);

            if ($existingCount == 0) {
                $sql_insert = "INSERT INTO turuserzy (turniejId, UserId) SELECT ?, UserId from users where Login= ?;";
                $stmt_insert = mysqli_prepare($conn, $sql_insert);
                mysqli_stmt_bind_param($stmt_insert, "is", $_SESSION['TurniejId'], $_SESSION['username']);
                mysqli_stmt_execute($stmt_insert);
                mysqli_stmt_close($stmt_insert);
            }
            mysqli_commit($conn);

            $_SESSION['info'] = $lang['joinSuccess'];

            if ($conn) {
                mysqli_close($conn);
            }
            header("Location: joined.php");
        }
    } else {
        $_SESSION['info'] = $lang['invalidCode'];;
        if ($conn) {
            mysqli_close($conn);
        }
        header("Location: logged.php");
        exit();
    }
} else {
    echo "No data";
}
