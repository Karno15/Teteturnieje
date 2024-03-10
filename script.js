$(document).ready(function () {

    langses = localStorage.getItem("lang");
    var lang = langses || 'en';

    localStorage.setItem("lang", lang);

    gearClicked = false;
    $('#gear').on('click', function () {
        if (!gearClicked) {
            $('.lang').css('display', 'flex');
            gearClicked = true;
        } else {
            $('.lang').fadeOut(200);
            $('.tooltiplang').fadeOut(200);
            gearClicked = false;
        }
    });

    var flagClick = false;
    $('.flag').css('background-image', 'url(' + getFlagUrl(lang) + ')');
    $('.flag').on('click', function () {
        flagClick = true;
        var newLang = (lang === 'pl') ? 'en' : 'pl';
        $('.flag').css('background-image', 'url(' + getFlagUrl(newLang) + ')');
        $('.lang-select').val(newLang);
        $('.lang-select').trigger('change');
    });
    $('.lang-select').on('change', function () {
        if (!flagClick) {
            lang = $(this).val();
            localStorage.setItem("lang", lang);
            $.post('setlang.php', {
                language: lang
            }, function (response) {
                console.log('Language changed to: ' + lang);
                location.reload();
            });
        }
        flagClick = false;
    });

    $('.lang').on('mouseenter', function () {
        var newLang = (lang === 'pl') ? 'en' : 'pl';
        $('.flag').fadeOut(100, function () {
            $(this).css('background-image', 'url(' + getFlagUrl(newLang) + ')').fadeIn(100);
        });
        $('.tooltiplang').fadeIn(200);
    });

    $('.lang').on('mouseleave', function () {
        $('.flag').fadeOut(100, function () {
            $(this).css('background-image', 'url(' + getFlagUrl(lang) + ')').fadeIn(100);
        });
        $('.tooltiplang').fadeOut(200);
    });

    function getFlagUrl(lang) {
        return (lang === 'en') ? 'images/en.svg' : 'images/pl.svg';
    }

    $('#cookie').on('mouseenter', function () {
        $('#cookieinfo').fadeIn(100);
    });
    
    $('#cookie').on('mouseleave', function () {
        $('#cookieinfo').fadeOut(100);
    });

    $('#cookieinfo').html(translations['cookieinfo'][lang]);
    $('#contact').html(translations['contact'][lang]);
    $('#pageInfoTitle').html(translations['pageInfoTitle'][lang]);
    $('#pageInfo').html(translations['pageInfo'][lang]);
    $('.tooltiplang').html(translations['tooltiplang'][lang]);
    $('button#login').html(translations['login'][lang]);
    $('button#register').html(translations['register'][lang]);

    $('#head span').click(function (event) {
        event.preventDefault();

        window.location.href = 'index.php';
    });

    $("#host").click(function () {
        window.location.href = "host.php";
    });

    loginHTML = '<button id="closeButton" class="codeconfrim">' + translations['return'][lang] + '</button><br>' +
        translations['log in'][lang].toUpperCase() + '<br><form action="login.php" method="post">' +
        'Login:<br><input type="text" name="login" class="inputlogin" maxlength="12" required><div id="definput">' +
        '<div id="definput">' + translations['password'][lang] + ':</br><input type="password" name="pass" class="inputlogin" required></div>' +
        '<button type="submit" class="codeconfrim">' + translations['log in'][lang] + '</button></form>';
    registerHTML = '<button id="closeButton" class="codeconfrim">' + translations['return'][lang] + '</button><br>' +
        translations['register'][lang].toUpperCase() + '<br><form  action="register.php" method="post">' +
        'Login:<br><input type="text" name="login" class="inputlogin" maxlength="12" required><div id="definput">' +
        '<div id="definput">' + translations['password'][lang] + ':</br><input type="password" name="pass" class="inputlogin" required></div>'
        + '<div class="disclaimer">' + translations['passPolicy'][lang] + '</div>' +
        '<button type="submit" class="codeconfrim">' + translations['register'][lang] + '</button></form>'
    $("#popup").html(loginHTML);

    $("#register").on("click", function () {
        $("#popup").show();
        $(".popup-overlay").show();
        $("#popup").html(registerHTML);
    });

    $("#login").on("click", function () {
        $("#popup").show();
        $(".popup-overlay").show();
        $("#popup").html(loginHTML);
    });

    $('#closeButton').click(function () {
        $('#popup').hide();
        $(".popup-overlay").hide();
    });

    if ($('.info').length) {
        $('.info').delay(2000).fadeOut();
    }

    function generateRandomCode() {
        var randomCode = Math.floor(Math.random() * 9000) + 1000;
        return randomCode;
    }

    $('.startLink').click(function (event) {

        var turniejId = $(this).data('turniejid');

        $('#popup').html('<button id="closeButton" class="codeconfrim">' + translations['close'][lang] + '</button><br><p>' + translations['startTurniej'][lang]
            + ': ' + turniejId + '<br><button id="generujKodBtn"  class="codeconfrim">' + translations['generateCode'][lang] + '</button>' +
            '<input  type="number" id="kodTurnieju" class="codeconfrim" placeholder="' + translations['startPlaceholder'][lang] + '" min="1000" max="9999">' +
            '<br><button id="zapiszKod" data-turniejid=' + turniejId + ' class="codeconfrim">' + translations['startButton'][lang] + '</button>' + '</p>');
        $('.popup-overlay').show();
        $('#popup').show();
    });

    $(document).on('click', '#zapiszKod', function () {
        var turniejId = $(this).data('turniejid');
        var kodTurnieju = $('#kodTurnieju').val();

        $.ajax({
            type: 'POST',
            url: 'saveCode.php',
            data: {
                turniejId: turniejId,
                kodTurnieju: kodTurnieju
            },
            success: function (response) {
                if (response === 'success') {
                    $('#popup').hide();
                    $(".popup-overlay").hide();
                    window.location.href = 'joined.php';
                } else {
                    $('body').append("<div class='info'>" + response + "</div>");
                    $("#popup").hide();
                    $(".popup-overlay").hide();
                    $('.info').delay(3000).fadeOut();
                }
            },
            error: function (xhr, status, error) {
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
    popup.append('<div class="loading-spinner"></div>');

    var xhr = new XMLHttpRequest();

    xhr.open("GET", "getQuest.php?id=" + id, true);

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            popup.html('<button id="closeButton" class="codeconfrim">' + translations['close'][lang] + '</button><br>');
            popup.append(xhr.responseText);
        }
    };
    xhr.send();
}

$(document).on('click', '#closeButton', function () {
    $('#popup').hide();
    $('.popup-overlay').hide();
});

function formatDuration(duration) {
    var totalSeconds = Math.floor(duration / 1000);
    var seconds = totalSeconds % 60;
    var minutes = Math.floor(totalSeconds / 60);
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

function createTextNode(value) {
    console.log(value)
    var textNode = document.createTextNode(value);
    var div = document.createElement('div');
    div.appendChild(textNode);
    console.log(div.innerHTML)
    return div.innerHTML;
}

function decodeEntities(encodedString) {
    var textarea = document.createElement('textarea');
    textarea.innerHTML = encodedString;
    return textarea.value;
}
