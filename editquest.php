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


        $positions = array(); // Initialize an array to store positions

        // Query to get positions
        $stmtpoz = $conn->prepare("SELECT PozId, Value FROM `pytaniapoz` WHERE PytId = ? order by PozId;");
        $stmtpoz->bind_param("i", $pytid);
        $stmtpoz->execute();
        $resultpoz = $stmtpoz->get_result();

        $stmtans = $conn->prepare("SELECT PozId FROM `prawiodpo` where PytId= ? ;");
        $stmtans->bind_param("i", $pytid);
        $stmtans->execute();
        $resultans = $stmtans->get_result();

        while ($rowpoz = mysqli_fetch_assoc($resultpoz)) {
            $position = array(
                'PozId' => $rowpoz['PozId'],
                'Value' => base64_decode($rowpoz['Value']) // Assuming you want to base64 hash the 'Value'
            );
            $positions[] = $position; // Add the position to the array
        }

        $numPositions = count($positions) ? count($positions) : null;
        $rowans = $resultans->fetch_assoc();
        $correct = $rowans['PozId'] ?? null;

        $stmtans->close();
        $stmtpoz->close();
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

        if (!isset($pytid)) {
            // Insert operation
            $stmt = $conn->prepare("INSERT INTO `pytania`(`TurniejId`, `Quest`, `TypeId`, `Rewards`, `Category`, `IsBid`, `After`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isidsis", $turniejId, $tresc, $type, $rewards, $category, $isbid, $after);
            $stmt->execute();

            // Check for errors
            if ($stmt->error) {
                $_SESSION['info'] = "Error description: " . $stmt->error;
            } else {
                $pytanie_id = $stmt->insert_id; // Get the inserted question ID

                if ($type == 1) {
                    // Insert operation for pytaniapoz table
                    $stmt1 = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                    // Insert operation for prawiodpo table
                    $stmt2 = $conn->prepare("INSERT INTO prawiodpo (`PytId`, `PozId`) VALUES (?, ?)");

                    $options = array();

                    // Loop through the submitted form data
                    for ($i = 1; isset($_POST["option{$i}"]); $i++) {
                        // Add each option to the array
                        $options[] = $_POST["option{$i}"];
                    }

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
        } else {
            // Update operation
            $stmtUpdate = $conn->prepare("UPDATE `pytania` SET `Quest` = ?, `TypeId` = ?, `Rewards` = ?, `Category` = ?, `IsBid` = ?, `After` = ? WHERE `PytId` = ?");
            $stmtUpdate->bind_param("sidsisi", $tresc, $type, $rewards, $category, $isbid, $after, $pytid);
            $stmtUpdate->execute();

            if ($stmtUpdate->error) {
                $_SESSION['info'] = "Error updating pytania table: " . $stmtUpdate->error;
            } else {
                // Check and update pytaniapoz table only for closed-type questions
                if ($type == 1) {

                    if ($positions > 0 && $correct) {
                        // The array is not empty.
                        // Delete existing options
                        $stmtDeleteOptions = $conn->prepare("DELETE FROM pytaniapoz WHERE PytId = ?");
                        $stmtDeleteOptions->bind_param("i", $pytid);
                        $stmtDeleteOptions->execute();

                        // Check for errors in the delete statement
                        if ($stmtDeleteOptions->error) {
                            $_SESSION['info'] = "Error deleting pytaniapoz records: " . $stmtDeleteOptions->error;
                        } else {
                            // Insert new options
                            $stmtInsertOptions = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                            $options = array();

                            // Loop through the submitted form data
                            for ($i = 1; isset($_POST["option{$i}"]); $i++) {
                                // Add each option to the array
                                $options[] = $_POST["option{$i}"];
                            }

                            $i = 1;
                            foreach ($options as $op) {
                                $odpowiedz = base64_encode(trim($op));
                                // Bind parameters and execute the pytaniapoz INSERT statement
                                $stmtInsertOptions->bind_param("iss", $pytid, $i, $odpowiedz);
                                $stmtInsertOptions->execute();

                                // Check for errors in the pytaniapoz INSERT statement
                                if ($stmtInsertOptions->error) {
                                    $_SESSION['info'] = "Error inserting pytaniapoz records: " . $stmtInsertOptions->error;
                                    break; // Exit the loop if an error occurs
                                }

                                $i++;
                            }

                            // Close prepared statements
                            $stmtInsertOptions->close();
                        }

                        // Close the delete statement for pytaniapoz
                        $stmtDeleteOptions->close();
                    } else {
                        // Insert operation for pytaniapoz table
                        $stmt1 = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                        // Insert operation for prawiodpo table
                        $stmt2 = $conn->prepare("INSERT INTO prawiodpo (`PytId`, `PozId`) VALUES (?, ?)");

                        $options = array();

                        // Loop through the submitted form data
                        for ($i = 1; isset($_POST["option{$i}"]); $i++) {
                            // Add each option to the array
                            $options[] = $_POST["option{$i}"];
                        }

                        $i = 1;
                        foreach ($options as $op) {
                            $odpowiedz = base64_encode(trim($op));
                            // Bind parameters and execute the pytaniapoz INSERT statement
                            $stmt1->bind_param("iss", $pytid, $i, $odpowiedz);
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
                                $stmt2->bind_param("ii", $pytid, $i);
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
                }

                // Update existing records in prawiodpo for the current question
                $stmtUpdateOdp = $conn->prepare("UPDATE prawiodpo SET `PozId` = ? WHERE `PytId` = ?");
                $selectedAnswer = $_POST["answer"];
                $selectedAnswerId = substr($selectedAnswer, 1);

                // Bind parameters and execute the prawiodpo UPDATE statement for the correct answer
                $stmtUpdateOdp->bind_param("ii", $selectedAnswerId, $pytid);
                $stmtUpdateOdp->execute();

                // Check for errors in the prawiodpo UPDATE statement
                if ($stmtUpdateOdp->error) {
                    $_SESSION['info'] = "Error updating prawiodpo records: " . $stmtUpdateOdp->error;
                }

                // Close the prepared statement for prawiodpo
                $stmtUpdateOdp->close();
            }

            // Close the prepared statement for pytania
            $stmtUpdate->close();

            // Redirect to the edit page
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
                        <span class='disclaimer'>Dodaj lub usuń liczbę opcji</span><br>

                        <button type="button" id="addOptionBtn" class="codeconfrim">+</button>
                        <button type="button" id="removeOptionBtn" class="codeconfrim">-</button>

                        <!-- Container to hold the quest options -->
                        <div class='quest-options' id='questOptionsContainer'>
                            <!-- Initial quest options -->
                            <?php
                            // Set the desired number of options (customize as needed)
                            $numOptions = $numPositions ?? 4;

                            // Loop to generate HTML for each option
                            for ($i = 1; $i <= $numOptions; $i++) {
                                echo "
        <div class='quest-option' id='option{$i}'>
            Opcja {$i}:
            <input type='radio' name='answer' value='a{$i}' required>
            <input type='text' class='sinputy' name='option{$i}' value='-'>
        </div>
    ";
                            }
                            ?>

                        </div>

                        <br>
                        <span class='disclaimer'>Zaznacz prawdiłową odpowiedź klikając w checkbox</span><br>
                        <br><br>
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
                    <?php
                    echo '<script>';
                    echo 'var positions = ' . json_encode($positions ?? '') . ';';
                    echo '</script>';
                    ?>
                    <script>
                        $(document).ready(function() {
                            var optionCounter = <?php echo json_encode($numPositions ?? 4); ?>; // Start with 4 initial options

                            // Function to add a new quest option
                            $("#addOptionBtn").click(function() {
                                if (optionCounter < 30) {
                                    optionCounter++;

                                    // Set the HTML content for the new option
                                    var newOptionHTML = `
Opcja ${optionCounter}:
<input type='radio' name='answer' value='a${optionCounter}' required>
<input type='text' class='sinputy' name='option${optionCounter}' value='-'>
`;

                                    // Append the new option to the container
                                    $("#questOptionsContainer").append(`<div class='quest-option' id='option${optionCounter}'>${newOptionHTML}</div>`);
                                }
                            });

                            // Function to remove the last quest option
                            $("#removeOptionBtn").click(function() {
                                if (optionCounter > 2) {
                                    // Remove the last option
                                    $(`#option${optionCounter}`).remove();
                                    optionCounter--;
                                }
                            });

                            var pytid = getUrlParameter('pytid');

                            if (pytid) {
                                var tresc = <?php echo json_encode($row['Quest'] ?? ''); ?>;

                                // If 'pytid' exists, set content based on the parameter value
                                $('.note-editable').html(tresc);
                                $('.summernote').html(tresc);

                                var pts = <?php echo json_encode($row['Rewards'] ?? ''); ?>;

                                var answer = <?php echo json_encode($row['After'] ?? ''); ?>;

                                // If 'pytid' exists, set content based on the parameter value
                                $('.note-editable').eq(1).html(answer);
                                $('.summernote').eq(1).html(answer);

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
                                    $("#addOptionBtn").hide();
                                    $("#removeOptionBtn").hide();
                                    $("#questionForm input[type='radio']").removeAttr("required");
                                    $("select[name='type'] option[value=2]").prop("selected", "selected")
                                } else {
                                    // get correct pos
                                    var correct = <?php echo json_encode($correct ?? ''); ?>;
                                    // Access the positions array in JavaScript
                                    for (var i = 0; i < positions.length; i++) {
                                        var pozId = positions[i]['PozId'];
                                        var Value = positions[i]['Value'];

                                        // Assuming PozId starts from 1, adjust the index accordingly
                                        $(".sinputy[name='option" + pozId + "']").val(Value);

                                        if (pozId == correct) {
                                            $("input[value='a" + pozId + "']").attr('checked', 'checked');
                                        }
                                    }


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
                                    $("#addOptionBtn").hide();
                                    $("#removeOptionBtn").hide();
                                    $("#questionForm input[type='radio']").removeAttr("required");

                                } else {
                                    $(".disclaimer").show();
                                    $(".quest-options").show()
                                    $("#addOptionBtn").show();
                                    $("#removeOptionBtn").show();

                                    // Set the desired number of options
                                    var optionCounter = <?php echo json_encode($numPositions ?? 4); ?>;
                                    console.log(optionCounter)
                                    // Initialize an empty string to store the options HTML
                                    var optionsHTML = '';

                                    // Loop to generate HTML for each option
                                    for (var i = 1; i <= optionCounter; i++) {
                                        optionsHTML += `
        <div class='quest-option' id='option${i}'>
            Opcja ${i}:
            <input type='radio' name='answer' value='a${i}' required>
            <input type='text' class='sinputy' name='option${i}' value='-'>
        </div>
    `;
                                    }

                                    // Set the HTML content of questOptionsContainer
                                    $("#questOptionsContainer").html(optionsHTML);
                                    $("#questionForm input[type='radio']").prop("required", true);
                                };
                            });

                        });
                    </script>


                </div>
            </div>

    </body>
<?php


}

?>