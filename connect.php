<?php

$servername = "localhost";
$sa = "root";
$sa_pass = "";
$dbname = "id20965121_teteturnieje";

$conn = new mysqli($servername, $sa, $sa_pass, $dbname);
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
