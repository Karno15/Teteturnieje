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


?>

<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/favicon.ico">
    <link rel="stylesheet" href="style.css">    
    <script src="jquery/jquery.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
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
                if ($result->num_rows > 0 && $_SESSION['username'] != $row['Login']) {
                    echo "(" . $_SESSION['username'] . ")";
                }

                ?>
            </div>
            <div class='startpopup'>

                <span id='titlejoin'>DOŁĄCZ DO TURNIEJU</span>
                <div id='definput'>
                    Wpisz nickname:
                    <form action="join.php" method="post">
                        <input type="text" class="inputy" name="login" maxlength="12" required>
                </div>
                <div id='definput'>
                    Wpisz kod:
                    </br>
                    <input type="text" i class="inputy" name="gamecode" pattern="[0-9]{4}" maxlength="4" required>
                </div>
                <button type='submit' class='codeconfrim'>Dołącz!</button>
                </form>
            </div><br>
            <div id='join-back-cont'></div>
            <script>
                $.ajax({
                    url: 'chkStatus.php',
                    type: 'GET',
                    dataType: 'json', // Wskazujemy, że oczekujemy danych JSON
                    success: function(response) {
                        $('#join-back-cont').html("<a href='joined.php' id='join-back'>TURNIEJ W TRAKCIE</a><br><br>");
                        $('#join-back-cont').show();
                    },
                    error: function(response) {
                        $('#join-back-cont').html("");
                        $('#join-back-cont').hide();
                    }
                });
            </script>
            <button class="button-85" id='host'>Hostuj turniej</button>
        </div>
        <div>
        </div>
    </div>
    <div id='popup'> <button id='closeButton' class='codeconfrim'>Powrót</button><br>
        LOGOWANIE
        <br>
        <form action="login.php" method="post">
            Login:<br>
            <input type="text" name="login" class='inputlogin' maxlength="12" required>

            <div id='definput'>
                Hasło:
                </br>
                <input type="password" name="pass" class='inputlogin' required>
            </div>

            <button type='submit' class='codeconfrim'>Loguj</button>
        </form>
    </div><br>
    <div id='footer'>v<span id='ver'><?php echo file_get_contents('verinfo.txt'); ?></span> Made by @karkarno</div>

</body>