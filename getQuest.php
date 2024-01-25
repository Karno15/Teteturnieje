<?php
session_start();

if (isset($_SESSION['userid']) && isset($_GET['id'])) {
    $id_pytania = (int)$_GET['id'];

    // Validate the input to ensure it is an integer
    if ($id_pytania <= 0) {
        die("Invalid input");
    }

    require('connect.php');

    // Get the logged-in user's ID from the session
    $loggedInUserId = $_SESSION['userid'];
    $stmt = $conn->prepare("SELECT p.Quest, p.After, p.TypeId, t.Creator FROM `pytania` p JOIN turnieje t ON p.TurniejId=t.TurniejId WHERE p.PytId = ?");
    $stmt->bind_param("i", $id_pytania);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Error: " . $conn->error);
    }

    // Fetch the result
    while ($row1 = $result->fetch_assoc()) {
        // Check if the logged-in user is the creator
        if ($row1['Creator'] != $loggedInUserId) {
            die("Access denied.");
        }

        echo "<div>" . $row1['Quest'] . "</div>";
        echo "<hr>";
        if (isset($row1['Quest'])) {
            $after = $row1['After'];
        }
        $typeId = $row1['TypeId'];
    }

    // Use prepared statement for the second query
    $stmt = $conn->prepare("SELECT pp.PytId, pp.PozId, Value, po.PozId as 'correct' FROM `pytaniapoz` pp LEFT JOIN prawiodpo po ON po.PytId=pp.PytId WHERE pp.PytId=?");
    $stmt->bind_param("i", $id_pytania);
    $stmt->execute();
    $result1 = $stmt->get_result();

    // Check if the second query was successful
    if (!$result1) {
        die("Error: " . $conn->error);
    }

    // Display the options
    if (mysqli_num_rows($result1) > 0 && $typeId == 1) {
        echo "<div class='quest-options'>";
        while ($row = $result1->fetch_assoc()) {
            echo "<div class='quest-option' ";
            if ($row['PozId'] == $row['correct'])
                echo "id='correct'";
            echo ">" . base64_decode($row['Value']) . "</div>";
        }
        echo "</div><br>";
    }

    // Display the 'After' content if it exists
    if (isset($after)) {
        if (!is_null($after)) {
            echo "<div>" . $after . "</div>";
        }
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
} else {
    echo "No Access";
}
