<?php
session_start();

require 'connect.php';

// Assuming you have a button click event or some trigger
if (isset($_POST['login']) && isset($_POST['pts']) && isset($_POST['answer']) && isset($_POST['answer'])) {
    $login = $_POST['login'];
    $pts = $_POST['pts'];
    $answer = $_POST['answer'];
    $turniejId = $_POST['turniejId'];
    
    if ($answer == 0) {
        $pts = -1 * abs($_POST['pts']);
    }
    // Your SQL query
    $sql = "INSERT INTO answers (Login, Points, Answer, TurniejId) VALUES (?, ?, ?, ?)";

    try {
        // Prepare the statement
        $stmt = mysqli_prepare($conn, $sql);

        // Bind the parameters
        mysqli_stmt_bind_param($stmt, 'sdii', $login, $pts, $answer, $turniejId);

        // Execute the statement
        $execute = mysqli_stmt_execute($stmt);

        if ($execute) {
            echo "Answered!";
        } else {
            // Handle any other errors
            echo "Error answer other!";
        }

        // Close the statement
        mysqli_stmt_close($stmt);
    } catch (mysqli_sql_exception $exception) {
        // Handle exceptions (e.g., log the error)
        echo "Error answer sql!".$sql;
    } finally {
        // Close the connection
        mysqli_close($conn);
    }
} else {
    // Handle the case where the expected parameters are not set
    echo "Error: Required parameters are missing!";
}
