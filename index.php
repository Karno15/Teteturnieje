<?php
ob_start();
session_start();

if (!isset($_SESSION['lang'])) {
    // Set default language to 'en' (English)
    $userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    
    // Check if the first language in the list is 'pl' (Polish), otherwise set it to 'en' (English)
    $_SESSION['lang'] = (stripos($userLanguages[0], 'pl') === 0) ? 'pl' : 'en';
 //   $_SESSION['lang'] = 'en';
    
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

    <script>
        $(document).ready(function() {
    // Flag click flag
    var flagClick = false;

    // Set initial flag based on $lang
    $('.flag').css('background-image', 'url(' + getFlagUrl('<?php echo $lang; ?>') + ')');

    // Change flag on click
    $('.flag').on('click', function() {
        flagClick = true; // Set flag to true when flag is clicked

        var currentLang = $('.lang-select').val();
        var newLang = (currentLang === 'pl') ? 'en' : 'pl';

        // Set the new flag
        $('.flag').css('background-image', 'url(' + getFlagUrl(newLang) + ')');

        // Change the selected option in the hidden select element
        $('.lang-select').val(newLang);

        // Trigger the change event to handle the language change
        $('.lang-select').trigger('change');
    });

    // Change flag on select change
    $('.lang-select').on('change', function() {
        if (!flagClick) { // Check if the change event was triggered by the flag click
            let lang = $(this).val();
            localStorage.setItem("lang", lang);
            // Make an AJAX call to setlang.php
            $.post('setlang.php', {
                language: lang
            }, function(response) {
                console.log('Language changed to: ' + lang);
                location.reload();
            });
        }

        // Reset the flagClick variable
        flagClick = false;
    });

    // Function to get flag URL based on language
    function getFlagUrl(lang) {
        return (lang === 'en') ? 'images/en.svg' : 'images/pl.svg';
    }
});

    </script>

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
                <button id='login' class='codeconfrim'></button>
                <button id='register' class='codeconfrim'></button>
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
                                        ob_flush(); ?></span> Made by @karkarno</div>
</body>