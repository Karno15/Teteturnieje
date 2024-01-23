$(document).ready(function () {


    langses = localStorage.getItem("lang");
    var lang = langses || 'en';
    
    var flagClick = false;
    $('.flag').css('background-image', 'url(' + getFlagUrl(lang) + ')');
    $('.flag').on('click', function() {
        flagClick = true;
        var currentLang = $('.lang-select').val();
        var newLang = (currentLang === 'pl') ? 'en' : 'pl';
        $('.flag').css('background-image', 'url(' + getFlagUrl(newLang) + ')');
        $('.lang-select').val(newLang);
        $('.lang-select').trigger('change');
    });
    $('.lang-select').on('change', function() {
        if (!flagClick) {
            let lang = $(this).val();
            localStorage.setItem("lang", lang);
            $.post('setlang.php', {
                language: lang
            }, function(response) {
                console.log('Language changed to: ' + lang);
                location.reload();
            });
        }
        flagClick = false;
    });
    function getFlagUrl(lang) {
        return (lang === 'en') ? 'images/en.svg' : 'images/pl.svg';
    }

    $('#contact').html(translations['contact'][lang]);  
    $('#pageInfo').html(translations['pageInfo'][lang]); 
    $('button#login').html(translations['login'][lang]);
    $('button#register').html(translations['register'][lang]);
    //console.log(getCookie('lang'));

    $('#head span').click(function (event) {
        event.preventDefault(); // Prevent the default link behavior

        // Redirect to the main index.php page
        window.location.href = 'index.php';
    });

    $("#host").click(function () {
        window.location.href = "host.php";
    });

    loginHTML = '<button id="closeButton" class="codeconfrim">' + translations['return'][lang] + '</button><br>' +
        translations['login'][lang].toUpperCase() + '<br><form action="login.php" method="post">' +
        'Login:<br><input type="text" name="login" class="inputlogin" maxlength="12" required><div id="definput">' +
        '<div id="definput">' + translations['password'][lang] + ':</br><input type="password" name="pass" class="inputlogin" required></div>' +
        '<button type="submit" class="codeconfrim">' + translations['log in'][lang] + '</button></form>';
    registerHTML = '<button id="closeButton" class="codeconfrim">' + translations['return'][lang] + '</button><br>' +
        translations['register'][lang].toUpperCase() + '<br><form  action="register.php" method="post">' +
        'Login:<br><input type="text" name="login" class="inputlogin" maxlength="12" required><div id="definput">' +
        '<div id="definput">' + translations['password'][lang] + ':</br><input type="password" name="pass" class="inputlogin" required></div>' +
        '<button type="submit" class="codeconfrim">' + translations['register'][lang] + '</button></form>'
    $("#popup").html(loginHTML);

    $("#register").on("click", function () {
        $("#popup").show();
        $(".popup-overlay").show(); // Pokaż tylko tło, nie nakładaj go na całą stronę
        $("#popup").html(registerHTML);
    });

    $("#login").on("click", function () {
        $("#popup").show();
        $(".popup-overlay").show(); // Pokaż tylko tło, nie nakładaj go na całą stronę
        $("#popup").html(loginHTML);
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
        $('#popup').html('<button id="closeButton" class="codeconfrim">' + translations['close'][lang] + '</button><br><p>' + translations['startTurniej'][lang]
            + ': ' + turniejId + '<br><button id="generujKodBtn"  class="codeconfrim">' + translations['generateCode'][lang] + '</button>' +
            '<input  type="number" id="kodTurnieju" class="codeconfrim" placeholder="' + translations['startPlaceholder'][lang] + '" min="1000" max="9999">' +
            '<br><button id="zapiszKod" data-turniejid=' + turniejId + ' class="codeconfrim">' + translations['startButton'][lang] + '</button>' + '</p>');
        $('.popup-overlay').show();
        $('#popup').show(); // Pokazujemy popup
    });

    $(document).on('click', '#zapiszKod', function () {
        var turniejId = $(this).data('turniejid');
        var kodTurnieju = $('#kodTurnieju').val(); // Pobieramy wartość z inputa

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
                    $(".popup-overlay").hide();
                    window.location.href = 'joined.php';
                } else {
                    // Wyświetlamy komunikat o błędzie w ".info" div
                    $('body').append("<div class='info'>" + response + "</div>");
                    $("#popup").hide();
                    $(".popup-overlay").hide();
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


    $(document).on('mouseover', '.category', function () {
        $(this).addClass('category-over');
    });

    $(document).on('mouseout', '.category-over', function () {
        $(this).removeClass('category-over').addClass('category');
    });

});

function pokazPytanie(id) {
    var popup = $("#popup");
    $('.popup-overlay').show();
    $("#popup").show();
    popup.html('<button id="closeButton" class="codeconfrim">' + translations['close'][lang] + '</button><br>');
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
            popup.html('<button id="closeButton" class="codeconfrim">' + translations['close'][lang] + '</button><br>');
            popup.append(xhr.responseText);

            // Show the close button

        }
    };

    // Wyślij zapytanie do serwera
    xhr.send();
}


function sleep(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
}


// Add click event for the close button using event delegation
$(document).on('click', '#closeButton', function () {
    $('#popup').hide(); //schowaj popup
    $('.popup-overlay').hide();
});

function formatDuration(duration) {
    var totalSeconds = Math.floor(duration / 1000); // Convert milliseconds to seconds
    var seconds = totalSeconds % 60;
    var minutes = Math.floor(totalSeconds / 60); // Convert remaining seconds to minutes
    var milliseconds = duration % 1000;

    return '+' + (minutes * 60 + seconds) + 's ' + milliseconds + 'ms';
}


function answerPoints(login, pts, answer, turniejId) {
    $.ajax({
        url: 'answer.php',
        type: 'POST',
        data: {
            login: login,
            pts: pts,
            answer: answer,
            turniejId: turniejId
        },
        success: function (response) {
            //   checkTournamentStatus();
        },
        error: function (error) {
            console.error('Error changing points:', error);
        }
    });
}

function getCookie(name) {
    var dc = document.cookie;
    var prefix = name + "=";
    var begin = dc.indexOf("; " + prefix);
    if (begin == -1) {
        begin = dc.indexOf(prefix);
        if (begin != 0) return null;
    }
    else {
        begin += 2;
        var end = document.cookie.indexOf(";", begin);
        if (end == -1) {
            end = dc.length;
        }
    }

    return decodeURI(dc.substring(begin + prefix.length, end));
}

function getUrlParameter(name) {
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(window.location.href);
    if (!results) return null;
    if (!results[2]) return '';
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}