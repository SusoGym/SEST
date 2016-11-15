<!DOCTYPE html>
<html lang="de">
<head>
    <title>Suso-Elternsprechtag</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"
          media="screen,projection"/>
<body class="container teal">
<div class="row">
    <div class="col s12 m8 l4 offset-m2 offset-l4" style="margin-top: 100px;">


        <ul class="collapsible white" data-collapsible="accordion">
            <li>
                <div class="collapsible-header active"><i class="material-icons">person</i>Anmelden</div>
                <div class="collapsible-body" style="padding: 20px;">
                    <form autocomplete="off" onsubmit="submitLogin()" action="javascript:void(0);">
                        <div class="input-field">
                            <i class="material-icons prefix">person</i>
                            <input id="usr_login" type="email" class="validate" required>
                            <label for="usr_login">Email-Addresse</label>
                        </div>
                        <div class="input-field ">
                            <i class="material-icons prefix">vpn_key</i>
                            <input id="pwd_login" type="password" required>
                            <label for="pwd_login">Passwort</label>
                        </div>
                        <div class="row" style="margin-bottom: 0;">
                            <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i
                                    class="material-icons right">send</i></button>
                        </div>
                    </form>
                </div>
            </li>
            <li>
                <div class="collapsible-header"><i class="material-icons">person_add</i>Registrieren</div>
                <div class="collapsible-body" style="padding: 20px;">
                    <form method="post" onsubmit="submitRegister()" action="javascript:void(0);" autocomplete="off">
                        <div class="input-field">
                            <i class="material-icons prefix">mail</i>
                            <input id="mail_register" name="mail" type="email" required="required" class="validate">
                            <label for="mail_register">Email-Addresse</label>
                        </div>
                        <div class="input-field ">
                            <i class="material-icons prefix">vpn_key</i>
                            <input id="pwd_register" name="pwd" type="password" required>
                            <label for="pwd_register">Passwort</label>
                        </div>
                        <div class="input-field ">
                            <i class="material-icons prefix">cached</i>
                            <input id="pwdrep_register" name="pwdrep" type="password" required>
                            <label for="pwdrep_register">Passwort wiederholen</label>
                        </div>
                        <a class="btn-flat teal-text" style="margin-bottom: 10px;" onclick="addStudent();"
                           id="moreFields"><i class="material-icons left">add</i>Schüler hinzufügen</a>
                        <span id="students"></span>
                        <div class="row" style="margin-bottom: 0;">
                            <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i
                                    class="material-icons right">send</i></button>
                        </div>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</div>
<div id="student_blueprint" class="row" style="display: none;">
    <input id="student" class="col s6 autocomplete name" name="student" type="text" placeholder="Name">
    <input type="date" class="datepicker col s6 bday" id="bday" name="bday" placeholder="Geburtsdatum">
    <!-- id="bday" class="col s6" name="bday" type="date" class="bday" -->
</div>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script type="text/javascript" src="http://materializecss.com/bin/materialize.js"></script>
<script>
    <?php
    if (isset($data['notifications']))
        foreach ($data['notifications'] as $not) {
            echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
        }

    ?>
    function initDatepick() {

        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: 20,
            max: new Date(),
            format: "dd.mm.yyyy",

            labelMonthNext: 'Nächster Monat',
            labelMonthPrev: 'Vorheriger Monat',
            labelMonthSelect: 'Monat wählen',
            labelYearSelect: 'Jahr wählen',
            monthsFull: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthsShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
            weekdaysFull: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            weekdaysShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            weekdaysLetter: ['S', 'M', 'D', 'M', 'D', 'F', 'S'],
            today: 'Heute',
            clear: 'Löschen',
            close: 'Ok',
            firstDay: 1

        });

    }


    var counter = 0;

    function addStudent() {
        counter++;
        if (counter <= 100) {
            var clonedNode = document.getElementById('student_blueprint').cloneNode(true);
            clonedNode.id = ''; // reset id name of clone
            clonedNode.style.display = 'block'; // remove display: none; from clone
            clonedNode.className = 'student_instance';
            var childNodes = clonedNode.childNodes;
            childNodes.forEach(function (childNode) {
                var nodeName = childNode.name;
                if (nodeName)
                    childNode.name = nodeName + "[" + counter + "]";
            });
            var insertHere = document.getElementById('students');
            insertHere.parentNode.insertBefore(clonedNode, insertHere);
        }

        initDatepick();
    }

    $(document).ready(function () {
        addStudent(); // -> create one default student field

    });

    function submitLogin() {
        var pwd = $('#pwd_login').val();
        var usr = $('#usr_login').val();
        var url = "index.php?console&type=login&login[password]=" + pwd + "&login[mail]=" + usr;


        $.get(url, function (data) {
            if (data == "true") {
                location.reload();
            } else if(data == "false"){
                Materialize.toast("Email-Addresse oder Passwort falsch", 4000);
                $('#pwd_login').val("");

                $('label[for="pwd_login"]').removeClass("active");
            } else {
                Materialize.toast("Unexpected response: " + data, 4000);
                $('#pwd_login').val("");

                $('label[for="pwd_login"]').removeClass("active");
            }
        });

        return false;
    }

    function submitRegister() {
        // register[ usr, mail, pwd, student[ [name, bday], [name, bday], ...]
        var url_param = "?console&type=register";

        //TODO: check inputs for correct syntax etc.
        var mail = $('#mail_register').val();
        var pwd = $('#pwd_register');
        var pwdrep = $('#pwdrep_register');

        if (pwd.val() != pwdrep.val()) {
            pwd.val("");
            pwdrep.val("");
            Materialize.toast("Die eingegebenen Passwörter stimmen nicht überein!", 4000);

            return;
        }


        url_param += "&register[mail]=" + mail + "&register[pwd]=" + pwd;

        var num_validStudents = 0;

        var studentNotes = document.getElementsByClassName('student_instance');

        for (var i = 0; i < studentNotes.length; i++) {
            var student = studentNotes[i];
            var name = student.childNodes[1].value;
            var bday = student.childNodes[3].value;  // magic numbers op!

            if (name == "" || bday == "")
                continue;

            num_validStudents++;
            url_param += "&register[student][]=" + name + ":" + bday;

        }

        if (num_validStudents == 0) {// No valid Students...
            Materialize.toast("Bitte geben sie mindestens einen Schüler an.");
            return;
        }

        // give request to backend and utilize response
        $.get("index.php" + url_param, function (data) {

            try {
                var myData = JSON.parse(data);
                if (myData.success) {
                    location.reload();
                }
                else { // oh no! ;-;
                    var notifications = myData['notifications'];
                    notifications.forEach(function (data) {
                        Materialize.toast(data, 4000);
                    });
                }
            } catch (e) {
                Materialize.toast('Interner Server Fehler!');
                console.error(e);
                console.info('Request: ' + url_param);
                console.info('Response: ' + data);
            }

        });
    }

</script>
</body>

</html>
