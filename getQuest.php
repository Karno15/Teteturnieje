<?php
session_start();

if (isset($_SESSION['userid']) && isset($_GET['id'])) {
    $id_pytania = filter_var($_GET['id'], FILTER_VALIDATE_INT);
    $loggedInUserId = $_SESSION['userid'];
    
    if ($id_pytania <= 0) {
        die("No data");
    }

    require('connect.php');

    $stmt = $conn->prepare("SELECT p.Quest, p.After, p.TypeId, t.Creator FROM `pytania` p JOIN turnieje t ON p.TurniejId=t.TurniejId WHERE p.PytId = ?");
    $stmt->bind_param("i", $id_pytania);
    $stmt->execute();
    $result = $stmt->get_result();

    if (!$result) {
        die("Error: " . $conn->error);
    }

    if ($result->num_rows > 0) {
        while ($row1 = $result->fetch_assoc()) {
            if ($row1['Creator'] != $loggedInUserId) {
                die("No access");
            }
            echo "<div>" . $row1['Quest'] . "</div>";
            echo "<hr>";
            if (isset($row1['Quest'])) {
                $after = $row1['After'];
            }
            $typeId = $row1['TypeId'];
        }

        $stmt = $conn->prepare("SELECT pp.PytId, pp.PozId, Value, po.PozId as 'correct' FROM `pytaniapoz` pp LEFT JOIN prawiodpo po ON po.PytId=pp.PytId WHERE pp.PytId=?");
        $stmt->bind_param("i", $id_pytania);
        $stmt->execute();
        $result1 = $stmt->get_result();

        if (!$result1) {
            die("Error: " . $conn->error);
        }

        if (mysqli_num_rows($result1) > 0 && $typeId == 1) {
            echo "<div class='quest-options'>";
            while ($row = $result1->fetch_assoc()) {
                echo "<div class='quest-option' ";
                if ($row['PozId'] == $row['correct'])
                    echo "id='correct'";
                echo ">" . base64_decode($row['Value']) . "</div>";
            }
            echo "</div><br>";
        }

        if (isset($after)) {
            if (!is_null($after)) {
                echo "<div>" . $after . "</div>";
            }
        }
    } else {
        echo "No access";
    }
    $stmt->close();
    $conn->close();
} else {
    echo "No data";
}
