<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['turniejId']) && isset($_POST['kodTurnieju']) && $_POST['kodTurnieju'] > 0 and $_POST['kodTurnieju'] <= 9999) {
        require "connect.php"; // Załóżmy, że plik connect.php zawiera konfigurację połączenia z bazą danych


        $turniejId = mysqli_real_escape_string($conn, $_POST['turniejId']);
        $kodTurnieju = mysqli_real_escape_string($conn, $_POST['kodTurnieju']);


        //sprawdzenie czy kod istnieje
        $checkSql = "SELECT turniejId FROM turnieje WHERE Code = '$kodTurnieju' and TurniejId !=  $turniejId ";
        $result = $conn->query($checkSql);

        if ($result->num_rows > 0) {
            echo "Błąd: Taki kod już istnieje";
        } else {

            //sprawdzenie czy pytania istnieją
            $countSql = "SELECT PytId FROM pytania WHERE TurniejId = $turniejId;";
            $resultcount = $conn->query($countSql);
            if ($resultcount->num_rows == 0) {
                echo "Błąd: Brak pytań w turnieju";
            } else {
                // Wykonaj zapytanie do bazy danych, aby zaktualizować kod turnieju
                $sql = "UPDATE turnieje SET Code =$kodTurnieju, Status='A' WHERE TurniejId = $turniejId";
                if ($conn->query($sql) === TRUE) {
                    $_SESSION['leader'] = $turniejId; //znacznik sesji sugeruje turniej którego leaderem jest user

                    $_SESSION['TurniejId'] = $_POST['turniejId'];
                    echo "success";
                } else {
                    echo "Nieprawidłowy kod turnieju. ";
                    //for debbuging echo . $conn->error;
                }
            }
        }
        $conn->close();
    } else {
        echo "Nieprawidłowy kod.";
    }
} else {
    echo  "Nieprawidłowa metoda żądania.";
}
