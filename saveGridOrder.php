<?php

session_start();

require 'connect.php';

$turniejid = isset($_POST['turniejid']) ? intval($_POST['turniejid']) : 0;
$order = isset($_POST['order']) ? $_POST['order'] : [];
$columns = isset($_POST['columns']) ? intval($_POST['columns']) : 0;

if ($turniejid <= 0 || $columns <= 0 || empty($order)) {
    echo json_encode(['error' => 'Invalid input']);
    exit();
}

$userId = isset($_SESSION['userid']) ? $_SESSION['userid'] : null;

$stmtCheckCreator = $conn->prepare("SELECT Creator FROM turnieje WHERE turniejid = ? AND Creator = ?");
$stmtCheckCreator->bind_param('ii', $turniejid, $userId);
$stmtCheckCreator->execute();
$stmtCheckCreator->store_result();

if ($stmtCheckCreator->num_rows <= 0) {
    echo json_encode(['error' => 'No Access']);
    exit();
}

$stmtCheckCreator->close();

try {
    $questionPlaceholders = implode(',', array_fill(0, count($order), '?'));
    $stmtCheckQuestions = $conn->prepare("SELECT pytId FROM pytania WHERE pytId IN ($questionPlaceholders) AND TurniejId = ?");

    $typeDefinition = str_repeat('i', count($order)) . 'i';

    $values = array_merge($order, [&$turniejid]);
    $bindParams = array();
    foreach ($values as $key => $value) {
        $bindParams[] = &$values[$key];
    }
    $bindParams = array_merge([$typeDefinition], $bindParams);
    call_user_func_array(array($stmtCheckQuestions, 'bind_param'), $bindParams);

    $stmtCheckQuestions->execute();
    $stmtCheckQuestions->store_result();

    if ($stmtCheckQuestions->num_rows !== count($order)) {
        echo json_encode(['error' => 'Invalid list']);
        exit();
    }

    $stmtCheckQuestions->close();


    foreach ($order as $index => $pytId) {
        $stmtUpdateOrder = $conn->prepare("UPDATE pytania SET `Order` = ? WHERE pytId = ?");
        $stmtUpdateOrder->bind_param('ii', $index, $pytId);
        $stmtUpdateOrder->execute();
        $stmtUpdateOrder->close();
    }

    $stmtUpdateColumns = $conn->prepare("UPDATE turnieje SET `Columns` = ? WHERE turniejid = ?");
    $stmtUpdateColumns->bind_param('ii', $columns, $turniejid);
    $stmtUpdateColumns->execute();
    $stmtUpdateColumns->close();

    echo json_encode(['message' => 'Grid order and columns saved successfully']);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error saving grid order and columns: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
