<?php
session_start();

include_once('translation/' . $_SESSION['lang'] . ".php");

if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = $lang["notFound"];
    header('Location:host.php');
} elseif (!isset($_SESSION['userid'])) {
    $_SESSION['info'] = $lang["noAccess"];
    header('Location:index.php');
} else {

    require "connect.php";

    $userId = $_SESSION['userid'];
    $turniejId = mysqli_real_escape_string($conn, $_GET['turniejid']);

    $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
    $stmt->bind_param("i", $turniejId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creatorId = $row['Creator'];

        if ($creatorId != $userId) {
            $_SESSION['info'] = $lang["notFound"];
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
        }
    } else {
        $_SESSION['info'] = $lang["notFound"];
        header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
    }

    if (isset($_GET["pytid"])) {
        $pytid = $_GET["pytid"];
        $stmt = $conn->prepare("SELECT PytId, Quest, TypeId, Category, IsBid, Rewards, After FROM pytania WHERE TurniejId = ? AND PytId = ?");
        $stmt->bind_param("ii", $turniejId, $pytid);
        $stmt->execute();
        $result = $stmt->get_result();

        $row = $result->fetch_assoc();

        if ($result->num_rows == 0) {
            $_SESSION['info'] = $lang["notFound"];
            echo $_SESSION['info'];
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
        }
        $stmt->close();

        $positions = array();

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
                'Value' => base64_decode($rowpoz['Value'])
            );
            $positions[] = $position;
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
        $type = $_POST["type"]; //1- closed, 2- open
        $after = $_POST["after"];
        $maxFileSize =  5 * 1024 * 1024;

        if (strlen($tresc) > $maxFileSize || strlen($after) > $maxFileSize) {
            $_SESSION['info'] = $lang["limitReached"];
            header("Location: edit.php?turniejid=" . $_GET["turniejid"]);
            exit();
        }

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
            $stmt = $conn->prepare("INSERT INTO `pytania`(`TurniejId`, `Quest`, `TypeId`, `Rewards`, `Category`, `IsBid`, `After`) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isidsis", $turniejId, $tresc, $type, $rewards, $category, $isbid, $after);
            $stmt->execute();

            if ($stmt->error) {
                $_SESSION['info'] = "Error: " . $stmt->error;
            } else {
                $pytanie_id = $stmt->insert_id;

                if ($type == 1) {
                    $stmt1 = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                    $stmt2 = $conn->prepare("INSERT INTO prawiodpo (`PytId`, `PozId`) VALUES (?, ?)");

                    $options = array();

                    for ($i = 1; isset($_POST["option{$i}"]); $i++) {
                        $options[] = $_POST["option{$i}"];
                    }

                    $i = 1;
                    foreach ($options as $op) {
                        $odpowiedz = base64_encode(trim($op));
                        $stmt1->bind_param("iss", $pytanie_id, $i, $odpowiedz);
                        $stmt1->execute();

                        if ($stmt1->error) {
                            $_SESSION['info'] = $stmt1->error;
                            break;
                        }
                        $selectedAnswer = isset($_POST["answer"]) ? $_POST["answer"] : NULL;
                        $selectedAnswerId = substr($selectedAnswer, 1);

                        if ($i == $selectedAnswerId) {
                            $stmt2->bind_param("ii", $pytanie_id, $i);
                            $stmt2->execute();

                            if ($stmt2->error) {
                                $_SESSION['info'] = $stmt2->error;
                                break;
                            }
                        }
                        $i++;
                    }
                    $stmt1->close();
                    $stmt2->close();
                }

                $_SESSION['info'] = $lang["saved"];
                header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
                exit();
            }
        } else {
            $stmtUpdate = $conn->prepare("UPDATE `pytania` SET `Quest` = ?, `TypeId` = ?, `Rewards` = ?, `Category` = ?, `IsBid` = ?, `After` = ? WHERE `PytId` = ?");
            $stmtUpdate->bind_param("sidsisi", $tresc, $type, $rewards, $category, $isbid, $after, $pytid);
            $stmtUpdate->execute();

            if ($stmtUpdate->error) {
                $_SESSION['info'] = "Error updating: " . $stmtUpdate->error;
            } else {
                if ($type == 1) {

                    if ($positions > 0 && $correct) {
                        $stmtDeleteOptions = $conn->prepare("DELETE FROM pytaniapoz WHERE PytId = ?");
                        $stmtDeleteOptions->bind_param("i", $pytid);
                        $stmtDeleteOptions->execute();

                        if ($stmtDeleteOptions->error) {
                            $_SESSION['info'] = "Error deleting: " . $stmtDeleteOptions->error;
                        } else {
                            $stmtInsertOptions = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                            $options = array();

                            for ($i = 1; isset($_POST["option{$i}"]); $i++) {
                                $options[] = $_POST["option{$i}"];
                            }

                            $i = 1;
                            foreach ($options as $op) {
                                $odpowiedz = base64_encode(trim($op));
                                $stmtInsertOptions->bind_param("iss", $pytid, $i, $odpowiedz);
                                $stmtInsertOptions->execute();

                                if ($stmtInsertOptions->error) {
                                    $_SESSION['info'] = "Error inserting: " . $stmtInsertOptions->error;
                                    break;
                                }

                                $i++;
                            }
                            $stmtInsertOptions->close();
                        }
                        $stmtDeleteOptions->close();
                    } else {
                        $stmt1 = $conn->prepare("INSERT INTO pytaniapoz (`PytId`, `PozId`, `Value`) VALUES (?, ?, ?)");

                        $stmt2 = $conn->prepare("INSERT INTO prawiodpo (`PytId`, `PozId`) VALUES (?, ?)");

                        $options = array();

                        for ($i = 1; isset($_POST["option{$i}"]); $i++) {
                            $options[] = $_POST["option{$i}"];
                        }

                        $i = 1;
                        foreach ($options as $op) {
                            $odpowiedz = base64_encode(trim($op));
                            $stmt1->bind_param("iss", $pytid, $i, $odpowiedz);
                            $stmt1->execute();

                            if ($stmt1->error) {
                                $_SESSION['info'] = $stmt1->error;
                                break;
                            }

                            $selectedAnswer = isset($_POST["answer"]) ? $_POST["answer"] : NULL;
                            $selectedAnswerId = substr($selectedAnswer, 1);

                            if ($i == $selectedAnswerId) {
                                $stmt2->bind_param("ii", $pytid, $i);
                                $stmt2->execute();

                                if ($stmt2->error) {
                                    $_SESSION['info'] = $stmt2->error;
                                    break;
                                }
                            }
                            $i++;
                        }
                        $stmt1->close();
                        $stmt2->close();
                    }
                }
                $stmtUpdateOdp = $conn->prepare("UPDATE prawiodpo SET `PozId` = ? WHERE `PytId` = ?");
                $selectedAnswer = isset($_POST["answer"]) ? $_POST["answer"] : NULL;
                $selectedAnswerId = substr($selectedAnswer, 1);

                $stmtUpdateOdp->bind_param("ii", $selectedAnswerId, $pytid);
                $stmtUpdateOdp->execute();

                if ($stmtUpdateOdp->error) {
                    $_SESSION['info'] = "Error updating: " . $stmtUpdateOdp->error;
                }

                $stmtUpdateOdp->close();
            }
            $stmtUpdate->close();

            $_SESSION['info'] = $lang["saved"];
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
            exit();
        }
    }
?>

    <head>
        <title>TTT-TeTeTurnieje</title>
        <link rel="icon" type="image/gif" href="images/favicon.ico">
        <script>
            var langses = <?php echo json_encode($_SESSION['lang']); ?>;
            var lang = langses || 'en';
            localStorage.setItem("lang", lang);
        </script>
        <script src="translation/translation.js"></script>
        <script src="jquery/jquery.min.js"></script>
        <link href="summernote/summernote-lite.min.css" rel="stylesheet">
        <script src="summernote/summernote-lite.min.js"></script>
        <link rel="stylesheet" href="summernote/summernote-audio.css">
        <script type="text/javascript" src="summernote/summernote-audio.js"></script>
        <link rel="stylesheet" href="style.css">
        <script src="script.js"></script>
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
        <div id="popup"><span id='loadingQuest'></span><br>
            <div class='loading-spinner'></div>
        </div>
        <div class='popup-overlay'></div>
        <div id="main-container">
            <div id='head'>
                <span>TETETURNIEJE</span>
            </div>

            <div id='content'>

                <b>
                    <?php
                    echo isset($pytid) ? $lang["editQuestion"] : $lang["newQuestion"];
                    ?>
                </b>

                <div class='startpopup'>

                    <form action="#" method='post' id='questionForm'>
                        <span id='category'></span>
                        <input type='text' name='category' style='width:50%;
    height:40px;
    font-size: 20pt;
    ' value='<?php echo isset($pytid) ? $row['Category'] : ''; ?>'>
                        <hr><br>
                        <span id='contentQuest'></span>
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
                                    ['insert', ['link', 'picture', 'video', 'audio']],
                                    ['view', ['fullscreen', 'codeview', ]],
                                ],
                                dialogsInBody: true
                            });
                        </script>
                        <br>
                        <span class="disclaimer" id='audioTip'></span>
                        <hr>
                        <span id='questType'></span>
                        <select name='type' class='codeconfrim'>
                            <option value='1' id='closed'></option>
                            <option value='2' id='open'></option>
                        </select>
                        <span class='section-options'>
                            <br>
                            <span class='disclaimer' id='tipOptions'></span><br>

                            <button type="button" id="addOptionBtn" class="codeconfrim">+</button>
                            <button type="button" id="removeOptionBtn" class="codeconfrim">-</button>
                            <div class='quest-options' id='questOptionsContainer'>
                                <?php
                                $numOptions = $numPositions ?? 4;

                                for ($i = 1; $i <= $numOptions; $i++) {
                                    echo "
        <div class='quest-option' id='option{$i}'>" . $lang["option"] . " {$i}:
            <input type='radio' name='answer' value='a{$i}' required>
            <input type='text' class='sinputy' name='option{$i}' value='-'>
        </div>
    ";
                                }
                                ?>

                            </div>

                            <br>
                            <span class='disclaimer' id='tipCheck'></span>
                        </span>
                        <hr>
                        <br>
                        <span id='ptsAmount'></span>
                        <input type='number' name='rewards' step=".01" class='codeconfrim' value='50'>
                        <span><span id='ptsBet'></span>
                            <input type='checkbox' name='isbid'></span>

                        <hr><br>
                        <span id='contentAnswer'></span>
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
                                    ['insert', ['link', 'picture', 'video', 'audio']],
                                    ['view', ['fullscreen', 'codeview', ]]
                                ],
                                dialogsInBody: true
                            });
                        </script>
                        <?php
                        echo "<button  type='button' onclick=\"location.href='edit.php?turniejid=" . $_GET['turniejid'] . "'\" id='back'
             class='codeconfrim'>POWRÓT</button>";
                        ?>

                        <input type='submit' id='save' name='submity' value='ZAPISZ' class='codeconfrim'>
                    </form>
                    <?php
                    echo '<script>';
                    echo 'var positions = ' . json_encode($positions ?? '') . ';';
                    echo '</script>';
                    ?>
                    <script>
                        function showLoadingSpinner() {
                            $(".popup-overlay").show();
                            $("#popup").show();
                        }

                        function hideLoadingSpinner() {
                            $(".popup-overlay").hide();
                            $("#popup").hide();
                        }
                        showLoadingSpinner();
                        $(document).ready(function() {
                            hideLoadingSpinner();



                            var optionCounter = <?php echo json_encode($numPositions ?? 4); ?>;

                            $("#addOptionBtn").click(function() {
                                if (optionCounter < 30) {
                                    optionCounter++;

                                    var newOptionHTML = translations['option'][lang] + ` ${optionCounter}:
<input type='radio' name='answer' value='a${optionCounter}' required>
<input type='text' class='sinputy' name='option${optionCounter}' value='-'>
`;
                                    $("#questOptionsContainer").append(`<div class='quest-option' id='option${optionCounter}'>${newOptionHTML}</div>`);
                                }
                            });

                            $("#removeOptionBtn").click(function() {
                                if (optionCounter > 2) {
                                    $(`#option${optionCounter}`).remove();
                                    optionCounter--;
                                }
                            });

                            var pytid = getUrlParameter('pytid');

                            if (pytid) {
                                var tresc = <?php echo json_encode($row['Quest'] ?? ''); ?>;

                                $('.note-editable').html(tresc);
                                $('.summernote').html(tresc);

                                var pts = <?php echo json_encode($row['Rewards'] ?? ''); ?>;

                                var answer = <?php echo json_encode($row['After'] ?? ''); ?>;

                                $('.note-editable').eq(1).html(answer);
                                $('.summernote').eq(1).html(answer);

                                var isBid = <?php echo json_encode($row['IsBid'] ?? ''); ?>;
                                if (isBid) {
                                    $('input[name="isbid"]').attr('checked', 'checked');
                                    $('input[name="rewards"]').prop('type', 'text');
                                    $('input[name="rewards"]').val('(' + translations['betting'][lang] + ')');
                                } else {
                                    $('input[name="rewards"]').val(pts);
                                }

                                var typeid = <?php echo json_encode($row['TypeId'] ?? ''); ?>;
                                if (typeid == 2) {
                                    $(".section-options").hide();
                                    $("#questionForm input[type='radio']").removeAttr("required");
                                    $("select[name='type'] option[value=2]").prop("selected", "selected")
                                } else {
                                    var correct = <?php echo json_encode($correct ?? ''); ?>;
                                    for (var i = 0; i < positions.length; i++) {
                                        var pozId = positions[i]['PozId'];
                                        var Value = positions[i]['Value'];

                                        $(".sinputy[name='option" + pozId + "']").val(Value);

                                        if (pozId == correct) {
                                            $("input[value='a" + pozId + "']").attr('checked', 'checked');
                                        }
                                    }
                                }

                            } else {
                                $('.note-placeholder').html(translations['contentPlaceholder'][lang]);
                                $('.note-placeholder').eq(1).html(translations['answerPlaceholder'][lang]);
                            }

                            $('input[name="isbid"]').on('change', function() {
                                var rewards = $('input[name="rewards"]');

                                if (rewards.prop('type') !== 'text') {
                                    rewards.prop('disabled', true);
                                    rewards.prop('type', 'text');
                                    rewards.val('(' + translations['betting'][lang] + ')');
                                } else {
                                    rewards.prop('disabled', false);
                                    rewards.prop('type', 'number');
                                    if (!pts) {
                                        pts = 50;
                                    }
                                    var numericPts = parseFloat(rewards.val());
                                    if (!isNaN(numericPts)) {
                                        rewards.val(numericPts);
                                    } else {
                                        rewards.val(pts);
                                    }
                                }
                            });

                            $('select[name="type"]').on('change', function() {
                                var opcja = $('select[name="type"] option:selected').val();
                                if (opcja == 2) {
                                    $(".section-options").hide();
                                    $("#questionForm input[type='radio']").removeAttr("required");

                                } else {
                                    $(".section-options").show();

                                    var optionCounter = <?php echo json_encode($numPositions ?? 4); ?>;
                                    var optionsHTML = '';

                                    for (var i = 1; i <= optionCounter; i++) {
                                        optionsHTML += `<div class='quest-option' id='option${i}'>` + translations['option'][lang] + ` ${i}:
            <input type='radio' name='answer' value='a${i}' required>
            <input type='text' class='sinputy' name='option${i}' value='-'>
        </div>
    `;
                                    }
                                    $("#questOptionsContainer").html(optionsHTML);
                                    $("#questionForm input[type='radio']").prop("required", true);
                                };
                            });
                            $("#back").html(translations['return'][lang]);
                            $("#save").val(translations['save'][lang]);
                            $("#contentQuest").html(translations['contentQuest'][lang] + ":");
                            $("#audioTip").html(translations['audioTip'][lang]);
                            $("#tipOptions").html(translations['tipOptions'][lang]);
                            $("#tipCheck").html(translations['tipCheck'][lang]);
                            $("#ptsAmount").html(translations['ptsAmount'][lang] + ":");
                            $("#ptsBet").html(translations['ptsBet'][lang] + ":");
                            $("#contentAnswer").html(translations['contentAnswer'][lang] + ":");
                            $("#ptsAmount").html(translations['ptsAmount'][lang] + ":");
                            $("#questType").html(translations['questType'][lang] + ":");
                            $("#open").html(translations['open'][lang]);
                            $("#closed").html(translations['closed'][lang]);
                        });
                    </script>

                </div>
            </div>
        </div>
    </body>
<?php
}
mysqli_close($conn);
?>