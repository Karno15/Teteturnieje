<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['turniejId']) && isset($_POST['kodTurnieju'])) {
        require "connect.php"; // Załóżmy, że plik connect.php zawiera konfigurację połączenia z bazą danych
        
        $_SESSION['TurniejId']=$_POST['turniejId'];
        
        $turniejId = mysqli_real_escape_string($conn, $_POST['turniejId']);
        $kodTurnieju = mysqli_real_escape_string($conn, $_POST['kodTurnieju']);


        //sprawdzenie czy kod istnieje
        $checkSql = "SELECT * FROM turnieje WHERE Code = '$kodTurnieju' and TurniejId !=  $turniejId ";
        $result = $conn->query($checkSql);

        if ($result->num_rows > 0) {
            echo "Błąd: Taki kod już istnieje";
        } else {

        // Wykonaj zapytanie do bazy danych, aby zaktualizować kod turnieju
        $sql = "UPDATE turnieje SET Code =$kodTurnieju, Status='A' WHERE TurniejId = $turniejId";
         
        $_SESSION['leader']=$turniejId; //znacznik sesji sugeruje turniej którego leaderem jest user
        
        
        if ($conn->query($sql) === TRUE) {
            echo "success";
        } else {
            echo "Nieprawidłowy kod turnieju. ";
            //for debbuging echo . $conn->error;
        }
        }
        $conn->close();
        
    } else {
        echo "Nieprawidłowe dane żądania.";
    }
} else {
    echo  "Nieprawidłowa metoda żądania.";
}
?>
