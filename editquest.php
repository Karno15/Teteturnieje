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
    $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
    $stmt->bind_param("i", $turniejId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creatorId = $row['Creator'];

        // Check if the TurniejId's creator matches the user's ID - if yes do the rest
        if ($creatorId != $userId) {
            $_SESSION['info'] = 'Nie znaleziono turnieju';
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
        }
    } else {
        $_SESSION['info'] = 'Nie znaleziono turnieju';
        header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
    }

    if (isset($_GET["pytid"])) {
        $pytid = $_GET["pytid"];
        // Query to check if the PytId exists
        $stmt = $conn->prepare("SELECT PytId, Quest, TypeId, Category, IsBid, Rewards, After FROM pytania WHERE TurniejId = ? AND PytId = ?");
        $stmt->bind_param("ii", $turniejId, $pytid);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        if ($result->num_rows == 0) {
            $_SESSION['info'] = 'Nie znaleziono pytania';
            echo $_SESSION['info'];
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
        }
        $stmt->close();

        if ($row['TypeId'] == 1) {
            // Query to get positions
            $stmtpoz = $conn->prepare("SELECT PozId,Value FROM `pytaniapoz`where PytId= ? ;");
            $stmtpoz->bind_param("i", $pytid);
            $stmtpoz->execute();
            $resultpoz = $stmtpoz->get_result();
            while ($rowpoz = mysqli_fetch_assoc($resultpoz)) {
                //to do - make an array for positions and dont forget to decode md5
            }

            $stmtans = $conn->prepare("SELECT PozId FROM `prawiodpo` where PytId= ? ;");
            $stmtans->bind_param("i", $pytid);
            $stmtans->execute();
            $resultans = $stmtans->get_result();

            $rowans = $resultans->fetch_assoc();

            $stmtans->close();
            $stmtpoz->close();
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

        // Prepare the INSERT statement
        $stmt = $conn->prepare("INSERT INTO `pytania`(`TurniejId`, `Quest`, `TypeId`, `Rewards`, `Category`, `IsBid`, `After`) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isidsis", $turniejId, $tresc, $type, $rewards, $category, $isbid, $after);

        $stmt->execute();
        //Perform a query, check for error
        if ($stmt->error) {
            $_SESSION['info'] = "Error description: " . $stmt->error;
        } else {
            $pytanie_id = $stmt->insert_id; // Get the inserted question ID
            if ($type == 1) {
                // Prepare the INSERT statement for pytaniapoz table
                $stmt1 = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                // Prepare the INSERT statement for prawiodpo table
                $stmt2 = $conn->prepare("INSERT INTO prawiodpo (`PytId`, `PozId`) VALUES (?, ?)");

                // Loop through the options
                $options = array($_POST["option1"], $_POST["option2"], $_POST["option3"], $_POST["option4"]);
                $i = 1;
                foreach ($options as $op) {
                    $odpowiedz = base64_encode(trim($op));

                    // Bind parameters and execute the pytaniapoz INSERT statement
                    $stmt1->bind_param("iss", $pytanie_id, $i, $odpowiedz);
                    $stmt1->execute();

                    // Check for errors in the pytaniapoz INSERT statement
                    if ($stmt1->error) {
                        $_SESSION['info'] = $stmt1->error;
                        break; // Exit the loop if an error occurs
                    }

                    // Check if this option is the correct answer
                    $selectedAnswer = $_POST["answer"];
                    $selectedAnswerId = substr($selectedAnswer, 1);

                    // Bind parameters and execute the prawiodpo INSERT statement for the correct answer
                    if ($i == $selectedAnswerId) {
                        $stmt2->bind_param("ii", $pytanie_id, $i);
                        $stmt2->execute();

                        // Check for errors in the prawiodpo INSERT statement
                        if ($stmt2->error) {
                            $_SESSION['info'] = $stmt2->error;
                            break; // Exit the loop if an error occurs
                        }
                    }

                    $i++;
                }

                // Close prepared statements
                $stmt1->close();
                $stmt2->close();
            }



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


                <b>
                    <?php
                    echo isset($pytid) ? 'EDYCJA PYTANIA' : 'NOWE PYTANIE';
                    ?>
                </b>


                <div class='startpopup'>


                    <form action="#" method='post' id='questionForm'>
                        Kategoria:
                        <input type='text' name='category' style='width:50%;
    height:40px;
    font-size: 20pt;
    ' value='<?php echo isset($pytid) ? $row['Category'] : ''; ?>'><br><br>
                        Treść:
                        <textarea class="summernote" name="tresc"></textarea>
                        <script>
                            $('.summernote').summernote({
                                placeholder: ' ',
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
                        <input type='number' name='rewards' step=".01" class='codeconfrim' value='50'>
                        <span> Obstawianie punktów: <input type='checkbox' name='isbid'></span>

                        <br><br>
                        Treść do wyświetlenia odpowiedzi:
                        <textarea class="summernote" name="after"></textarea>
                        <script>
                            $('.summernote').summernote({
                                placeholder: ' ',
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
                        <input type='submit' name='submity' value='Zapisz' class='codeconfrim'>
                    </form>
                    <script>
                        $(document).ready(function() {
                            var pytid = getUrlParameter('pytid');

                            if (pytid) {
                                var tresc = <?php echo json_encode($row['Quest'] ?? ''); ?>;

                                // If 'pytid' exists, set content based on the parameter value
                                $('.note-editable').html(tresc);

                                var pts = <?php echo json_encode($row['Rewards'] ?? ''); ?>;

                                var answer = <?php echo json_encode($row['After'] ?? ''); ?>;

                                // If 'pytid' exists, set content based on the parameter value
                                $('.note-editable').eq(1).html(answer);

                                var isBid = <?php echo json_encode($row['IsBid'] ?? ''); ?>;
                                if (isBid) {
                                    $('input[name="isbid"]').attr('checked', 'checked');
                                    $('input[name="rewards"]').prop('type', 'text');
                                    $('input[name="rewards"]').val('(do obstawienia)');
                                } else {
                                    $('input[name="rewards"]').val(pts);
                                }

                                var typeid = <?php echo json_encode($row['TypeId'] ?? ''); ?>;
                                if (typeid == 2) {
                                    $(".quest-options").hide();
                                    $(".disclaimer").hide();
                                    $("#questionForm input[type='radio']").removeAttr("required");
                                    $("select[name='type'] option[value=2]").prop("selected", "selected")
                                }

                            } else {
                                $('.note-placeholder').html('Umieść tutaj treść pytania');
                                // If 'pytid' doesn't exist, set a placeholder content
                                $('.note-placeholder').eq(1).html('Umieść tutaj treść odpowiedzi');
                            }

                            $('input[name="isbid"]').on('change', function() {
                                var rewards = $('input[name="rewards"]');

                                if (rewards.prop('type') !== 'text') {
                                    // Switch to text input
                                    rewards.prop('disabled', true);
                                    rewards.prop('type', 'text');
                                    rewards.val('(do obstawienia)');
                                } else {
                                    // Switch to number input
                                    rewards.prop('disabled', false);
                                    rewards.prop('type', 'number');


                                    if (!pts) {
                                        pts = 50;
                                    }
                                    // Set a default numeric value if 'pts' is not numeric
                                    var numericPts = parseFloat(rewards.val());
                                    if (!isNaN(numericPts)) {
                                        rewards.val(numericPts);
                                    } else {
                                        rewards.val(pts); // Default value if 'pts' is not a valid number
                                    }
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
                                var content = $(".note-editable").summernote('code').trim();
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