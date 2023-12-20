<?php
session_start();



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
        if ($creatorId != $userId) {
            $_SESSION['info'] = 'Nie znaleziono turnieju';
            header('Location:index.php');
        }
    }

    if (isset($_POST["submity"])) {
        require "connect.php";
        $category = $_POST["category"];
        $tresc = $_POST["tresc"];
        $type = $_POST["type"]; //1- zamknięte, 2- otwarte
        $after = $_POST["after"];

        if (isset($_POST["isbid"])) {
            $isbid = $_POST["isbid"];

            if ($isbid == 'on') {
                $isbid = 1;
                $rewards = 0;
            } elseif ($isbid == 'off')
                $isbid = 0;
        } else {
            $isbid = 0;
            $rewards = $_POST["rewards"];
        }

        $sql =
            "INSERT INTO `pytania`( `TurniejId`, `Quest`, `TypeId`, `Rewards`,`Category`,`IsBid`,`After`) 
            VALUES (" .
            $_GET["turniejid"] .
            ",'" .
            $tresc .
            "',$type, $rewards,'$category',$isbid,'$after');";

        echo $sql;
        //Perform a query, check for error
        if (!$conn->query($sql)) {
            $_SESSION['info'] = "Error description: " . $conn->error;
        } else {
            $pytanie_id = $conn->insert_id; // Get the inserted question ID
            if ($type == 1) {
                // For closed-type questions, insert answers into "pytaniapoz" table
                $options = array($_POST["option1"], $_POST["option2"], $_POST["option3"], $_POST["option4"]);
                $i = 1;
                foreach ($options as $op) {
                    $odpowiedz = base64_encode(trim($op)); // Usuń ewentualne białe znaki na początku i końcu odpowiedzi;base64

                    // Wstaw odpowiedź do tabeli "pytaniapoz"
                    $sql3 = "INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES ($pytanie_id, $i, '$odpowiedz')";
                    $i++;
                    if ($conn->query($sql3) === FALSE) {
                        $_SESSION['info'] = $conn->error;
                    }
                }

                // Check which radio button was selected for the correct answer
                $selectedAnswer = $_POST["answer"];
                $selectedAnswerId = substr($selectedAnswer, 1); // Remove the first character "a" from the answer value

                // Insert the selected answer and its value into the "prawiodpo" table
                $sql4 = "INSERT INTO prawiodpo (`PytId`, `PozId`) VALUES ($pytanie_id, $selectedAnswerId)";
                if ($conn->query($sql4) === FALSE) {
                    $_SESSION['info'] = $conn->error;
                }
            }

            $conn->close();
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
            exit();
        }
    }
?>

    <head>
        <title>TTT-TeTeTurnieje</title>
        <link rel="icon" type="image/gif" href="images/favicon.ico">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300&display=swap" rel="stylesheet">
        <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
        <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
        <link rel="stylesheet" href="style.css">
        <script src="script.js"></script>
    </head>

    <body>
        <div id="main-container">
            <div id='head'>
                <span>TETETURNIEJE</span>
            </div>

            <div id='content'>
                <?php
                echo "<button onclick=\"location.href='edit.php?turniejid=" . $_GET['turniejid'] . "'\" id='back'
             class='codeconfrim'>POWRÓT</button>";
                ?>


                <b> NOWE PYTANIE</b>

                <div class='startpopup'>


                    <form action="#" method='post' id='questionForm'>
                        Kategoria:
                        <input type='text' name='category' style='width:50%;
    height:40px;
    font-size: 20pt;
    '><br><br>
                        Treść:
                        <textarea class="summernote" name="tresc"></textarea>
                        <script>
                            $('.summernote').summernote({
                                placeholder: 'Umieść tutaj treść pytania',
                                tabsize: 2,
                                height: 200,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'underline', 'clear']],
                                    ['color', ['color']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'picture', 'video']],
                                    ['view', ['fullscreen', 'codeview', ]]
                                ]
                            });
                        </script>
                        <br>
                        <hr>
                        Typ pytania:
                        <select name='type' class='codeconfrim'>
                            <option value='1'>Zamknięte</option>
                            <option value='2'>Otwarte</option>
                        </select><br>
                        <span class='disclaimer'>Zaznacz prawdiłową odpowiedź klikając w checkbox*</span><br>
                        <div class='quest-options'>
                            <div class='quest-option'>Opcja 1: <br>
                                <input type='radio' name='answer' value='a1' required>
                                <input type='text' class='sinputy' name='option1' value='-'>
                            </div>
                            <div class='quest-option'>Opcja 2: <br>
                                <input type='radio' name='answer' value='a2' required>
                                <input type='text' class='sinputy' name='option2' value='-'>
                            </div><br>
                            <div class='quest-option'>Opcja 3: <br>
                                <input type='radio' name='answer' value='a3' required>
                                <input type='text' class='sinputy' name='option3' value='-'>
                            </div>
                            <div class='quest-option'>Opcja 4: <br>
                                <input type='radio' name='answer' value='a4' required>
                                <input type='text' class='sinputy' name='option4' value='-'>
                            </div>
                        </div><br><br>
                        Ilość punktów do zdobycia:
                        <input type='number' name='rewards' value='50' step=".01" class='codeconfrim'>
                        <span> Obstawianie punktów: <input type='checkbox' name='isbid'></span>

                        <br><br>
                        Treść do wyświetlenia odpowiedzi:
                        <textarea class="summernote" name="after"></textarea>
                        <script>
                            $('.summernote').summernote({
                                placeholder: 'Umieść tutaj treść odpowiedzi',
                                tabsize: 2,
                                height: 200,
                                toolbar: [
                                    ['style', ['style']],
                                    ['font', ['bold', 'underline', 'clear']],
                                    ['color', ['color']],
                                    ['para', ['ul', 'ol', 'paragraph']],
                                    ['table', ['table']],
                                    ['insert', ['link', 'picture', 'video']],
                                    ['view', ['fullscreen', 'codeview', ]]
                                ]
                            });
                        </script>
                        <br><br>
                        <input type='submit' name='submity' value='Dodaj' class='codeconfrim'>
                    </form>
                    <script>
                        $(document).ready(function() {

                            $('input[name="isbid"]').on('change', function() {
                                rewards = $('input[name="rewards"]');
                                if (rewards.attr('type') != 'text') {
                                    rewards.attr('disabled', 'disabled');
                                    rewards.attr('type', 'text');
                                    rewards.attr('value', '(do obstawienia)');
                                } else {
                                    rewards.removeAttr('disabled');
                                    rewards.attr('value', 50);
                                    rewards.attr('type', 'number');
                                }
                            });


                            //otwieranie zamykanie zależnie pd typu formularza
                            $('select[name="type"]').on('change', function() {
                                var opcja = $('select option:selected').text();
                                if (opcja == 'Otwarte') {
                                    $(".quest-options").hide();
                                    $(".disclaimer").hide();
                                    $("#questionForm input[type='radio']").removeAttr("required");

                                } else {
                                    $(".disclaimer").show();
                                    $(".quest-options").show()
                                    $("#questionForm input[type='radio']").prop("required", true);
                                };
                            });



                            // Funkcja do walidacji formularza
                            function validateForm(event) {
                                // Sprawdzamy, czy treść edytora "summernote" nie jest pusta
                                var content = $("#summernote").summernote('code').trim();
                                if (content === '') {
                                    // Jeśli treść jest pusta, zatrzymujemy wysłanie formularza
                                    event.preventDefault();
                                    alert("Treść pytania nie może być pusta.");
                                }
                            }

                            // Dodajemy event listener do formularza po kliknięciu przycisku "Wyślij"
                            $("#questionForm").submit(validateForm);

                            // Delegacja zdarzeń do przycisku "submit" dla dynamicznie tworzonego pola "summernote"
                            $(document).on('click', '#questionForm :submit', function(event) {
                                // Wywołujemy funkcję walidacji formularza
                                validateForm(event);
                            });
                        });
                    </script>


                </div>
            </div>

    </body>
<?php


}

?>