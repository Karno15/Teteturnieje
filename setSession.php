<?php
session_start();

if (isset($_POST['info'])) {
    $_SESSION['info'] = $_POST['info'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Info not provided']);
}
