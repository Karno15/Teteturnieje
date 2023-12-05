<?php

session_start();

if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}


if (!isset($_SESSION['username'])) {
    $_SESSION['info'] = 'Brak dostępu';
    header('Location:error.php');
}

require('connect.php');
$userId = isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null';
$turniejid = $_SESSION['TurniejId'];
$currentQuest = isset($_SESSION['currentQuest']) ? json_encode($_SESSION['currentQuest']) : 0;
$isLeader = (isset($_SESSION['leader']) && $turniejid == $_SESSION['leader']);

//echo $userId."+".$turniejid."+".$currentQuest.'+'.$isLeader;

function updateStatus($newStatus)
{
    require('connect.php');

    if (isset($_POST['start'])) {
        $sql = "UPDATE turnieje SET Status=? WHERE TurniejId =?";
        $turniejid = $_SESSION['TurniejId'];

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $newStatus, $turniejid);

        if ($stmt->execute() === TRUE) {
            $_SESSION['info'] = "Success!";
        } else {
            $_SESSION['info'] = "Błąd!";
            // echo "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function() {
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

        function checkTournamentStatus() {
            $.ajax({
                url: 'chkStatus.php',
                type: 'GET',
                dataType: 'json', // Wskazujemy, że oczekujemy danych JSON
                success: function(response) {
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
                            $('#popup').html("<div class='info'>" + 'Brak uczestników!' + "</div>");
                            $('.info').delay(3000).fadeOut();
                        });
                    } else {
                        $(document).on('click', '#start', function() {
                            $('#popup').hide();
                            updateStatusAjax('K', 0); //dont need current quest so we set it for 0
                        })
                    }



                    if (ptsresponse != JSON.stringify(response.participants)) {
                        var creator = response.creator
                        status = response.status
                        ptsresponse = JSON.stringify(response.participants)
                        // Wyświetl listę uczestników
                        var participantsList = '<table class="datatables">';



                        for (var i = 0; i < response.participants.length; i++) {
                            participantsList += '<tr><td>';

                            if (response.participants[i].Login == username) {
                                participantsList += '<b><font color="blue">' + response.participants[i].Login + "</font></b></td>"
                            } else {
                                participantsList += '<b>' + response.participants[i].Login + "</b></td></tr>"
                            }

                            let editable = (isLeader) ? 'true' : 'false';

                            participantsList += "<tr><td><div class='score-edit' contenteditable=" + editable + " data-login='" +
                                response.participants[i].Login + "'>" + response.participants[i].CurrentScore + '</div></td></tr>';
                        }
                        participantsList += '</table>';

                        $('#participantsInfo').html('<p>Organizator:<b><br> ' + response.creator +
                            '</b></p><p>Rzule: ' + participantsList + '</p>');
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
                                        categoriesHTML = "Wybieranie pytania<br><div id='categories-container'>";

                                        for (var i = 0; i < response.length; i++) {
                                            categoriesHTML += "<div class='category";
                                            (response[i].Done) ? categoriesHTML += "-none": categoriesHTML += "";
                                            categoriesHTML += "' data-pytid='" + response[i].PytId + "'>" + response[i].Category +
                                                "<br><br>Pkt:"
                                            categoriesHTML += response[i].IsBid ? "Do obstawienia" : response[i].Rewards;
                                            categoriesHTML += "</div>";
                                        }
                                        categoriesHTML += "</div>";
                                        $('.startpopup').html(categoriesHTML);

                                        if (isLeader) {

                                            $(document).on('click', '.category', function() {
                                                var pytId = $(this).data('pytid');
                                                currentQuest = pytId;
                                                updateStatusAjax('P', currentQuest);
                                            });

                                        }
                                        if (isLeader && status == 'X') {

                                            $("#statusInfo").html('<button id="finish" href=# class="button-85">Zakończ</button>');

                                            $(document).on('click', '#finish', function() {
                                                updateStatusAjax('Z', 0);
                                            });

                                        }
                                    }
                                },
                                error: function(error) {
                                    // Handle errors here
                                    console.error('Error:', error);
                                }
                            });
                        }
                    }
                    /// STATUS PYTANIA LUB ODPOWIEDZI --------------------------------------
                    if (status == 'P' || status == 'O') {
                        $.ajax({
                            url: 'chkBuzzes.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                turniejId: turniejId
                            },
                            success: function(response) {

                                if (buzzresponse != JSON.stringify(response)) {

                                    buzzresponse = JSON.stringify(response);
                                    var buzzesHTML = '<p id="aligned">Buzzers:<table class="datatables">';
                                    var firstBuzz = 0;


                                    for (var i = 0; i < response.buzzes.length; i++) {
                                        var buzz = response.buzzes[i];
                                        var buzztime = new Date(buzz.buzztime);

                                        buzzesHTML += '<tr><td><b>' + buzz.Login + ' </b></td></tr>';

                                        // Show the buzztime only for the second and subsequent logins
                                        if (i !== 0) {
                                            var duration = buzztime - firstBuzz;
                                            buzzesHTML += '<tr><td>' + formatDuration(duration) + '</td></tr>';
                                        } else {
                                            firstBuzz = new Date(buzz.buzztime);
                                            buzzesHTML += '<tr><td>First!</td>';
                                        }
                                        buzzesHTML += '</tr><tr>';

                                        if (isLeader) {
                                            buzzesHTML += '<td id="answerbuttons"><button class="okbutton" data-login="' + buzz.Login +
                                                '">✔️</button><button class="badbutton" data-login="' +
                                                buzz.Login + '">❌</button></td></tr>';
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
                                $('.info').text('Błąd pobierania buzza.');
                            }
                        });
                    }

                    /// STATUS PYTANIA --------------------------------------
                    if (!showQuest) {
                        if (status == 'P' || status == 'O') {
                            showQuest = true;
                            $('.startpopup').html('<span class="disclaimer">(spacja również działa jak przycisk BUZZ)</span><button id="buzzer">BUZZ</button>');
                            $('#startform').hide();

                            if (isLeader)
                                $("#turniej").html('<button id="status" href=# class="button-85">Pokaż odpowiedź</button>');

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
                                    // Tutaj możesz przetwarzać dane zwrócone z quest.php
                                    var PytId = quests.PytId;
                                    var Quest = quests.Quest;
                                    var Category = quests.Category;
                                    var TypeId = quests.TypeId;
                                    var Rewards = quests.Rewards;
                                    var pozycje = quests.pozycje;
                                    var isBid = quests.IsBid;

                                    pts = Rewards;
                                    if (PytId) {

                                        questHTML = '<p>Kategoria: ' + Category + '<br>Punkty: '
                                        questHTML += isBid ? 'Do obstawienia' : Rewards;
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
                                                $("#turniej").html('<button id="next" class="button-85">Nowe Pytanie</button>');

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
                                                            //przypisz id correct do pozycji która jest prawidłowa
                                                        }
                                                        $('#answer').html('<hr id="spliter">' + Answer);
                                                        $("#answer")[0].scrollIntoView();
                                                    } else {
                                                        $('#answer').html('Błąd pobierania odpowiedzi');
                                                    }
                                                },
                                                error: function() {
                                                    $('.info').text('Błąd podczas pobierania pytań.');
                                                    $('#participantsInfo').html('Error');
                                                }
                                            });
                                        }
                                    } else {
                                        $('.startpopup').append('Błąd pobierania pytania');
                                    }
                                },
                                error: function() {
                                    $('.info').text('Błąd podczas pobierania pytań.');
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

                            // Construct the URL with parameters
                            var redirectURL = redirectPage + "?turniejId=" + parameter1;

                            // Redirect to the constructed URL
                            window.location.href = redirectURL;
                        }
                    }

                },
                error: function() {
                    $('#statusInfo').text('Błąd podczas sprawdzania statusu turnieju.');
                    $('#participantsInfo').html('Error');
                }
            });

            prevstatus = status;
        }

        var pressed = false;

        function buzz() {
            if (!pressed) {
                buzzsfx.play();
                $('#buzzer').prop('disabled', true);
                pressed = true;
                $('#buzzer').css("background", "gray");
                $('#buzzer').css("border-color", "dimgray");
                $('#buzzer').css("box-shadow", "3px 7px 0px 0px #4d4d4d");

                var userId = <?php echo isset($_SESSION['userid']) ? $_SESSION['userid'] : 'null'; ?>;
                var turniejId = <?php echo isset($_SESSION['TurniejId']) ? $_SESSION['TurniejId'] : 'null'; ?>;

                // AJAX request to insert a record into the 'buzzes' table
                $.ajax({
                    url: 'buzz.php',
                    type: 'POST',
                    data: {
                        userId: userId,
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
                    // Aktualizuj listę uczestników po zaktualizowaniu wyniku
                    checkTournamentStatus();
                },
                error: function() {
                    $('.info').text('error.');
                }
            });
        });

        $(document).on('click', '.okbutton', function() {
            var login = $(this).data('login');
            answerPoints(login, pts, 1, turniejId);
            checkTournamentStatus();
        });

        $(document).on('click', '.badbutton', function() {
            var login = $(this).data('login');
            answerPoints(login, pts, 0, turniejId);
            checkTournamentStatus();
        });

        function updateStatusAjax(status, currentQuest) {
            $.ajax({
                type: 'POST',
                url: 'updateStatus.php', // Replace with the actual path to your PHP file
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
        // Uruchamiaj funkcję co 2 sekundy
        setInterval(checkTournamentStatus, 400);
    });
</script>


<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300&display=swap" rel="stylesheet">
    <script src="script.js"></script>
</head>

<body>

    <div id="main-container">
        <div id='head'>
            <span>TETETURNIEJE</span>
        </div>

        <div id='content'>
            <div class='startpopup'>
                <div class="loading-spinner"></div>
                Oczekiwanie na rozpoczęcie...<br><br>

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