<?php

session_start();

if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}


require('connect.php');

if (isset($_POST['formname'])) {
    $sql = "INSERT INTO `turnieje`( `TypeId`, `Creator`, `Name`, `Status`)
select 1, m.masterId, '". $_POST['formname'] ."', 'N' from users u JOIN masters m ON
u.masterId=m.masterId where m.masterId= ". $_SESSION['userid'] ."  limit 1; ";

    $execute = $conn->query($sql);

    $query = "SELECT TurniejId FROM turnieje order by TurniejId desc limit 1";
    $result = mysqli_query($conn, $query);

    $resultrow = mysqli_fetch_row($result);

    $_GET['turniejid'] = $resultrow[0];
}






if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = 'Nie znaleziono turnieju';
    header('Location:host.php');
} elseif (!isset($_SESSION['userid'])) {
    $_SESSION['info'] = 'Brak dostępu';
    header('Location:index.php');
} else {
    // Get the user's ID from the session
    $userId = $_SESSION['userid'];

    // Get the TurniejId from the query parameter
    $turniejId = $_GET['turniejid'];

    // Connect to the database
    require "connect.php"; // Assuming you have a connection script

    // Query to check if the TurniejId belongs to the user
    $sql = "SELECT Creator FROM turnieje WHERE TurniejId = $turniejId";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creatorId = $row['Creator'];

        // Check if the TurniejId's creator matches the user's ID - if yes do the rest
        if ($creatorId == $userId) {


            if (isset($_POST["delete_question"])) {
                $question_id = $_POST["question_id"];

                // Perform a query to delete the question
                $sql = "DELETE FROM pytania WHERE PytId = $question_id;
            DELETE FROM pytaniapoz WHERE PytId = $question_id;
            DELETE FROM prawiodpo WHERE PytId = $question_id;";

                if (!$conn->multi_query($sql)) {
                    $_SESSION['info'] = "Error description: " . $mysqli->error;
                } else {
                    $_SESSION['info'] = "Pytanie zostało usunięte.";
                    header("Location: edit.php?turniejid=" . $_GET["turniejid"]);
                    exit();
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
            </head>

            <body>
                <div class="popup-overlay"></div>
                <div id="main-container">
                    <div id='head'>
                        <span>TETETURNIEJE</span>
                    </div>

                    <div id='content' class='fonty'>




                        <div class='startpopup'>
                            <?php
                            echo "<form action='editquest.php?turniejid=" . $_GET['turniejid'] . "' method='POST'>";
                            ?>
                            <br> <button class="button-85" type='submit' margin-top='0px'>Dodaj nowe pytanie</button>
                            </form>
                        </div><br>

                        <div class='startpopup'>
                            <div id='pass'>
                                <table class='datatable'>
                                    <thead>
                                        <tr>
                                            <th>ID pytania</th>
                                            <th>Typ Pytania</th>
                                            <th>Kategoria</th>
                                            <th>Punkty</th>
                                            <th>Zobacz</th>
                                            <th>Usuń</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                            <?php

                            // Fetch records from the "turnieje" table
                            $query = "SELECT * FROM pytania p where TurniejId=" . $_GET['turniejid'];
                            $result = mysqli_query($conn, $query);

                            // Check for errors in the query
                            if (!$result) {
                                die("Query failed: " . mysqli_error($conn));
                            }
                            //PytId		Quest	TypeId	Rewards
                            // Loop through the results and display them in HTML
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<tr>";
                                //        echo "<td>" . $row['Order'] . "</td>"; -TO DO
                                echo "<td>" . $row['PytId'] . "</td>";
                                echo "<td>";
                                if ($row['TypeId'] == 1) {
                                    echo 'Zamknięte';
                                } elseif ($row['TypeId'] == 2) {
                                    echo 'Otwarte';
                                }
                                echo "</td>";
                                echo "<td>" . $row['Category'] . "</td><td>";
                                echo $row['IsBid'] == 1 ? 'obstawiane' : $row['Rewards'];
                                echo "</td><td><button class='codeconfrim' onclick='pokazPytanie(" . $row['PytId'] . ")'>Zobacz</button></td>";
                                echo "<td><form method='post'>
                <input type='hidden' name='question_id' value='" . $row['PytId'] . "'>
                  <button type='submit' name='delete_question' onclick='return confirm(\"Czy na pewno chcesz usunąć to pytanie?\")'
                  style='background: none; border: none; cursor: pointer;'>
                   <img src='images/trash.png' alt='trash' height='40px' width='40px'>
                  </button>
                        </form>
                </td>";
                                echo "</tr>";
                            }
                        } else {
                            $_SESSION['info'] = 'Brak dostępu';
                            header('Location:error.php');
                        }
                    } else {
                        $_SESSION['info'] = 'Brak dostępu';
                        header('Location:error.php');
                    }
                }
                // Close the database connection
                            ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button onclick="location.href='host.php'" id='back' class='codeconfrim'>
                            POWRÓT</button>

                    </div>

                </div>

                <div id='popup'></div>
            </body>