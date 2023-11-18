$(document).ready(function () {

    $('#head span').click(function (event) {
        event.preventDefault(); // Prevent the default link behavior

        // Redirect to the main index.php page
        window.location.href = 'index.php';
    });

    $("#host").click(function () {
        window.location.href = "host.php";
    });

    $("#login").on("click", function () {
        $("#popup").show();
        $(".popup-overlay").show(); // Pokaż tylko tło, nie nakładaj go na całą stronę
    });

    $('#closeButton').click(function () {
        $('#popup').hide(); // Schowaj popup
        $(".popup-overlay").hide(); // Schowaj też tło
    });

    if ($('.info').length) {
        $('.info').delay(2000).fadeOut(); //fadeout informacji
    }

    function generateRandomCode() {
        // Generujemy losowe liczby od 1000 do 9999
        var randomCode = Math.floor(Math.random() * 9000) + 1000;
        return randomCode;
    }



    // Obsługa kliknięcia na link "start"
    $('.startLink').click(function (event) {

        var turniejId = $(this).data('turniejid'); // Pobieramy ID turnieju z atrybutu data

        // Wyświetlamy wartość ID turnieju w popupie
        $('#popup').html('<button id="closeButton" class="codeconfrim">Zamknij</button><br><p>Rozpocznij turniej: ' + turniejId +
            '<br><button id="generujKodBtn"  class="codeconfrim"> Generuj kod </button>' +
            '<input  type="number" id="kodTurnieju" class="codeconfrim" placeholder="Wprowadź czterocyfrowy kod turnieju">' +
            '<br><button id="zapiszKod" data-turniejid=' + turniejId + ' class="codeconfrim"> Zapisz kod i rozpocznij turniej</button>' +
            '</p>');

        $('#popup').show(); // Pokazujemy popup
    });

    $(document).on('click', '#zapiszKod', function () {
        var turniejId = $(this).data('turniejid');
        var kodTurnieju = $('#kodTurnieju').val(); // Pobieramy wartość z inputa
        console.log(turniejId);

        // Wysyłamy dane do serwera za pomocą AJAX
        $.ajax({
            type: 'POST',
            url: 'saveCode.php', // Adres pliku PHP obsługującego zapis kodu turnieju
            data: {
                turniejId: turniejId,
                kodTurnieju: kodTurnieju
            },
            success: function (response) {
                if (response === 'success') {
                    // Ukrywamy popup
                    $('#popup').hide();

                    // Przekierowujemy na stronę "joined.php"
                    window.location.href = 'joined.php';
                } else {
                    // Wyświetlamy komunikat o błędzie w ".info" div
                    $('#popup').html("<div class='info'>" + response + "</div>");
                    $('.info').delay(3000).fadeOut();
                }
            },
            error: function (xhr, status, error) {
                // Wyświetlamy komunikat o błędzie w ".info" div
                $('#popup').html("<div class='info'>" + response + "</div>");
                $('.info').delay(3000).fadeOut();
            }
        });
    });


    $(document).on('click', '#generujKodBtn', function () {
        var generatedCode = generateRandomCode();
        $('#kodTurnieju').val(generatedCode);
    });
});

function pokazPytanie(id) {
    var popup = $("#popup");
    $("#popup").show();
    popup.html('<button id="closeButton" class="codeconfrim">Zamknij</button><br>');
    // Wyświetl popup
    popup.append('<div class="loading-spinner"></div>');

    // Utwórz nowy obiekt XMLHttpRequest
    var xhr = new XMLHttpRequest();

    // Skonfiguruj zapytanie do serwera
    xhr.open("GET", "getQuest.php?id=" + id, true);

    // Ustaw callback, który zostanie wykonany po odebraniu odpowiedzi z serwera
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            // Odebrano odpowiedź, więc ustaw zawartość popupa na pobraną treść
            popup.html('<button id="closeButton" class="codeconfrim">Zamknij</button><br>');
            popup.append(xhr.responseText);

            // Show the close button

        }
    };

    // Wyślij zapytanie do serwera
    xhr.send();
}


// Funkcja do wyświetlania pozycji pytań w elementach o klasie "quest-options"
function wyswietlPozycje(data) {
    var questOptionsContainer = $('#questOptionsContainer'); // Znajdź kontener za pomocą jQuery

    if (data === void 0) {
        questOptionsContainer.append('<p></p>')
    }
    else {

        data.forEach(function (pozycja) {
            var div = $('<div></div>'); // Utwórz nowy div
            div.addClass('quest-option'); // Dodaj klasę "quest-option" do diva
            div.text(pozycja.Value); // Ustaw treść diva na wartość z JSON
            questOptionsContainer.append(div); // Dodaj div do kontenera
        });
    }
}
function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}


// Add click event for the close button using event delegation
$(document).on('click', '#closeButton', function () {
    $('#popup').hide(); //schowaj popup
});

