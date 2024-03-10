<?php

session_start();

if (isset($_SESSION['info'])) {
    echo "<div class='info'>";
    echo $_SESSION['info'];
    echo "</div>";
    unset($_SESSION['info']);
}

include_once('translation/' . $_SESSION['lang'] . ".php");

if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = $lang["notFound"];
    header("Location: index.php");
}

require('connect.php');

$turniejId = mysqli_real_escape_string($conn, $_GET['turniejid']);
$query = "SELECT RANK() OVER (ORDER BY tu.CurrentScore DESC) AS ScoreRank, u.Login,
     tu.CurrentScore FROM turuserzy tu JOIN users u ON u.UserId=tu.UserId 
     WHERE turniejId= ? and CurrentScore IS NOT NULL;";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $turniejId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
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
        * {
            font-size: 62, 5%;
            box-sizing: border-box;
            margin: 0;
        }

        main {
            width: 40rem;
            background-color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            border-radius: 0.5rem;
        }

        #header {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2.5rem 2rem;
        }

        .share {
            width: 4.5rem;
            background-color: #f55e77;
            border: 0;
            border-bottom: 0.2rem solid #c0506a;
            border-radius: 2rem;
            cursor: pointer;
        }

        h1 {
            letter-spacing: 3px;
            font-family: cartoon;
            font-size: 48pt;
            color: #141a39;
            text-transform: uppercase;
            cursor: default;
        }

        #leaderboard {
            width: 100%;
            position: relative;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            color: #141a39;
            cursor: default;
        }

        tr {
            transition: all 0.2s ease-in-out;
            border-radius: 0.2rem;
        }

        tr:not(:first-child):hover {
            background-color: #fff;
            transform: scale(1.05);
        }

        tr:nth-child(odd) {
            background-color: #f9f9f9;
        }

        tr:nth-child(1) {
            color: #fff;
        }

        td {
            height: 5rem;
            font-family: cartoon;
            font-size: 1.4rem;
            padding: 1rem 2rem;
            position: relative;
        }

        .number {
            width: 1rem;
            font-size: 2.2rem;
            font-weight: bold;
            text-align: left;
        }

        .name {
            text-align: left;
            font-size: 1.2rem;
        }

        .points {
            font-weight: bold;
            font-size: 1.3rem;
            display: flex;
            justify-content: flex-end;
            align-items: center;
        }

        .points:first-child {
            width: 10rem;
        }

        .gold-medal {
            height: 3rem;
            margin-left: 1.5rem;
        }

        .ribbon {
            width: 42rem;
            height: 5.5rem;
            top: -0.5rem;
            background-color: #5c5be5;
            position: absolute;
            left: -1rem;
            -webkit-box-shadow: 0px 15px 11px -6px #7a7a7d;
            box-shadow: 0px 15px 11px -6px #7a7a7d;
        }

        .ribbon::before {
            content: "";
            height: 1.5rem;
            width: 1.5rem;
            bottom: -0.8rem;
            left: 0.35rem;
            transform: rotate(45deg);
            background-color: #5c5be5;
            position: absolute;
            z-index: -1;
        }

        .ribbon::after {
            content: "";
            height: 1.5rem;
            width: 1.5rem;
            bottom: -0.8rem;
            right: 0.35rem;
            transform: rotate(45deg);
            background-color: #5c5be5;
            position: absolute;
            z-index: -1;
        }
    </style>
</head>

<body>
    <div id="gear"><img src='images/gear.svg'></div>
    <div class='lang' class="lang-select-container">
        <span class="flag" style="cursor: pointer;"></span>
        <select class="lang-select" name="lang" style="display: none;">
            <option value="pl" <?php echo ($lang === 'pl') ? 'selected' : ''; ?>></option>
            <option value="en" <?php echo ($lang === 'en') ? 'selected' : ''; ?>></option>
        </select>
    </div>
    <div class='lang' class="lang-select-container">
        <span class="flag" style="cursor: pointer;"></span>
        <select class="lang-select" name="lang" style="display: none;">
            <option value="pl" <?php echo ($lang === 'pl') ? 'selected' : ''; ?>></option>
            <option value="en" <?php echo ($lang === 'en') ? 'selected' : ''; ?>></option>
        </select>
    </div>
    <div class="tooltiplang"></div>
    <div class="popup-overlay"></div>
    <div id="main-container">
        <div id='head'>
            <span>TETETURNIEJE</span>
        </div>
        <div id='content'>
            <div class='startpopup'>
                <main>
                    <div id="header">
                        <h1 id="turResult"></h1>
                    </div>
                    <div id="leaderboard">

                        <table>
                            <?php
                            while ($row = $result->fetch_assoc()) {
                                if ($row['ScoreRank'] == 1) {
                                    echo '<div class="ribbon"></div>';
                                }
                                echo  '<tr><td class="number">' . $row['ScoreRank'] . '</td>';
                                echo  '<td class="name">' . $row['Login'] . '</td>';
                                echo  '<td class="points">' . $row['CurrentScore'];
                                if ($row['ScoreRank'] == 1) {
                                    echo    '<img class="gold-medal" src="images/gold-medal.png?raw=true" alt="gold medal" />';
                                }
                                echo '</td></tr>';

                                if (isset($_SESSION['username'])) {
                                    if ($row['Login'] == $_SESSION['username']) {
                                        $yourScore = $row['CurrentScore'];
                                    }
                                }
                            }
                            ?>
                        </table>
                    </div>
                </main>
                <?php
                if (isset($yourScore)) {
                ?>
                    <div id='yourscore'>
                    <?php
                    echo  $lang["yourScore"] . ": <br>" . $yourScore;
                }
                    ?>
                    </div>
                    <script>
                        $(document).ready(function() {
                            $("#turResult").html(translations['turResult'][lang]);
                            $("#back").html(translations['return'][lang]);

                            $("#back").click(function() {
                                window.location.href = 'logged.php';
                            })
                        });
                    </script>
                    <button id='back' class='button-85' style='padding: 0; min-width: 200px;'></button>
                    <?php
                    if (isset($_SESSION['userid'])) {
                        unset($_SESSION['TurniejId']);
                        unset($_SESSION['currentQuest']);
                    }
                    ?>
            </div>
        </div>
    </div>
</body>