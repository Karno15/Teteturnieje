<?php
session_start();

require 'connect.php';

// Assuming you have a button click event or some trigger
if (isset($_POST['username']) && isset($_POST['turniejId'])) {
    $username = $_POST['username'];
    $turniejId = $_POST['turniejId'];
    // Your SQL query
    $sql = "INSERT INTO buzzes (UserId, TurniejId, PytId) SELECT u.UserId, t.turniejId, t.CurrentQuest from turnieje t 
    JOIN turuserzy tu ON tu.turniejId=t.TurniejId JOIN users u ON u.UserId=tu.UserId where t.TurniejId= ?  and u.Login= ? ; ";

    try {
        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Bind the parameters
        mysqli_stmt_bind_param($stmt, 'is', $turniejId, $username);

        // Execute the statement
        $execute = mysqli_stmt_execute($stmt);

        // Check the number of affected rows
        $affectedRows = mysqli_stmt_affected_rows($stmt);

        if ($execute && $affectedRows > 0) {
            echo "Buzzed!";
        } else {
            // Handle the case where no rows are affected
            echo "You cant buzz on this tournament!";
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
