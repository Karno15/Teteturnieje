<?php
// Plik sprawdz_status.php
session_start();

require('connect.php');

// Wykonaj zapytanie w celu pobrania statusu turnieju i organizera
$statusQuery = mysqli_query($conn, "SELECT t.Status,u.Login AS 'Creator' FROM turnieje t JOIN
users u ON u.UserId=t.Creator WHERE TurniejId = " . $_SESSION['TurniejId']);
$statusRow = mysqli_fetch_assoc($statusQuery);
$status = $statusRow['Status'];
$creator = $statusRow['Creator'];

// Wykonaj zapytanie w celu pobrania listy uczestników obecnego turnieju
$participantsQuery = mysqli_query($conn, "SELECT Login, CurrentScore FROM turuserzy t JOIN users u ON u.UserId=t.UserId
WHERE turniejid = " . $_SESSION['TurniejId']);

$participants = array();
while ($participantRow = mysqli_fetch_assoc($participantsQuery)) {
    $participants[] = $participantRow;
}

// Utwórz tablicę, która zawiera status i uczestników
$response = array(
    "status" => $status,
    "participants" => $participants,
    "creator" => $creator
);

mysqli_close($conn);

// Zwróć dane w formie JSON
echo json_encode($response);
