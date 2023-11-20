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

$turniejid = $_SESSION['TurniejId'];

$isLeader = (isset($_SESSION['leader']) && $turniejid == $_SESSION['leader']);

?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        var buzzsfx = new Audio("sounds/buzz.wav");

        $('#participantsInfo').html('<div class="loading-spinner"></div>Loading...');
        var username = <?php echo isset($_SESSION['username']) ? json_encode($_SESSION['username']) : 'null'; ?>;
        shown = false;
        status = 0;
        currentQuest = 0;
        var pts = 0;

        function checkTournamentStatus() {

            $.ajax({
                url: 'chkStatus.php',
                type: 'GET',
                dataType: 'json', // Wskazujemy, że oczekujemy danych JSON
                success: function(response) {
                    console.log(status + ":" + response.status);

                    if (status != response.status) {
                        shown = false;
                    }

                    if (!shown) {
                        var creator = response.creator
                        status = response.status
                        currentQuest = response.currentQuest

                        // Wyświetl listę uczestników
                        var participantsList = '<table class="datatables">';


                        for (var i = 0; i < response.participants.length; i++) {
                            participantsList += '<tr><td>';

                            if (response.participants[i].Login == username) {
                                participantsList += '<b><font color="blue">' + response.participants[i].Login + "</font></b></td>"
                            } else {
                                participantsList += '<b>' + response.participants[i].Login + "</b></td>"
                            }

                            participantsList += "<td> Wynik: <span class='score-edit' contenteditable='true' data-login='" +
                                response.participants[i].Login + "'>" + response.participants[i].CurrentScore + '</span></td></tr>';
                        }
                        participantsList += '</table>';

                        $('#participantsInfo').html('Organizator:<b> ' + response.creator +
                            '</b><p>Rzule: ' + participantsList + '</p>');
                    }
                    if (status == 'P') {
                        $.ajax({
                            url: 'chkBuzzes.php',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                pytId: currentQuest
                            },
                            success: function(response) {

                                // Assuming response has a property named 'buzzes'
                                var buzzesHTML = '<p>Buzzes:<b><table class="datatables">';
                                var firstBuzz = 0;
                                var isLeader = <?php echo json_encode($isLeader); ?>;

                                for (var i = 0; i < response.buzzes.length; i++) {
                                    var buzz = response.buzzes[i];
                                    var buzztime = new Date(buzz.buzztime);

                                    buzzesHTML += '<tr><td><b>' + buzz.Login + ' </b></td>';

                                    // Show the buzztime only for the second and subsequent logins
                                    if (i !== 0) {
                                        var duration = buzztime - firstBuzz;
                                        buzzesHTML += '<td>' + formatDuration(duration) + '</td>';
                                    } else {
                                        firstBuzz = new Date(buzz.buzztime);
                                        buzzesHTML += '<td>First!</td>';
                                    }
                                    buzzesHTML += '</tr><tr>';

                                    if (isLeader) {
                                        buzzesHTML += '<td><button class="okbutton" data-login="' + buzz.Login +
                                            '">OK</button></td><td><button class="badbutton" data-login="' + buzz.Login + '">BAD</button></td>';
                                    }

                                    buzzesHTML += '</tr>';
                                }

                                // Remove the trailing comma
                                buzzesHTML = buzzesHTML.replace(/,\s*$/, '');

                                buzzesHTML += '</b>';

                                buzzesHTML += '</table></p>';
                                // Insert the HTML into the #buzzerInfo element
                                $('#buzzerInfo').html(buzzesHTML);
                            },
                            error: function() {
                                $('.info').text('Błąd pobierania buzza.');
                            }
                        });



                    }

                    if (!shown) {
                        shown = true;
                        if (status == 'P') {
                            $('.startpopup').html('<button id="buzzer">BUZZ</button>');
                            $('#startform').hide();


                            $.ajax({
                                url: 'quest.php',
                                type: 'GET',
                                dataType: 'json',
                                success: function(quests) {
                                    // Tutaj możesz przetwarzać dane zwrócone z quest.php
                                    var PytId = quests.PytId;
                                    var Quest = quests.Quest;
                                    var Category = quests.Category;
                                    var TypeId = quests.TypeId;
                                    var whoFirst = quests.whoFirst;
                                    var Rewards = quests.Rewards;
                                    var pozycje = quests.pozycje;

                                    pts = Rewards;

                                    $('.startpopup').append('<p>Kategoria: ' + Category + '<br>Punkty: ' + Rewards +
                                        '</p><span id="quest">' + Quest +
                                        "</span><div class='quest-options' id='questOptionsContainer'></div>");

                                    wyswietlPozycje(pozycje);


                                    $('#statusInfo').text(<?php echo ($isLeader)
                                                                ? '"test"'
                                                                : null; ?>);


                                },
                                error: function() {
                                    $('.info').text('Błąd podczas pobierania pytań.');
                                    $('#participantsInfo').html('Error');
                                }
                            });

                        }
                    }
                },
                error: function() {
                    $('#statusInfo').text('Błąd podczas sprawdzania statusu turnieju.');
                    $('#participantsInfo').html('Error');
                }
            });


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
                        pytId: currentQuest
                    },
                    success: function(response) {
                        console.log('Buzzed!');
                    },
                    error: function(error) {
                        console.error('Error buzzing:', error);
                    }
                });
            }
        }

        $(document).on('click', '#buzzer', function() {
            buzz();
        });

        window.onkeydown = function(event) {

            if (event.keyCode === 32) {
                event.preventDefault();
                buzz();
            }
        };


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
                    shown = false;
                    //  checkTournamentStatus();
                },
                error: function() {
                    $('.info').text('error.');
                }
            });
        });

        checkTournamentStatus();
        // Uruchamiaj funkcję co 2 sekundy
        setInterval(checkTournamentStatus, 2000);

        $(document).on('click', '.okbutton', function() {
            var login = $(this).data('login');
            answerPoints(login, pts, 1);
        });

        $(document).on('click', '.badbutton', function() {
            var login = $(this).data('login');
            answerPoints(login, pts, 0);
            shown = false;
        });
    });
</script>


<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="shortcut icon" type="image/gif" href="images/title.png">
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
                    echo '<form method="post" id="startform">';
                    echo '<button id="start" name="start" class="button-85" type="submit" margin-top="0px">START</button>';
                    echo '</form>';
                    if (isset($_POST['start'])) {
                        // Wykonaj zapytanie do bazy danych, aby zaktualizować kod turnieju
                        $sql = "UPDATE turnieje SET Status='P' WHERE TurniejId = $turniejid";

                        if ($conn->query($sql) === TRUE) {
                            $_SESSION['info'] = "Success!";
                        } else {
                            $_SESSION['info'] = "Błąd!";
                            //for debbuging echo . $conn->error;
                        }
                    }
                }


                ?>
            </span>


            <div id="statusInfo"></div>
            <div id="participantsInfo"></div>
            <div id="buzzerInfo"></div>



</body>