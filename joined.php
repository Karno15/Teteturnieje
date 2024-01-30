<?php

session_start();

include_once('translation/' . $_SESSION['lang'] . ".php");
if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}

if (!isset($_SESSION['username']) || !isset($_SESSION['TurniejId'])) {
    $_SESSION['info'] = $lang["noAccess"];
    header('Location:index.php');
}

require('connect.php');
$userId = isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null';
$turniejid = isset($_SESSION['TurniejId']) ? $_SESSION['TurniejId'] : 'null';
$currentQuest = isset($_SESSION['currentQuest']) ? json_encode($_SESSION['currentQuest']) : 0;
$isLeader = (isset($_SESSION['leader']) && $turniejid == $_SESSION['leader']);

function updateStatus($newStatus)
{
    require('connect.php');

    if (isset($_POST['start'])) {
        $sql = "UPDATE turnieje SET Status=? WHERE TurniejId =?";
        $turniejid = $_SESSION['TurniejId'];

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newStatus, $turniejid);

        if ($stmt->execute() === TRUE) {
            $_SESSION['info'] = $lang["success"];
        } else {
            $_SESSION['info'] = $lang["error"];
        }
        $stmt->close();
    }
}
?>
<script src="jquery/jquery.min.js"></script>
<script>
    var langses = <?php echo json_encode($_SESSION['lang']); ?>;
    var lang = langses || 'en';
    localStorage.setItem("lang", lang);
</script>
<script src="script.js"></script>
<script src="translation/translation.js"></script>
<script>
    $(document).ready(function() {
        $('#awaitingStart').html(translations['awaitingStart'][lang])

        var isLeader = <?php echo json_encode($isLeader); ?>;

        var buzzsfx = new Audio("sounds/buzz.wav");

        $('#participantsInfo').html('<div class="loading-spinner"></div>Loading...');
        var username = <?php echo $userId ?>;
        showQuest = false;
        shown = false;
        var prevstatus = 0;
        status = 0;
        var currentQuest = <?php echo $currentQuest ?>;
        var pts = 0;
        var ptsresponse = 0;
        var catresponse = 0;
        var buzzresponse = 0;
        var turniejId = <?php echo $turniejid ?>;
        var ee_shown = false;

        function checkTournamentStatus() {
            $.ajax({
                url: 'chkStatus.php',
                type: 'GET',
                dataType: 'json',
                success: function(response) {

                    if (getCookie('EE_Larvolcarona') && ee_shown == false) {
                        $('body').append('<img class="flier1" src="images/larvesta.png"><img class="flier2" src="images/volcarona.png">')
                        ee_shown = true;
                    }

                    if (response.error) {
                        $('#popup').html("<div class='info'>" + response.error + "</div>");
                        window.location.href = 'error.php?info=' + response.error;
                    }

                    if (status != response.status) {
                        shown = false;
                        showQuest = false;
                        status = response.status;
                    }

                    if (response.participants.length == 0) {
                        $(document).on('click', '#start', function() {
                            $('#popup').show();
                            $('#popup').html("<div class='info'>" + translations['noParticipants'][lang] + "</div>");
                            $('.info').delay(3000).fadeOut();
                        });
                    } else {
                        $(document).on('click', '#start', function() {
                            $('#popup').hide();
                            updateStatusAjax('K', 0);
                        })
                    }

                    if (ptsresponse != JSON.stringify(response.participants)) {
                        var creator = response.creator
                        status = response.status
                        ptsresponse = JSON.stringify(response.participants)
                        var participantsList = '<table class="datatables">';

                        for (var i = 0; i < response.participants.length; i++) {
                            participantsList += '<tr><td>';

                            if (response.participants[i].Login == username) {
                                participantsList += '<b><font color="blue">' + response.participants[i].Login + "</font></b></td>"
                            } else {
                                participantsList += '<b>' + response.participants[i].Login + "</b></td></tr>"
                            }

                            let editable = (isLeader) ? 'true' : 'false';

                            participantsList += "<tr><td><div class='score-edit' contenteditable='" + editable + "' data-login=\"" +
                                escape(response.participants[i].Login) + "\">" + response.participants[i].CurrentScore + '</div></td></tr>';

                        }
                        participantsList += '</table>';

                        $('#participantsInfo').html('<p>Host:<b><br> ' + response.creator +
                            '</b></p><p id="participantsPts">' + translations['participants'][lang] + ': ' + participantsList + '</p>');
                    }

                    /// STATUS KATEGORII I KOŃCA KATEGORI (X) --------------------------------------
                    if (status == 'K' || status == 'X') {
                        if (prevstatus != status && prevstatus != 0) {
                            location.reload() //for too much pressure
                        }
                        if (!shown) {
                            shown = true;
                            $("#turniej").html("");
                            $.ajax({
                                url: 'getCategory.php',
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    if (catresponse != JSON.stringify(response)) {

                                        catresponse = JSON.stringify(response);
                                        categoriesHTML = translations['selectQuest'][lang] + '<br><div class="gridpopup"><div id="grid-container" class="grid-container">';

                                        for (var i = 0; i < response.length; i++) {
                                            categoriesHTML += "<div class='category";
                                            (response[i].Done) ? categoriesHTML += "-none": categoriesHTML += "";
                                            categoriesHTML += "' data-pytid='" + response[i].PytId + "'>" + response[i].Category +
                                                "<br><br>" + translations['pts'][lang] + ": "
                                            categoriesHTML += response[i].IsBid ? translations['betting'][lang] : response[i].Rewards;
                                            categoriesHTML += "</div>";
                                        }
                                        categoriesHTML += "</div></div>";
                                        $('.startpopup').html(categoriesHTML);
                                        $('#grid-container').css('grid-template-columns', 'repeat(' + response[0].Columns + ', 1fr)');
                                        if (isLeader) {

                                            $(document).on('click', '.category', function() {
                                                var pytId = $(this).data('pytid');
                                                currentQuest = pytId;
                                                updateStatusAjax('P', currentQuest);
                                            });

                                        }
                                        if (isLeader && status == 'X') {

                                            $("#statusInfo").html('<button id="finish" href=# class="button-85">' + translations['finish'][lang] + '</button>');

                                            $(document).on('click', '#finish', function() {
                                                updateStatusAjax('Z', 0);
                                            });

                                        }
                                    }
                                },
                                error: function(error) {
                                    console.error('Error:', error);
                                }
                            });
                        }
                    }
                    /// STATUS PYTANIA LUB ODPOWIEDZI --------------------------------------
                    if (status == 'P' || status == 'O') {
                        $.ajax({
                            url: 'chkBuzzes.php',
                            type: 'GET',
                            dataType: 'json',
                            success: function(response) {

                                if (buzzresponse != JSON.stringify(response)) {

                                    buzzresponse = JSON.stringify(response);
                                    if (buzzresponse != '{"buzzes":[]}') {
                                        buzzsfx.play();

                                    }

                                    var buzzesHTML = '<p id="aligned">Buzzers:<table class="datatables">';
                                    var firstBuzz = 0;

                                    for (var i = 0; i < response.buzzes.length; i++) {
                                        var buzz = response.buzzes[i];
                                        var buzztime = new Date(buzz.buzztime);

                                        buzzesHTML += '<tr><td><b>' + buzz.Login + ' </b></td></tr>';
                                        if (i !== 0) {
                                            var duration = buzztime - firstBuzz;
                                            buzzesHTML += '<tr><td>' + formatDuration(duration) + '</td></tr>';
                                        } else {
                                            firstBuzz = new Date(buzz.buzztime);
                                            buzzesHTML += '<tr><td>' + translations['first'][lang] + '!</td>';
                                        }
                                        buzzesHTML += '</tr><tr>';

                                        if (isLeader) {
                                            buzzesHTML += '<td id="answerbuttons"><button class="okbutton" data-login=\"' + escape(buzz.Login) +
                                                '\">✔️</button><button class="badbutton" data-login=\"' +
                                                escape(buzz.Login) + '\">❌</button></td></tr>';
                                        }

                                        buzzesHTML += '</tr>';
                                    }

                                    // Remove the trailing comma
                                    buzzesHTML = buzzesHTML.replace(/,\s*$/, '');

                                    buzzesHTML += '</b>';

                                    buzzesHTML += '</table></p>';
                                    // Insert the HTML into the #buzzerInfo element
                                    $('#buzzerInfo').html(buzzesHTML);
                                    $('#buzzerInfo').show(); //set block
                                }

                            },
                            error: function() {
                                $('#buzzerInfo').text(translations['errorBuzz'][lang]);
                            }
                        });
                    }

                    /// STATUS PYTANIA --------------------------------------
                    if (!showQuest) {
                        if (status == 'P' || status == 'O') {
                            showQuest = true;
                            $('.startpopup').html('<span class="disclaimer" id="tipSpace">' + translations['tipSpace'][lang] + '</span><button id="buzzer">BUZZ</button>');
                            $('#startform').hide();

                            if (isLeader)
                                $("#turniej").html('<button id="status" href=# class="button-85">' + translations['showAnswer'][lang] + '</button>');

                            $(document).on('click', '#status', function() {
                                updateStatusAjax('O', currentQuest);
                            });

                            $.ajax({
                                url: 'quest.php',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    turniejId: turniejId
                                },
                                success: function(quests) {
                                    var PytId = quests.PytId;
                                    var Quest = quests.Quest;
                                    var Category = quests.Category;
                                    var TypeId = quests.TypeId;
                                    var Rewards = quests.Rewards;
                                    var pozycje = quests.pozycje;
                                    var isBid = quests.IsBid;

                                    pts = Rewards;
                                    if (PytId) {

                                        questHTML = '<p>' + translations['questCat'][lang] + ': ' + Category + '<br>' + translations['questPts'][lang] + ': '
                                        questHTML += isBid ? translations['betting'][lang] : Rewards;
                                        questHTML += '</p><span id="quest">' + Quest + "</span>";



                                        if (typeof pozycje !== 'undefined')
                                            questHTML += "<div class='quest-options' id='questOptionsContainer'></div>";


                                        $('.startpopup').append(questHTML);

                                        if (typeof pozycje !== 'undefined') {
                                            pozycjeHTML = "";
                                            pozycje.forEach(function(pozycja) {
                                                pozycjeHTML += '<div class="quest-option">' + pozycja.Value + '</div>';
                                            });
                                            $('#questOptionsContainer').append(pozycjeHTML);
                                        }
                                        $('.startpopup').append("<div id='answer'></div>");

                                        /// STATUS ODPOWIEDZI --------------------------------------
                                        if (status == 'O') {
                                            shown = true;
                                            if (isLeader)
                                                $("#turniej").html('<button id="next" class="button-85">' + translations['newQuest'][lang] + '</button>');

                                            $(document).on('click', '#next', function() {
                                                updateStatusAjax('K', 0);
                                                checkTournamentStatus();
                                            });

                                            $.ajax({
                                                url: 'getAnswer.php',
                                                type: 'POST',
                                                dataType: 'json',
                                                data: {
                                                    turniejId: turniejId
                                                },
                                                success: function(answer) {
                                                    var PytId = answer.PytId;
                                                    var Answer = answer.Answer;
                                                    var PozId = answer.PozId;
                                                    if (PytId) {
                                                        if (PozId) {
                                                            $(".quest-option").eq(PozId - 1).attr('id', 'correct');
                                                        }
                                                        $('#answer').html('<hr id="spliter">' + Answer);
                                                        $("#answer")[0].scrollIntoView();

                                                    } else {
                                                        $('#answer').html(translations['errorAnswer'][lang]);
                                                    }
                                                },
                                                error: function() {
                                                    $('.info').text(translations['errorQuest'][lang]);
                                                    $('#participantsInfo').html('Error');
                                                }
                                            });
                                        }
                                    } else {
                                        $('.startpopup').append(translations['errorQuest'][lang]);
                                    }
                                },
                                error: function() {
                                    $('.info').text(translations['errorQuest'][lang]);
                                    $('#participantsInfo').html('Error');
                                }
                            });

                            $(document).on('click', '#buzzer', function() {
                                buzz();
                            });

                            window.onkeydown = function(event) {

                                if (event.keyCode === 32) {
                                    event.preventDefault();
                                    buzz();
                                }
                            };
                        }

                    }
                    /// STATUS ZAKOŃCZENIA --------------------------------------
                    if (!shown) {
                        if (status == 'Z') {
                            shown = true;
                            var redirectPage = "finish.php";
                            var parameter1 = turniejId;
                            var redirectURL = redirectPage + "?turniejid=" + parameter1;
                            window.location.href = redirectURL;
                        }
                    }

                },
                error: function() {
                    $('#statusInfo').text(translations['errorStatus'][lang]);
                    $('#participantsInfo').html('Error');
                }
            });

            prevstatus = status;
        }

        var pressed = false;

        function buzz() {
            if (!pressed) {
                $('#buzzer').prop('disabled', true);
                pressed = true;
                $('#buzzer').css("background", "gray");
                $('#buzzer').css("border-color", "dimgray");
                $('#buzzer').css("box-shadow", "3px 7px 0px 0px #4d4d4d");

                var username = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;
                var turniejId = <?php echo isset($_SESSION['TurniejId']) ? $_SESSION['TurniejId'] : 'null'; ?>;
                $.ajax({
                    url: 'buzz.php',
                    type: 'POST',
                    data: {
                        username: username,
                        turniejId: turniejId,
                    },
                    success: function(response) {
                        checkTournamentStatus();
                    },
                    error: function(error) {
                        console.error('Error buzzing:', error);
                    }
                });
            }
        }




        $('body').on('blur', '.score-edit', function() {
            var login = $(this).data('login');
            var newScore = $(this).text();

            $.ajax({
                url: 'updateScore.php',
                type: 'POST',
                data: {
                    login: login,
                    newScore: newScore
                },
                success: function(response) {
                    checkTournamentStatus();
                },
                error: function() {
                    $('.info').text('error.');
                }
            });
        });

        $(document).on('click', '.okbutton', function() {
            var login = $(this).data('login');
            if (pts != 0) {
                answerPoints(login, pts, 1, turniejId);
                checkTournamentStatus();
            }
        });

        $(document).on('click', '.badbutton', function() {
            var login = $(this).data('login');
            if (pts != 0) {
                answerPoints(login, pts, 0, turniejId);
                checkTournamentStatus();
            }
        });

        function updateStatusAjax(status, currentQuest) {
            $.ajax({
                type: 'POST',
                url: 'updateStatus.php',
                data: {
                    turniejId: turniejId,
                    status: status,
                    currentQuest: currentQuest
                },
                success: function(response) {
                    checkTournamentStatus();
                },
                error: function(error) {
                    console.error(error);
                }
            });
        }

        checkTournamentStatus();
        setInterval(checkTournamentStatus, 400);
    });
</script>

<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/favicon.ico">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div id="main-container">
        <div id='head'>
            <span>TETETURNIEJE</span>
        </div>
        <div id='content'>
            <div class='startpopup'>
                <?php
                if (isset($_SESSION['TurniejId'])) {

                    $codeSql = "SELECT Code FROM turnieje WHERE TurniejId = ?";
                    $codeStmt = $conn->prepare($codeSql);
                    $codeStmt->bind_param("i", $_SESSION['TurniejId']);
                    $codeStmt->execute();
                    $codeResult = $codeStmt->get_result();
                    $code = $codeResult->fetch_assoc();
                    echo  $lang["code"] . ': ' . $code['Code'];
                }
                ?>
                <div class="loading-spinner"></div>
                <span id='awaitingStart'></span><br><br>

            </div>
            <span id='turniej'>
                <?php
                if ($isLeader) {
                    echo '<button id="start" name="start" class="button-85" type="submit" padding-top="20px">START</button>';
                }
                ?>
            </span>
            <div id="statusInfo"></div>
            <div id="participantsInfo"></div>
            <div id="buzzerInfo"></div>
            <div id="popup"></div>
</body>
<?php
mysqli_close($conn);
?>