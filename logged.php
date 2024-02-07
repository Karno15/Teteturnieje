<?php

session_start();

if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}

if (!isset($_SESSION['userid']) || !isset($_SESSION['username'])) {
    header('Location:index.php');
}

if (!isset($_SESSION['lang'])) {
    $userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $_SESSION['lang'] = (stripos($userLanguages[0], 'pl') === 0) ? 'pl' : 'en';
}

$lang = (isset($_SESSION['lang']) ? $_SESSION['lang'] : '');
$login = (isset($_SESSION['username']) ? $_SESSION['username'] : '');

?>

<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="jquery/jquery.min.js"></script>
    <script>
        var langses = <?php echo json_encode($_SESSION['lang']); ?>;
        var lang = langses || 'en';
    </script>
    <script src="script.js"></script>
    <script src="translation/translation.js"></script>
</head>

<body>
    <div id='lang' class="lang-select-container">
        <span class="flag" style="cursor: pointer;"></span>
        <select class="lang-select" name="lang" style="display: none;">
            <option value="pl" <?php echo ($lang === 'pl') ? 'selected' : ''; ?>></option>
            <option value="en" <?php echo ($lang === 'en') ? 'selected' : ''; ?>></option>
        </select>
    </div>
    <div id='lang' class="lang-select-container">
        <span class="flag" style="cursor: pointer;"></span>
        <select class="lang-select" name="lang" style="display: none;">
            <option value="pl" <?php echo ($lang === 'pl') ? 'selected' : ''; ?>></option>
            <option value="en" <?php echo ($lang === 'en') ? 'selected' : ''; ?>></option>
        </select>
    </div>

    <div class="popup-overlay"></div>
    <div id="main-container">

        <div id='head'>
            <span>TETETURNIEJE</span>
        </div>
        <div id='content'>
            <a href='logout.php' class='codeconfrim logout'><img src='images/logout.svg' height='25' width='25'></a>
            <div id='nickname'>
                <?php

                require('connect.php');
                $sql = "SELECT m.Login FROM masters m JOIN users u ON u.masterId=m.masterId WHERE u.masterId= ? ";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "s", $_SESSION['userid']);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $row = mysqli_fetch_assoc($result);
                if (isset($row['Login'])) {
                    echo $row['Login'];
                }
                if ($result->num_rows > 0 && stripslashes($_SESSION['username']) != $row['Login']) {
                    echo " (" . stripslashes($_SESSION['username']) . ")";
                }

                ?>
            </div>
            <div class='startpopup'>

                <span id='titlejoin'></span>
                <div id='definput'>
                    <span id='enterNickname'></span>
                    <form action="join.php" method="post">
                        <input type="text" class="inputy" name="login" maxlength="12" required>
                </div>
                <div id='definput'>
                    <span id='enterCode'></span>
                    </br>
                    <input type="text" i class="inputy" name="gamecode" pattern="[0-9]{4}" maxlength="4" required>
                </div>
                <button type='submit' class='codeconfrim' id='join'></button>
                </form>
            </div><br>
            <div id='join-back-cont'></div>
            <script>
                $(document).ready(function() {
                    console.log('<?= $login; ?>');

                    var login = decodeEntities('<?= htmlspecialchars_decode($login, ENT_QUOTES); ?>');

                    $("#titlejoin").html(translations['joinTournament'][lang]);
                    $("#join").html(translations['join'][lang]);
                    $("#enterNickname").html(translations['enterNickname'][lang] + ":");
                    $("#enterCode").html(translations['enterCode'][lang] + ":");
                    $("#host").html(translations['host'][lang]);
                    $(".inputy[name='login']").val(login);

                    $.ajax({
                        url: 'chkStatus.php',
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            $('#join-back-cont').html("<a href='joined.php' id='join-back'>" + translations['inProgress'][lang] + "</a><br><br>");
                            $('#join-back-cont').show();
                        },
                        error: function(response) {
                            $('#join-back-cont').html("");
                            $('#join-back-cont').hide();
                        }
                    });
                });
            </script>
            <button class="button-85" id='host'></button>
        </div>
        <div>
        </div>
    </div>
    <br>
    <div id='footer'>v<span id='ver'><?php echo file_get_contents('verinfo.txt'); ?></span> Made by @karkarno</div>

</body>