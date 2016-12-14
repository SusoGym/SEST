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
                <div class="collapsible-body">
                    <form autocomplete="off" onsubmit="submitLogin()" action="javascript:void(0);" class="row" style="margin: 20px;">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">person</i>
                            <input id="usr_login" type="email" class="validate" required>
                            <label for="usr_login">Email-Addresse</label>
                        </div>
                        <div class="input-field col s12">
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
                <div class="collapsible-body">
                    <form method="post" onsubmit="submitRegister()" action="javascript:void(0);" autocomplete="off" class="row" style="margin: 20px;">
                      <div class="input-field col s6">
                          <i class="material-icons prefix">account_circle</i>
                          <input id="name_register" name="name" type="text" required>
                          <label for="name_register">Vorname</label>
                      </div>
                      <div class="input-field col s6">
                          <input id="surname_register" name="surname" type="text" required>
                          <label for="surname_register">Nachname</label>
                      </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">mail</i>
                            <input id="mail_register" name="mail" type="email" required="required" class="validate">
                            <label for="mail_register">Email-Addresse</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">vpn_key</i>
                            <input id="pwd_register" name="pwd" type="password" required>
                            <label for="pwd_register">Passwort</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">cached</i>
                            <input id="pwdrep_register" name="pwdrep" type="password" required>
                            <label for="pwdrep_register">Passwort wiederholen</label>
                        </div>
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
        foreach ($data['notifications'] as $not)
        {
            echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
        }

    ?>


    function submitLogin() {
        var pwd = $('#pwd_login').val();
        var usr = $('#usr_login').val();
        var url = "index.php?console&type=login&login[password]=" + pwd + "&login[mail]=" + usr;


        $.get(url, function (data) {
            if (data == "true") {
                location.reload();
            } else if (data == "false") {
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

        var mail = $('#mail_register').val();
        var pwd = $('#pwd_register');
        var pwdrep = $('#pwdrep_register');
        var nameVal = $('#name_register').val();
        var surnameVal = $('#surname_register').val();

        if (pwd.val() != pwdrep.val()) {
            pwd.val("");
            pwdrep.val("");
            Materialize.toast("Die eingegebenen Passwörter stimmen nicht überein!", 4000);

            return;
        }

        url_param += "&register[mail]=" + mail + "&register[pwd]=" + pwd.val() + "&register[name]=" + nameVal + "&register[surname]=" + surnameVal;

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
