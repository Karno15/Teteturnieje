<?php
session_start();
if (isset($_SESSION['info'])) {
  echo "<div class='info'>";
  echo $_SESSION['info'];
  echo "</div>";
  unset($_SESSION['info']);
}

require "connect.php"; // Assuming you have a connection script

if (!isset($_SESSION['userid']) || !isset($_SESSION['username'])) {
  $_SESSION['info'] = 'Brak dostępu';
  header('Location:logged.php');
} else {
  // Get the user's ID from the session
  $userId = $_SESSION['userid'];
  $username = $_SESSION['username'];

  $sql = "SELECT masterId FROM users u where Login ='$username';";

  $result = $conn->query($sql);

  if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $masterId = $row['masterId'];

    // Check if the TurniejId's creator matches the user's ID - if no do the error
    if ($masterId != $userId) {
      $_SESSION['info'] = 'Brak dostępu';
      header('Location:error.php');
    }
  } else {
    $_SESSION['info'] = 'Brak dowiązania!';
    header('Location:error.php');
  }
}
?>

<head>
  <title>TTT-TeTeTurnieje</title>
  <link rel="icon" type="image/gif" href="images/favicon.ico">
  <link rel="stylesheet" href="style.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300&display=swap" rel="stylesheet">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
  <script src="script.js"></script>
  <meta http-equiv="refresh" content="x">
</head>

<body>

  <div id="main-container">
    <div id='head'>
      <span>TETETURNIEJE</span>
    </div>

    <div id='content'>
      <div class='startpopup'>
        <form action='edit.php' method='POST'>
          Podaj nazwę nowego turnieju:<br>
          <input type='text' name='formname' required class='inputy'><br> <br>
          <button class="button-85" type='submit' margin-top='0px'>Utwórz nowy turniej</button>
        </form>
      </div>
      <br>
      <div class='startpopup'>
        <div id='pass'>
          <table class='datatable'>
            <thead>
              <tr>
                <th>ID</th>
                <th>Nazwa</th>
                <th>Data</th>
                <th>Kod</th>
                <th>Status</th>
                <th>Edycja</th>
                <th>Start</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Fetch records from the "turnieje" table
              $query = "SELECT t.TurniejId, t.Name, t.Created, t.Code, d.Label, d.Description FROM turnieje t
            JOIN dictionary d ON d.Symbol=t.Status 
            where t.Creator= " . $_SESSION['userid'] . "
            order by t.Created desc;";
              $result = mysqli_query($conn, $query);

              // Check for errors in the query
              if (!$result) {
                die("Query failed: " . mysqli_error($connection));
              }

              // Loop through the results and display them in HTML
              while ($row = mysqli_fetch_assoc($result)) {
                echo "<tr>";
                echo "<td>" . $row['TurniejId'] . "</td>";
                echo "<td>" . $row['Name'] . "</td>";
                echo "<td>" . $row['Created'] . "</td>";
                echo "<td>" . $row['Code'] . "</td>";
                echo "<td>" . $row['Label'] . "<img class='ask' src='images/questionmark.svg' alt='questionmark' ";
                echo "title='" . $row['Description'] . "' ></td>";
                echo "<td> <a href='edit.php?turniejid=" . $row['TurniejId'] . "' ><img src='images/edit.png' alt='edit' height='40px' width='40px'</a></td>";
                echo "<td> <a class='startLink' data-turniejid='" . $row['TurniejId'] . "'><img src='images/maupka.webp' alt='start' height='40px' width='40px'></a></td>";
                echo "</tr>";
              }

              // Close the database connection
              mysqli_close($conn);
              ?>
            </tbody>
          </table>
        </div>
        <span class="disclaimer">Tip: Najedź na
          <img src='images/questionmark.svg' alt='questionmark' height=10 width=10> przy danym statusie aby zobaczyć jego opis.</span>
      </div>
      <button onclick="location.href='logged.php'" id='back' class='codeconfrim'>
                            POWRÓT</button>
      <div>
      </div>
    </div>
</body>
<div id='popup'></div>