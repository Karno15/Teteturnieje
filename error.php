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
            error

            <?php
            //echo $_SESSION['userid'];
            if (!isset($_SESSION['userid'])) {
            ?>
                <button id='login' class='codeconfrim'>Zaloguj się</button>

            <?php
            }

            ?>
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

</body>