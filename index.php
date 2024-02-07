<?php
ob_start();
session_start();

if (!isset($_SESSION['lang'])) {
    $userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $_SESSION['lang'] = (stripos($userLanguages[0], 'pl') === 0) ? 'pl' : 'en';
}

$lang = (isset($_SESSION['lang']) ? $_SESSION['lang'] : '');

?>

<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/favicon.ico">
    <link rel="stylesheet" href="style.css">
    <script src="jquery/jquery.min.js"></script>
    <script>
        var langses = <?php echo json_encode($_SESSION['lang']); ?>;
        var lang = langses || 'en';
        localStorage.setItem("lang", lang);
    </script>
    <script src="script.js"></script>
    <script src="translation/translation.js"></script>
    <style>
        blockquote {
            width: 50%;
            max-width: 500px;
            padding: 0px 30px 20px 30px;
            margin: 50px auto;
            position: relative;
            background-color: white;

            p {
                background: linear-gradient(135deg, magenta, blue);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
        }

        blockquote::after {
            content: '';
            height: 100%;
            width: 100%;
            display: block;
            background: linear-gradient(135deg, magenta, blue);
            position: absolute;
            top: 17px;
            left: 17px;
            z-index: -1;
        }

        blockquote::before {
            content: '';
            height: calc(100% + 6px);
            width: calc(100% + 6px);
            display: block;
            background: linear-gradient(135deg, magenta, blue);
            position: absolute;
            top: -3px;
            left: -3px;
            z-index: -2;
        }
    </style>
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
            <blockquote>
                <p id="pageInfo">
                </p>
            </blockquote>
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
                <button id='login' class='codeconfrim'></button>
                <button id='register' class='codeconfrim'></button>
            <?php
            } else {
                header('Location:logged.php');
            }
            ?>
            <span id="contact"></span>
        </div>
    </div>
    <div id='popup'>
    </div>
    <div id='footer'>v<span id='ver'><?php echo file_get_contents('verinfo.txt');
                                        ob_flush(); ?>
        </span> Made by @karkarno
    </div>

</body>