<?php
session_start();



if (!isset($_GET['turniejid'])) {
    $_SESSION['info'] = 'Nie znaleziono turnieju';
    header('Location:host.php');
} elseif (!isset($_SESSION['userid'])) {
    $_SESSION['info'] = 'Brak dostępu';
    header('Location:index.php');
} else {
    // Get the user's ID from the session
    $userId = $_SESSION['userid'];

    // Get the TurniejId from the query parameter
    $turniejId = $_GET['turniejid'];

    // Connect to the database
    require "connect.php"; // Assuming you have a connection script

    // Query to check if the TurniejId belongs to the user
    $stmt = $conn->prepare("SELECT Creator FROM turnieje WHERE TurniejId = ?");
    $stmt->bind_param("i", $turniejId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $creatorId = $row['Creator'];

        // Check if the TurniejId's creator matches the user's ID - if yes do the rest
        if ($creatorId != $userId) {
            $_SESSION['info'] = 'Nie znaleziono turnieju';
            header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
        }
    } else {
        $_SESSION['info'] = 'Nie znaleziono turnieju';
        header("Location:edit.php?turniejid=" . $_GET["turniejid"]);
    }
    $stmt->close();
    mysqli_close($conn);

?>

    <head>
        <title>TTT-TeTeTurnieje</title>
        <link rel="icon" type="image/gif" href="images/favicon.ico">>
        <script src="jquery/jquery-3.4.1.slim.min.js"></script>
        <link rel="stylesheet" href="style.css">        
        <script src="script.js"></script>
        <meta charset="UTF-8">
        <link rel="stylesheet" href="jquery/jquery-ui.css">
        <script src="jquery/jquery-3.6.4.min.js"></script>
        <script src="jquery/jquery-ui.js"></script>

    </head>

    <body>
        <div id="main-container">
            <div id='head'>
                <span>TETETURNIEJE</span>
            </div>

            <div id='content'>

                <b>
                    EDYTUJ UŁOŻENIE
                </b>
                <div class="startpopup">
                    Ilość kolumn <input type="number" id="columnInput" min="1">
                    <button onclick="updateGrid()" class='codeconfrim'>Ustaw kolumny</button>
                    <div class="gridpopup">
                        <div id="grid-container" class="grid-container"></div>
                    </div>
                </div>
                <?php
                echo "<button onclick=\"location.href='edit.php?turniejid=" . $_GET['turniejid'] . "'\" id='back'
             class='codeconfrim'>POWRÓT</button>";
                ?>

                <script>
                    $(document).ready(function() {
                        var turniejidFromURL = getUrlParameter('turniejid');
                        var maxColumns = 8; // Maximum number of columns

                        // Initial setup with default columns
                        createGrid();

                        function createGrid() {
                            var gridContainer = $('#grid-container');

                            // Make AJAX request
                            $.ajax({
                                url: 'getCategory.php?turniejid=' + turniejidFromURL,
                                type: 'GET',
                                dataType: 'json',
                                success: function(response) {
                                    if (response.length > 0 && response[0].hasOwnProperty('Columns')) {
                                        gridContainer.empty(); // Clear existing grid items

                                        // Calculate the default number of columns based on the response length
                                        var defaultColumns = Math.min(response[0].Columns, maxColumns);

                                        for (var i = 0; i < response.length; i++) {
                                            var gridItem = $("<div class='category' style='cursor:move;' data-pytid='" + response[i].PytId + "'>" + response[i].Category + "<br><br>Pkt:" + (response[i].IsBid ? 'do obstawienia' : response[i].Rewards) + "</div>");
                                            gridContainer.append(gridItem);
                                        }

                                        // Make grid items draggable and sortable
                                        gridContainer.sortable({
                                            items: '.category',
                                            cursor: 'move',
                                            tolerance: 'pointer',
                                            update: function(event, ui) {
                                                saveGridOrder(defaultColumns); // Pass the number of columns to saveGridOrder
                                            }
                                        });
                                        $('#columnInput').val(defaultColumns);
                                        // Set the grid columns based on user input or default, limited to the maximum
                                        gridContainer.css('grid-template-columns', 'repeat(' + defaultColumns + ', 1fr)');
                                    } else {
                                        console.error('Error: Invalid response format or empty response.');
                                        window.location.href = 'edit.php?turniejid=' + turniejidFromURL + '&info=' + encodeURIComponent('Error: Brak danych.');
                                    }
                                },
                                error: function(error) {
                                    // Handle errors here
                                    console.error('Error:', error);

                                    // Redirect to the previous page with error information
                                    window.location.href = 'edit.php?turniejid=' + turniejidFromURL + '&info=' + encodeURIComponent('Error: Brak danych.');
                                }
                            });

                        }

                        function saveGridOrder(columns) {
                            var gridOrder = [];
                            $('.category').each(function() {
                                gridOrder.push($(this).data('pytid'));
                            });

                            // Make AJAX request to save the grid order and number of columns to the database
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

                                    // After saving the grid order, update the grid
                                    createGrid(columns);
                                },
                                error: function(error) {
                                    console.error('Error saving grid order:', error);
                                }
                            });
                        }

                        window.updateGrid = function() {
                            var columns = $('#columnInput').val();
                            // Limit the input to a maximum of 8 columns
                            columns = Math.min(columns, maxColumns);
                            // Update the input field
                            createGrid(columns);

                            // Call saveGridOrder to update the grid order
                            saveGridOrder(columns);
                        };
                    });
                </script>
    </body>
<?php


}

?>