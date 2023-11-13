<?php
    $servername = "localhost";
    $sa = "id20965121_sa";
    $sa_pass = "x3t33dM.2023";
    $dbname = "id20965121_teteturnieje";

    // Create connection

    $conn = new mysqli($servername, $sa, $sa_pass, $dbname);
    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }
 
?>
