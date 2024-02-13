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

include_once('translation/' . $_SESSION['lang'] . ".php");

if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = $lang['notFound'];
    header('Location:host.php');
} elseif (!isset($_SESSION['userid'])) {
    $_SESSION['info'] = $lang['noAccess'];
    header('Location:index.php');
} else {

    require('connect.php');

    $userId = $_SESSION['userid'];
    $turniejId = mysqli_real_escape_string($conn, $_GET['turniejid']);

    $sql = "SELECT Creator FROM turnieje WHERE TurniejId = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $turniejId);
        $stmt->execute();
        $stmt->bind_result($creatorId);
        $stmt->fetch();
        $stmt->close();

        if ($creatorId == $userId) {
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
                        $('#emptyQuestion').html(translations['emptyQuestion'][lang]);
                    });
                </script>
                <div class="popup-overlay"></div>
                <div id="main-container">
                    <div id='head'>
                        <span>TETETURNIEJE</span>
                    </div>
                    <div id='content' class='fonty'>
                        <div class='startpopup' style='flex-direction: row; justify-content: space-around'>
                            <a href='editquest.php?turniejid=<?= $turniejId ?>' class="button-85" id='addQuest'></a>
                            <a href='editgrid.php?turniejid=<?= $turniejId ?>' class="button-85" id='editGrid'></a>
                        </div><br>

                        <div class='startpopup'>
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

                        $query = "SELECT * FROM pytania WHERE TurniejId = ? order by PytId desc";
                        $stmtQuestions = mysqli_prepare($conn, $query);
                        mysqli_stmt_bind_param($stmtQuestions, 'i', $turniejId);
                        mysqli_stmt_execute($stmtQuestions);
                        $resultQuestions = mysqli_stmt_get_result($stmtQuestions);

                        if (!$resultQuestions) {
                            die("Query failed");
                        }

                        if (mysqli_num_rows($resultQuestions) == 0) {
                            echo "<tr><td id='emptyQuestion' colspan='7'></td></tr>";
                        } else {
                            while ($row = mysqli_fetch_assoc($resultQuestions)) {
                                echo "<tr>";
                                echo "<td>" . $row['PytId'] . "</td>";
                                echo "<td>";
                                echo ($row['TypeId'] == 1) ? $lang["closed"] : $lang["open"];
                                echo "</td>";
                                echo "<td>" . $row['Category'] . "</td><td>";
                                echo ($row['IsBid'] == 1) ? $lang["betting"] : $row['Rewards'];
                                echo "</td><td><img class='view' src='images/unowneyeclose.png' onclick='pokazPytanie(" . $row['PytId'] . ")' alt='unownclose' height='40px' width='40px'></button></td>";
                                echo "<td> <a href='editquest.php?turniejid=" . $row['TurniejId'] . "&pytid=" . $row['PytId'] . "'><img class='wrench' src='images/edit.svg' alt='edit' height='40px' width='40px'></a></td>";
                                echo "<td><form method='post' action='deleteQuest.php'>
                <input type='hidden' name='question_id' value='" . $row['PytId'] . "'>
                <input type='hidden' name='turniejid' value='" . $turniejId . "'>
                <button type='submit' name='delete_question' onclick=\"return confirm('" . $lang["deleteConfirm"] . "')\"
                style='background: none; border: none; cursor: pointer;'>
                <img class='trash' src='images/trash.png' alt='trash' height='40px' width='40px'></button></form></td></tr>";
                            }
                        }
                    } else {
                        $_SESSION['info'] = $lang['noAccess'];
                        header('Location: index.php');
                        exit();
                    }
                } else {
                    $_SESSION['info'] = "Error";
                    header('Location: index.php');
                    exit();
                }
            }
            mysqli_close($conn);
                        ?>
                                </tbody>
                            </table>
                        </div>
                        <button onclick="location.href='host.php'" id='back' class='codeconfrim'>
                        </button>
                    </div>
                </div>
                <div id='popup'></div>
            </body>