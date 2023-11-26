<?php
session_start();
if (isset($_SESSION['info'])) {
  echo "<div class='info'>";
  echo $_SESSION['info'];
  echo "</div>";
  unset($_SESSION['info']);
}


if (!isset($_SESSION['username']) or !isset($_SESSION['userid']))
  header('Location:error.php');
else
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
            require('connect.php'); // Include the connection file

            // Fetch records from the "turnieje" table
            $query = "SELECT t.TurniejId, t.Name, t.Created, t.Code, t.Status FROM
      turnieje t JOIN users u ON u.UserId=t.Creator where Login='" . $_SESSION['username'] . "' 
     order by t.Created desc";
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
              echo "<td>" . $row['Status'] . "</td>";
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
      </div>

      <div>
      </div>
    </div>
</body>
<div id='popup'></div>