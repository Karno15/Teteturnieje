<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

require 'connect.php';
$userId = '';

if (isset($_SESSION['userid'])) {
    $userId = $_SESSION['userid'];
}

if (isset($_SESSION['TurniejId'])) {
    $turniejId = $_SESSION['TurniejId'];
}

if (isset($_SESSION['TurniejId'])) {
    $turniejId = $_SESSION['TurniejId'];
}



// Query to check if the TurniejId belongs to the user
$sql = "SELECT Creator FROM turnieje WHERE TurniejId = $turniejId";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $creatorId = $row['Creator'];

    // Check if the TurniejId's creator matches the user's ID - if yes do the rest
    if ($creatorId == $userId and $_SESSION['leader']) {

        if (isset($_POST['login']) && isset($_POST['newScore']) && isset($_SESSION['TurniejId'])) {
            $login = $_POST['login'];
            $newScore = $_POST['newScore'];
            $turniejId = $_SESSION['TurniejId']; // Odbierz TurniejId
            

            $sql = "UPDATE turuserzy t JOIN users u ON u.UserId=t.UserId SET t.CurrentScore = ? WHERE u.Login =?
    AND t.turniejId = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, 'isi', $newScore, $login, $turniejId); // Uwzględnij TurniejId

            if (mysqli_stmt_execute($stmt)) {
                echo "Zaktualizowano wynik.";
            } else {
                echo "Błąd wykonania zapytania: " . mysqli_stmt_error($stmt);
            }
            mysqli_close($conn);
        } else {
            echo "Błąd danych";
        }
    } else {
        echo "Brak uprawnień";
    }
} else {
    echo "Nie znaleziono turnieju";
}
