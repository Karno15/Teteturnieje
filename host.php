<?php
session_start();

if (!isset($_SESSION['lang'])) {
  $userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
  $_SESSION['lang'] = (stripos($userLanguages[0], 'pl') === 0) ? 'pl' : 'en';
}

$lang = (isset($_SESSION['lang']) ? $_SESSION['lang'] : '');


if (isset($_SESSION['info'])) {
  echo "<div class='info'>";
  echo $_SESSION['info'];
  echo "</div>";
  unset($_SESSION['info']);
}

require "connect.php";

include_once('translation/' . $_SESSION['lang'] . ".php");

if (isset($_POST['formname'], $_SESSION['userid'])) {
  $formname = htmlspecialchars($_POST['formname']);
  $sqlInsert = "INSERT INTO turnieje (TypeId, Creator, Name, Status)
                SELECT 1, m.masterId, ?, 'N' FROM users u
                JOIN masters m ON u.masterId = m.masterId
                WHERE m.masterId = ? LIMIT 1";
  $stmtInsert = $conn->prepare($sqlInsert);
  $stmtInsert->bind_param('si', $formname, $_SESSION['userid']);
  $execute = $stmtInsert->execute();

  if ($execute) {
    $lastTurniejId = $stmtInsert->insert_id;

    $_SESSION['info'] = $lang["turniejCreated"];
    header('Location: edit.php?turniejid=' . $lastTurniejId);
    exit();
  } else {
    $_SESSION['info'] = "Error:" . $stmtInsert->error;
  }
  $stmtInsert->close();
}

if (!isset($_SESSION['userid']) || !isset($_SESSION['username'])) {
  $_SESSION['info'] = $lang["noAccess"];
  header('Location:logged.php');
}

?>

<head>
  <title>TTT-TeTeTurnieje</title>
  <link rel="icon" type="image/gif" href="images/favicon.ico">
  <link rel="stylesheet" href="style.css">
  <script src="jquery/jquery.min.js"></script>
  <script>
    var langses = <?php echo json_encode($_SESSION['lang']); ?>;
    var lang = langses || 'en';
    localStorage.setItem("lang", lang);
  </script>
  <script src="script.js"></script>
  <script src="translation/translation.js"></script>
  <meta http-equiv="refresh" content="x">
</head>

<body>
  <div id="gear"><img src='images/gear.svg'></div>
  <div class='lang' class="lang-select-container">
    <span class="flag" style="cursor: pointer;"></span>
    <select class="lang-select" name="lang" style="display: none;">
      <option value="pl" <?php echo ($lang === 'pl') ? 'selected' : ''; ?>></option>
      <option value="en" <?php echo ($lang === 'en') ? 'selected' : ''; ?>></option>
    </select>
  </div>
  <div class='lang' class="lang-select-container">
    <span class="flag" style="cursor: pointer;"></span>
    <select class="lang-select" name="lang" style="display: none;">
      <option value="pl" <?php echo ($lang === 'pl') ? 'selected' : ''; ?>></option>
      <option value="en" <?php echo ($lang === 'en') ? 'selected' : ''; ?>></option>
    </select>
  </div>
  <div class="tooltiplang"></div>
  <script>
    $(document).ready(function() {
      $("#newTurniej").html(translations['newTurniej'][lang] + ":");
      $("#createNewTurniej").html(translations['createNewTurniej'][lang]);
      $("#turniejId").html(translations['turniejId'][lang]);
      $("#turniejName").html(translations['turniejName'][lang]);
      $("#turniejDate").html(translations['turniejDate'][lang]);
      $("#turniejCode").html(translations['turniejCode'][lang]);
      $("#turniejStatus").html(translations['turniejStatus'][lang]);
      $("#turniejEdit").html(translations['turniejEdit'][lang]);
      $("#turniejStart").html(translations['turniejStart'][lang]);
      $("#tipHover").html(translations['tipHover'][lang]);
      $("#back").html(translations['return'][lang]);
      $("#emptyTurniej").html(translations['emptyTurniej'][lang]);
    });
  </script>
  <div id='popup'></div>
  <div class="popup-overlay"></div>
  <div id="main-container">
    <div id='head'>
      <span>TETETURNIEJE</span>
    </div>
    <div id='content'>
      <div class='startpopup'>
        <form method='POST'>
          <span id='newTurniej'></span><br>
          <input type='text' name='formname' required class='inputy'><br> <br>
          <button class="button-85" type='submit' margin-top='0px' id='createNewTurniej'></button>
        </form>
      </div>
      <br>
      <div class='startpopup'>
          <table class='datatable'>
            <thead>
              <tr>
                <th id='turniejId'></th>
                <th id='turniejName'></th>
                <th id='turniejDate'></th>
                <th id='turniejCode'></th>
                <th id='turniejStatus'></th>
                <th id='turniejEdit'></th>
                <th id='turniejStart'></th>
              </tr>
            </thead>
            <tbody>
              <?php
              $lang = mysqli_real_escape_string($conn, $_SESSION['lang']);

              $query = "SELECT t.TurniejId, t.Name, t.Created, t.Code, d.Label, d.Description FROM turnieje t
            JOIN dictionary d ON d.Symbol=t.Status where t.Creator= " . $_SESSION['userid'] . "
             and Type='quest.Status' and Language ='" . $lang . "' order by t.Created desc;";
              $result = mysqli_query($conn, $query);

              if (!$result) {
                die("Query failed");
              }
              if (mysqli_num_rows($result) == 0) {
                echo "<tr><td id='emptyTurniej' colspan='7'></td></tr>";
              } else {
                while ($row = mysqli_fetch_assoc($result)) {
                  echo "<tr>";
                  echo "<td>" . $row['TurniejId'] . "</td>";
                  echo "<td>" . $row['Name'] . "</td>";
                  echo "<td>" . $row['Created'] . "</td>";
                  echo "<td>" . $row['Code'] . "</td>";
                  echo "<td>" . $row['Label'] . "<img class='ask' src='images/questionmark.svg' alt='questionmark' ";
                  echo "title='" . $row['Description'] . "' ></td>";
                  echo "<td> <a href='edit.php?turniejid=" . $row['TurniejId'] . "' ><img class='wrench' src='images/edit.svg' alt='edit' height='40px' width='40px'</a></td>";
                  echo "<td> <a class='startLink' data-turniejid='" . $row['TurniejId'] . "'><img class='monke' src='images/maupka.webp' alt='start' height='40px' width='40px'></a></td>";
                  echo "</tr>";
                }
              }
              mysqli_close($conn);
              ?>
            </tbody>
          </table>
          <span class="disclaimer" id='tipHover'></span>
        </div>
      <button onclick="location.href='logged.php'" id='back' class='codeconfrim'>
      </button>
    </div>
  </div>
</body>