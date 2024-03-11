<?php

session_start();

require 'connect.php';

include_once('translation/' . $_SESSION['lang'] . ".php");

if (isset($_SESSION['userid']) && isset($_SESSION['TurniejId'])) {
    $userId = $_SESSION['userid'];
    $turniejId = $_SESSION['TurniejId'];
} else {
    echo json_encode(array("error" => $lang["noAccess"]));
    exit();
}

if (isset($_POST['login']) && isset($_POST['newScore'])) {

    $sql = "SELECT Creator FROM turnieje WHERE TurniejId = ?";
    $stmtCreator = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmtCreator, 'i', $turniejId);

    if (mysqli_stmt_execute($stmtCreator)) {
        $result = mysqli_stmt_get_result($stmtCreator);
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $creatorId = $row['Creator'];
            if ($creatorId == $userId && $_SESSION['leader']) {

                $login = $_POST['login'];
                $newScore = $_POST['newScore'];

                $sqlUpdate = "UPDATE turuserzy t JOIN users u ON u.UserId=t.UserId SET t.CurrentScore = ? WHERE u.Login = ? AND t.turniejId = ?";
                $stmtUpdate = mysqli_prepare($conn, $sqlUpdate);
                mysqli_stmt_bind_param($stmtUpdate, 'dsi', $newScore, $login, $turniejId);

                if (mysqli_stmt_execute($stmtUpdate)) {
                    echo "Updated.";
                } else {
                    echo "Error: " . mysqli_stmt_error($stmtUpdate);
                }
                mysqli_stmt_close($stmtUpdate);
            } else {
                echo $lang["noAccess"];
            }
        } else {
            echo $lang["notFound"];
        }
    } else {
        echo "Error: " . mysqli_stmt_error($stmtCreator);
    }
    mysqli_stmt_close($stmtCreator);

    mysqli_close($conn);
} else {
    echo "No data";
}
