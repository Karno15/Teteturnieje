<?php
session_start();

$userLanguages = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);

$lang = isset($_SESSION['lang']) ? $_SESSION['lang'] : $userLanguages[0];

$_SESSION = array();

session_destroy();
session_regenerate_id(true);

session_start();

$_SESSION['lang'] = $lang;

header('Location: index.php');
exit;
