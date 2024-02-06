<?php
session_start();

if (isset($_POST['language'])) {
    $validLanguages = ['en', 'pl'];
    $selectedLanguage = $_POST['language'];

    if (in_array($selectedLanguage, $validLanguages)) {
        $_SESSION['lang'] = $selectedLanguage;
        echo 'Language set to: ' . $selectedLanguage;
    } else {
        echo 'Invalid language value';
    }
} else {
    echo 'Language data not provided';
}
