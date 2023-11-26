<?php

session_start();

if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}

?>

<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300&display=swap" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script src="script.js"></script>
</head>

<body>
    <div class="popup-overlay"></div>
    <div id="main-container">
        <div id='head'>
            <span>TETETURNIEJE</span>
        </div>
        <div id='content'>
            Not found

            <?php
            //echo $_SESSION['userid'];
            if (!isset($_SESSION['userid'])) {
            ?>
                <button id='login' class='codeconfrim'>Zaloguj się</button>
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
            <?php
            }

            ?>
        </div>

</body>