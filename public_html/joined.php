<?php
  
  session_start();
  
    if(isset($_SESSION['info'])){
            echo "<div class='info'>";
            echo $_SESSION['info'];
            echo "</div>";
            unset($_SESSION['info']);
            }
  
  
if (!isset($_SESSION['username'])){
    $_SESSION['info']='Brak dostępu';
    header('Location:error.php');
}

  require('connect.php');
  
            if(isset($_SESSION['info'])){
            echo "<div class='info'>";
            echo $_SESSION['info'];
            echo "</div>";
            unset($_SESSION['info']);
            }
            
?>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $('#participantsInfo').html('<div class="loading-spinner"></div>Loading...');
        
        function checkTournamentStatus() {
            
            $.ajax({
                url: 'chkStatus.php',
                type: 'GET',
                dataType: 'json', // Wskazujemy, że oczekujemy danych JSON
                success: function(response) {
                    
                    var creator = response.creator
                    var status = response.status
                    
                                        // Wyświetl status turnieju
                    $('#statusInfo').text('Status turnieju: ' + status);

                    // Wyświetl listę uczestników
                    var participantsList = '<table class="users">';

                  for (var i = 0; i < response.participants.length; i++) {
            participantsList += '<tr><td>';
            
            var username = $("data#username").attr("value");
            
            if(response.participants[i].Login==username){
                participantsList +='<b><font color="blue">' + response.participants[i].Login +"</font></b></td>"
            }else{
                participantsList +='<b>' + response.participants[i].Login +"</b></td>"
            }
            
            participantsList += "<td> Wynik: <span class='score-edit' contenteditable='true' data-login='" + response.participants[i].Login + "'>" + response.participants[i].CurrentScore + '</span></td></tr>';
                   }
                    participantsList += '</table>';
                    
                    $('#participantsInfo').html('Organizator:<b> '+response.creator+
                    '</b><br>Rzule: ' + participantsList);
                    
                    
                    if(status=='R'){
                    $('.startpopup').html('<div class="loading-spinner"></div>Rozpoczęto- ładuje buzzer...');
                    $('#startform').hide();
                    
                 //                 $.ajax({
        //     url: 'quest.php',
        //     type: 'GET',
        //     dataType: 'json',
        //     success: function(quests) {
        //         console.log(quests);
        //         // Tutaj możesz przetwarzać dane zwrócone z quest.php
        //         var PytId = quests.PytId;
        //         var Quest = quests.Quest;
        //         var Category = quests.Category;
        //         var TypeId = quests.TypeId;
        //         var whoFirst = quests.whoFirst;
        //         var Rewards = quests.Rewards;
        //         var pozycje = quests.pozycje;

        //         $('.startpopup').html('Kategoria: '+Category +'<br>Punkty: '+Rewards +'<BR><span id="quest">' + Quest 
        //         +"</span><BR><div class='quest-options' id='questOptionsContainer'>"+'</div>');
        
        //         wyswietlPozycje(pozycje);
                
        //         $('#statusInfo').text('Status turnieju: ' + status);

        //             // Wyświetl listę uczestników
        //             var participantsList = '<table class="users">';

        //           for (var i = 0; i < response.participants.length; i++) {
        //     participantsList += '<tr><td><b>' + response.participants[i].Login +"</td><td></b> Wynik: <span class='score-edit' contenteditable='true' data-login='" + response.participants[i].Login + "'>" + response.participants[i].CurrentScore + '</span></td></tr>';

        //            }
        //             participantsList += '</table>';
                    
        //             $('#participantsInfo').html('Organizator:<b> '+response.creator+
        //             '</b><br>Rzule: ' + participantsList);
                    
        //     },
        //     error: function() {
        //         $('#statusInfo').text('Błąd podczas pobierania pytań.');
        //         $('#participantsInfo').html('Error');
        //     }
        // });
                    
                }
                },
                error: function() {
                    $('#statusInfo').text('Błąd podczas sprawdzania statusu turnieju.');
                    $('#participantsInfo').html('Error');
                }
            });
            
            
            

        
        
                $('body').on('blur', '.score-edit', function() {
    var login = $(this).data('login');
    var newScore = $(this).text();
    
    $.ajax({
        url: 'updateScore.php',
        type: 'POST',
        data: {
            login: login,
            newScore: newScore,
        },
        success: function(response) {
            // Aktualizuj listę uczestników po zaktualizowaniu wyniku
            checkTournamentStatus();
        },
        error: function() {
             $('#statusInfo').text('error.');
        }
    });
});
        }
        



        checkTournamentStatus();
        // Uruchamiaj funkcję co 2 sekundy
        //setInterval(checkTournamentStatus, 2000);
    });
</script>


<head>
    <title>TTT-TeTeTurnieje</title>
    <link rel="icon" type="image/gif" href="images/title.png">
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Work+Sans:wght@300&display=swap" rel="stylesheet">
    <script src="script.js"></script>
</head>
<body>

    <div id="main-container">
        <div id='head'>
            <span>TETETURNIEJE</span>
        </div> 

        <div id='content'>
            <div class='startpopup'>
                <div class="loading-spinner"></div>
                Oczekiwanie na rozpoczęcie...<br><br>

        </div> 
<?php
        $turniejid = $_SESSION['TurniejId']; 
                        
if(isset($_SESSION['leader']) && $turniejid==$_SESSION['leader']){
   echo '<form method="post" id="startform"><button id="start" name="start" class="button-85" type="submit" margin-top="0px">START</button></form>';
                }
                
                if(isset($_POST['start'])){
                    // Wykonaj zapytanie do bazy danych, aby zaktualizować kod turnieju
        $sql = "UPDATE turnieje SET Status='R' WHERE TurniejId = $turniejid";
         
        if ($conn->query($sql) === TRUE) {
            $_SESSION['info'] = "Success!";
        } else {
            $_SESSION['info'] = "Błąd!";
            //for debbuging echo . $conn->error;
        }
                }
         
        echo '<data id="username" value='.$_SESSION['username'].'></data>';
        
        
$conn->close();
                ?>
                
                
<div id="statusInfo"></div>
<div id="participantsInfo"></div>



</body>
