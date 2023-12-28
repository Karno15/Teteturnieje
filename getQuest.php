<?php
// Pobierz id pytania przekazane w parametrze GET
// Pobranie ID pytania z zabezpieczeniem przed SQL injection
if (isset($_GET['id'])) {
    $id_pytania = (int)$_GET['id'];


    // Wykonanie połączenia z bazą danych i sprawdzenie, czy udało się nawiązać połączenie


    require('connect.php');
    // Zabezpieczenie ID pytania przed SQL injection
    $id_pytania = $conn->real_escape_string($id_pytania);

    // Wykonanie zapytania SQL z przygotowanym wyrażeniem
    $sql = "SELECT Quest, After, TypeId FROM `pytania` WHERE PytId = $id_pytania";
    $result = $conn->query($sql);

    // Sprawdzenie, czy zapytanie powiodło się
    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    // Wyświetlenie zawartości tabeli "pytania"

    while ($row1 = $result->fetch_assoc()) {
        echo "<div>" . $row1['Quest'] . "</div>";
        echo "<hr>";
        if (isset($row1['Quest'])) {
            $after = $row1['After'];
        }
        $typeId = $row1['TypeId'];
    }

    $sql = "SELECT pp.PytId,pp.PozId,Value,po.PozId as 'correct' FROM `pytaniapoz` pp LEFT JOIN prawiodpo po ON po.PytId=pp.PytId where pp.PytId=$id_pytania;";

    //do zmainy
    $result1 = $conn->query($sql);

    // Sprawdzenie, czy zapytanie powiodło się
    if (!$result1) {
        die("Query failed: " . $conn->error);
    }

    // Wyświetlenie zawartości tabeli "pytania"


    if (mysqli_num_rows($result1) > 0 && $typeId==1) {
        echo    "<div class='quest-options'>";
        while ($row = $result1->fetch_assoc()) {
            echo    "<div class='quest-option' ";
            if ($row['PozId'] == $row['correct'])
                echo "id='correct'";
            echo ">" . base64_decode($row['Value']) . "</div>";
        }
        echo    "</div><br>";
    }



    if (!is_null($after)) {
        echo "<div>" . $after . "</div>";
    }

    // Zamknięcie połączenia z bazą danych
    $conn->close();
} else {
    // Obsługa błędu, jeśli brak parametru 'id' w żądaniu
    // Na przykład przekierowanie na inny widok lub wyświetlenie błędu
    echo "Błąd danych";
}
