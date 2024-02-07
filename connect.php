<?php


//$servername = "localhost";
//$sa = "root";
//$sa_pass = "";
//$dbname = "id20965121_teteturnieje";

//FOR PROD:
$servername = "localhost";
$sa = "u843275928_sa";
$sa_pass = "x3t33dM.2023";
$dbname = "u843275928_teteturnieje";

$conn = new mysqli($servername, $sa, $sa_pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
