<?php

session_start();


if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}

if (isset($_GET['info'])) {
    echo "<div class='info'>";
    echo $_GET['info'];
    echo "</div>";
}


require('connect.php');


include_once('translation/' . $_SESSION['lang'] . ".php");



if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = $lang['notFound'];
    header('Location:host.php');
} elseif (!isset($_SESSION['userid'])) {
    $_SESSION['info'] = $lang['noAccess'];
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

                // Prepare the DELETE statement for pytania table
                $stmt1 = $conn->prepare("DELETE FROM pytania WHERE PytId = ?");
                $stmt1->bind_param("i", $question_id);

                // Prepare the DELETE statement for pytaniapoz table
                $stmt2 = $conn->prepare("DELETE FROM pytaniapoz WHERE PytId = ?");
                $stmt2->bind_param("i", $question_id);

                // Prepare the DELETE statement for prawiodpo table
                $stmt3 = $conn->prepare("DELETE FROM prawiodpo WHERE PytId = ?");
                $stmt3->bind_param("i", $question_id);

                // Execute the DELETE statements
                $stmt1->execute();
                $stmt2->execute();
                $stmt3->execute();

                // Check for errors in the prepared statements
                if ($stmt1->error || $stmt2->error || $stmt3->error) {
                    $_SESSION['info'] = "Error description: " . $stmt1->error . $stmt2->error . $stmt3->error;
                } else {
                    $_SESSION['info'] = $lang['questionDeleted'];
                    header("Location: edit.php?turniejid=" . $_GET["turniejid"]);
                    exit();
                }

                // Close prepared statements
                $stmt1->close();
                $stmt2->close();
                $stmt3->close();
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
            </head>

            <body>
                <script>
                    $(document).ready(function() {
                        $("#back").html(translations['return'][lang]);
                        $('#questId').html(translations['questId'][lang]);
                        $('#questType').html(translations['questType'][lang]);
                        $('#questCat').html(translations['questCat'][lang]);
                        $('#questPts').html(translations['questPts'][lang]);
                        $('#questSee').html(translations['questSee'][lang]);
                        $('#questEdit').html(translations['questEdit'][lang]);
                        $('#questDelete').html(translations['questDelete'][lang]);
                        $('#addQuest').html(translations['addQuest'][lang]);
                        $('#editGrid').html(translations['editGrid'][lang]);
                    });
                </script>
                <div class="popup-overlay"></div>
                <div id="main-container">
                    <div id='head'>
                        <span>TETETURNIEJE</span>
                    </div>

                    <div id='content' class='fonty'>
                        <div class='startpopup' style='flex-direction: row; justify-content: space-around'>
                            <?php
                            echo "<form action='editquest.php?turniejid=" . $_GET['turniejid'] . "' method='POST'>";
                            ?>
                            <button class="button-85" type='submit' margin-top='0px' id='addQuest'></button>
                            </form>
                            <?php
                            echo "<form action='editgrid.php?turniejid=" . $_GET['turniejid'] . "' method='POST' >";
                            ?>
                            <button class="button-85" type='submit' margin-top='0px' id='editGrid'></button>
                            </form>
                        </div><br>

                        <div class='startpopup'>
                            <div id='pass'>
                                <table class='datatable'>
                                    <thead>
                                        <tr>
                                            <th id='questId'></th>
                                            <th id='questType'></th>
                                            <th id='questCat'></th>
                                            <th id='questPts'></th>
                                            <th id='questSee'></th>
                                            <th id='questEdit'></th>
                                            <th id='questDelete'></th>
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
                                    echo $lang["closed"];
                                } elseif ($row['TypeId'] == 2) {
                                    echo $lang["open"];
                                }
                                echo "</td>";
                                echo "<td>" . $row['Category'] . "</td><td>";
                                echo $row['IsBid'] == 1 ? $lang["betting"] : $row['Rewards'];
                                echo "</td><td><img class='view' src='images/unowneyeclose.png' onclick='pokazPytanie(" . $row['PytId'] . ")' alt='unownclose' height='40px' width='40px'></button></td>";
                                echo "<td> <a href='editquest.php?turniejid=" . $row['TurniejId'] . "&pytid=" . $row['PytId'] . "'><img class='wrench' src='images/edit.svg' alt='edit' height='40px' width='40px'</a></td>";
                                echo "<td><form method='post'>
        <input type='hidden' name='question_id' value='" . $row['PytId'] . "'>
        <button type='submit' name='delete_question' onclick=\"return confirm('" . $lang["deleteConfirm"] . "')\"
                style='background: none; border: none; cursor: pointer;'>
            <img class='trash' src='images/trash.png' alt='trash' height='40px' width='40px'>
        </button>
        </form>
        </td>";


                                echo "</tr>";
                            }
                        } else {
                            $_SESSION['info'] = $lang["noAccess"];
                            header('Location:index.php');
                        }
                    } else {
                        $_SESSION['info'] = $lang["noAccess"];
                        header('Location:index.php');
                    }
                }
                // Close the database connection
                            ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button onclick="location.href='host.php'" id='back' class='codeconfrim'>
                        </button>

                    </div>

                </div>

                <div id='popup'></div>
            </body>