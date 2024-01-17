<?php
ob_start();
session_start();

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


            <?php
            if (!isset($_SESSION['userid'])) {

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
                <button id='login' class='codeconfrim'>Zaloguj się</button>
                <button id='register' class='codeconfrim'>Rejestracja</button>
            <?php
            } else {
                header('Location:logged.php');
            }

            ?>
        </div>
    </div>
    <div id='popup'>
    </div>
    <div id='footer'>v<span id='ver'><?php echo file_get_contents('verinfo.txt'); 
    ob_flush();?></span> Made by @karkarno</div>
</body>