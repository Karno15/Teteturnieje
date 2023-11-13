<?php

session_start(); // Start the session

// Function to validate user credentials
function validateUser($username, $password) {

require "connect.php";

    // Sanitize the user input to prevent SQL injection
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);
    
    $password = md5($password);
    // Query the database for the user
    $query = "SELECT UserId,Login FROM users WHERE Login=UPPER('$username') AND Pass='$password'";
    $result = mysqli_query($conn, $query);
    
    $resultrow = mysqli_fetch_row($result);

    if ($result) {
        if (mysqli_num_rows($result) === 1) {
            //update last login
            $query = "UPDATE users SET LastLogged=CURRENT_TIMESTAMP() where UserID=".$resultrow[0];
            $result = mysqli_query($conn, $query);
            
            // User found, set up the session
            $_SESSION['username'] = strtoupper($username);
            $_SESSION['userid']=$resultrow[0];
            return true;
        } else {
            // User not found or invalid credentials
            return false;
        }
    } else {
        // Error executing the query
        echo "Error: " . mysqli_error($conn);
        return false;
    }
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['login'];
    $password = $_POST['pass'];

    if (validateUser($username, $password)) {
        // Redirect the user to a logged-in page
        header("Location: host.php");
        exit();
    } else {
        // Show an error message
       $_SESSION['info'] = "Błędny login lub hasło";
        header("Location: index.php");
        exit();
    }

}
?>