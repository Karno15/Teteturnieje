<?php
session_start();

if(isset($_POST['language'])) {
    $_SESSION['lang'] = $_POST['language'];
    echo 'Language set to: ' . $_POST['language'];
} else {
    echo 'Invalid language data';
}
