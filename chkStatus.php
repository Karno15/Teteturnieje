<?php
// Plik sprawdz_status.php
session_start();

require('connect.php');

if (isset($_SESSION['userid']) && isset($_SESSION['TurniejId'])) {
    $turniejId = $_SESSION['TurniejId'];

    // Wykonaj zapytanie w celu pobrania statusu turnieju i organizera
    $statusQuery = "SELECT t.Status, m.Login AS 'Creator', t.CurrentQuest FROM turnieje t
    JOIN masters m ON m.masterId=t.Creator 
    WHERE t.TurniejId= ?";
    $statusStmt = $conn->prepare($statusQuery);
    $statusStmt->bind_param("i", $turniejId);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();
    $statusRow = $statusResult->fetch_assoc();

    $statusStmt->close();

    $response = array();

    if ($statusResult->num_rows > 0) {
        $status = $statusRow['Status'];
        $creator = $statusRow['Creator'];
        $currentQuest = $statusRow['CurrentQuest'];

        $participantsQuery = "SELECT Login, ROUND(CurrentScore, 3) as 'CurrentScore' FROM
     turuserzy t JOIN users u ON u.UserId=t.UserId WHERE turniejid = ?";
        $participantsStmt = $conn->prepare($participantsQuery);
        $participantsStmt->bind_param("i", $turniejId);
        $participantsStmt->execute();
        $participantsResult = $participantsStmt->get_result();
        $participants = array();

        while ($participantsRow = $participantsResult->fetch_assoc()) {
            $participants[] = $participantsRow;
        }

        $participantsStmt->close();

        // Utwórz tablicę, która zawiera status i uczestników
        $response = array(
            "status" => $status,
            "participants" => $participants,
            "creator" => $creator,
            "currentQuest" => $currentQuest
        );
    } else {
        // Jeżeli brak danych, zwróć odpowiednią informację
        $response['error'] = 'Brak pytań';
    }

    mysqli_close($conn);

    // Zwróć dane w formie JSON
    echo json_encode($response);
} else {
    echo "Błąd danych";
}
