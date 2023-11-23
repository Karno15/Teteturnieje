<?php
session_start();

require 'connect.php';

if (!$_POST['pytId']) {
    // Nie udało się pobrać identyfikatora turnieju z sesji
    echo json_encode(array("error" => "Brak pytania."));
    exit();
}

// Assuming you have a button click event or some trigger
if (isset($_POST['userId']) && isset($_POST['turniejId']) && isset($_POST['pytId'])) {
    $userId = $_POST['userId'];
    $turniejId = $_POST['turniejId'];
    $pytId = $_POST['pytId'];
    // Your SQL query
    $sql = "INSERT INTO buzzes (UserId, TurniejId, PytId) VALUES (?, ?, ?)";

    try {
        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Bind the parameters
        mysqli_stmt_bind_param($stmt, 'iii', $userId, $turniejId, $pytId);

        // Execute the statement
        $execute = mysqli_stmt_execute($stmt);

        if ($execute) {
            echo "Buzzed!";
        } else {
            // Handle any other errors
            echo "Error buzz other!";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $exception) {
        // Handle exceptions (e.g., log the error)
        echo "Error buzz sql!";
    } finally {
        // Close the connection
        mysqli_close($conn);
    }
} else {
    // Handle the case where the expected parameters are not set
    echo "Error: Required parameters are missing!";
}
