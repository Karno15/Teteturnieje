<?php
session_start();

include_once('translation/' . $_SESSION['lang'] . ".php");

if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = $lang["notFound"];
    header('Location:host.php');
} elseif (!isset($_SESSION['userid'])) {
    $_SESSION['info'] = $lang["noAccess"];
    header('Location:index.php');
} else {

    require "connect.php";

    $userId = $_SESSION['userid'];
    $turniejId = mysqli_real_escape_string($conn, $_GET['turniejid']);

    $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
    $stmt->bind_param("i", $turniejId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creatorId = $row['Creator'];

        if ($creatorId != $userId) {
            $_SESSION['info'] = $lang["noAccess"];
            header("Location:edit.php?turniejid=" . $turniejId);
        }
    } else {
        $_SESSION['info'] = $lang["notFound"];
        header("Location:edit.php?turniejid=" .  $turniejId);
    }
    $stmt->close();
    mysqli_close($conn);

?>

    <head>
        <title>TTT-TeTeTurnieje</title>
        <script src="jquery/jquery.min.js"></script>
        <link rel="icon" type="image/gif" href="images/favicon.ico">
        <link rel="stylesheet" href="style.css">
        <script>
            var langses = <?php echo json_encode($_SESSION['lang']); ?>;
            var lang = langses || 'en';
            localStorage.setItem("lang", lang);
        </script>
        <script src="script.js"></script>
        <script src="translation/translation.js"></script>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="jquery/jquery-ui.css">
        <script src="jquery/jquery-ui.js"></script>

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
        <div id="main-container">
            <div id='head'>
                <span>TETETURNIEJE</span>
            </div>

            <div id='content'>

                <b id='editArrangement'>
                </b>
                <div class="startpopup">
                    <span id='columnAmt'></span> <input type="number" class='codeconfrim' id="columnInput" min="1">
                    <button onclick="updateGrid()" class='codeconfrim' id='setColumns'></button>
                    <div class="gridpopup">
                        <div id="grid-container" class="grid-container"></div>
                    </div>
                </div>
                <button onclick="location.href='edit.php?turniejid=<?= $turniejId ?>'" id="back" class="codeconfrim"></button>
                <script>
                    $(document).ready(function() {
                        $("#editArrangement").html(translations['editArrangement'][lang]);
                        $("#columnAmt").html(translations['columnAmt'][lang]);
                        $("#setColumns").html(translations['setColumns'][lang]);
                        $("#back").html(translations['return'][lang]);

                        var turniejidFromURL = getUrlParameter('turniejid');
                        var maxColumns = 8;

                        createGrid();

                        function createGrid() {
                            var gridContainer = $('#grid-container');

                            $.ajax({
                                url: 'getCategory.php?turniejid=' + turniejidFromURL,
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    if (response.length > 0 && response[0].hasOwnProperty('Columns')) {
                                        gridContainer.empty();

                                        var defaultColumns = Math.min(response[0].Columns, maxColumns);

                                        for (var i = 0; i < response.length; i++) {
                                            var gridItem = $("<div class='category' style='cursor:move;' data-pytid='" + response[i].PytId + "'>" +
                                                response[i].Category + "<br><br>" + translations['pts'][lang] + ": " +
                                                (response[i].IsBid ? translations['betting'][lang] : response[i].Rewards) + "</div>");
                                            gridContainer.append(gridItem);
                                        }

                                        gridContainer.sortable({
                                            items: '.category',
                                            cursor: 'move',
                                            tolerance: 'pointer',
                                            update: function(event, ui) {
                                                saveGridOrder(defaultColumns);
                                            }
                                        });
                                        $('#columnInput').val(defaultColumns);

                                        gridContainer.css('grid-template-columns', 'repeat(' + defaultColumns + ', 1fr)');
                                    } else {
                                        console.error('Error');
                                        window.location.href = 'edit.php?turniejid=' + turniejidFromURL + '&info=' + encodeURIComponent(translations['noData'][lang]);
                                    }
                                },
                                error: function(error) {
                                    console.error('Error:', error);
                                    window.location.href = 'edit.php?turniejid=' + turniejidFromURL + '&info=' + encodeURIComponent(translations['noData'][lang]);
                                }
                            });

                        }

                        function saveGridOrder(columns) {
                            var gridOrder = [];
                            $('.category').each(function() {
                                gridOrder.push($(this).data('pytid'));
                            });

                            $.ajax({
                                url: 'saveGridOrder.php',
                                type: 'POST',
                                data: {
                                    turniejid: turniejidFromURL,
                                    order: gridOrder,
                                    columns: columns
                                },
                                success: function(response) {
                                    console.log('Grid order saved successfully:', response);
                                    createGrid(columns);
                                },
                                error: function(error) {
                                    console.error('Error saving grid order:', error);
                                }
                            });
                        }

                        window.updateGrid = function() {
                            var columns = $('#columnInput').val();
                            if (columns > 0) {
                                columns = Math.min(columns, maxColumns);
                                createGrid(columns);

                                saveGridOrder(columns);
                            }
                        };
                    });
                </script>
    </body>
<?php
}
?>