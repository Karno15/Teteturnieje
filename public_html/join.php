<?php
require "connect.php";

session_start();

if (isset($_POST["login"]) && isset($_POST["gamecode"])) {
    $gc = $_POST["gamecode"];
    $login = $_POST["login"];

 // Przygotowanie zapytania
    $sql = "SELECT TurniejId,Name FROM turnieje WHERE Status='A' AND Code=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $gc);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($result->num_rows > 0) {
        $row = mysqli_fetch_assoc($result);
        
        $_SESSION['TurniejId'] = $row['TurniejId'];
        $_SESSION['Name'] = $row['Name'];
        
    $sql = "SELECT UserId, Login FROM users WHERE Login=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $login);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

if ($result->num_rows == 0) {
    $sql = "INSERT INTO users (Login, LastLogged) VALUES (UPPER(?), CURRENT_TIMESTAMP())";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $login);
    $execute = mysqli_stmt_execute($stmt);

    // Pobierz UserId nowo utworzonego użytkownika
    $userid = mysqli_insert_id($conn);
} else {
    $sql = "UPDATE users SET LastLogged=CURRENT_TIMESTAMP() WHERE Login=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $login);
    $execute = mysqli_stmt_execute($stmt);

    // Pobierz UserId istniejącego użytkownika
    $row = mysqli_fetch_assoc($result);
    $userid = $row['UserId'];
}

// Przypisz UserId do sesji
$_SESSION["userid"] = $userid;
$_SESSION["username"] = strtoupper($login);
        
        
// Rozpoczęcie transakcji
mysqli_begin_transaction($conn);

// Sprawdzenie czy rekord istnieje
$sql_check = "SELECT COUNT(*) FROM turuserzy t JOIN users u ON u.UserId=t.UserId WHERE t.turniejId = ? AND u.Login = ?;";
$stmt_check = mysqli_prepare($conn, $sql_check);
mysqli_stmt_bind_param($stmt_check, "is", $_SESSION['TurniejId'], $_SESSION['username']);
mysqli_stmt_execute($stmt_check);
mysqli_stmt_bind_result($stmt_check, $existingCount);
mysqli_stmt_fetch($stmt_check);
mysqli_stmt_close($stmt_check);

if ($existingCount == 0) {
    // Wstawianie rekordu
    $sql_insert = "INSERT INTO turuserzy (turniejId, UserId) SELECT ?, UserId from users where Login= ?;";
    $stmt_insert = mysqli_prepare($conn, $sql_insert);
    mysqli_stmt_bind_param($stmt_insert, "is", $_SESSION['TurniejId'], $_SESSION['username']);
    mysqli_stmt_execute($stmt_insert);
    mysqli_stmt_close($stmt_insert);
}

// Zakończenie transakcji
mysqli_commit($conn);
        
        $_SESSION['info'] = "Dołączono do turnieju. ";
        
        // Zamknij połączenie tylko jeśli jest otwarte
        if ($conn) {
            mysqli_close($conn);
        }
        // Przekieruj na inną stronę
        header("Location: joined.php");
    } else {
        $_SESSION['info'] = "Nie znaleziono turnieju";
        
        if ($conn) {
            mysqli_close($conn);
        }
        header("Location: index.php");
    }
} else {
    echo "error";
}

?>
