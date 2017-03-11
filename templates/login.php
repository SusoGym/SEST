<?php $data = $this->getDataForView(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Suso-Gymnasium</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
<body class="container teal">
<div class="row">
    <div class="col s12 m8 l4 offset-m2 offset-l4" style="margin-top: 100px;">


        <ul class="collapsible white" data-collapsible="accordion">
            <li>
                <div class="collapsible-header active"><i class="material-icons">person</i>Anmelden</div>
                <div class="collapsible-body">
                    <form autocomplete="off" onsubmit="submitLogin()" action="javascript:void(0);" class="row"
                          style="margin: 20px;">
                        <div class="input-field col s12">
                            <i class="material-icons prefix">person</i>
                            <input id="usr_login" type="text" class="" required>
                            <label for="usr_login" class="truncate">Email-Adresse</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">vpn_key</i>
                            <input id="pwd_login" type="password" style="margin-bottom:0px;" required>
                            <label for="pwd_login" class="truncate">Passwort</label>
                            <span class="right" style="margin-bottom:20px;" >
                              <a href="#forgot" class="teal-text text-darken-2">Passwort vergessen?</a>
                            </span>
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
                    <form method="post" onsubmit="submitRegister()" action="javascript:void(0);" autocomplete="off"
                          class="row" style="margin: 20px;">
                        <div class="input-field col s6">
                            <i class="material-icons prefix">account_circle</i>
                            <input id="name_register" name="name" type="text" required>
                            <label for="name_register" class="truncate">Vorname</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="surname_register" name="surname" type="text" required>
                            <label for="surname_register" class="truncate">Nachname</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">mail</i>
                            <input id="mail_register" name="mail" type="email" required="required" class="validate">
                            <label for="mail_register" class="truncate">Email-Adresse</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">vpn_key</i>
                            <input id="pwd_register" name="pwd" type="password" required>
                            <label for="pwd_register" class="truncate">Passwort</label>
                        </div>
                        <div class="input-field col s12">
                            <i class="material-icons prefix">cached</i>
                            <input id="pwdrep_register" name="pwdrep" type="password" required>
                            <label for="pwdrep_register" class="truncate">Passwort wiederholen</label>
                        </div>
                        <div class="row" style="margin-bottom: 0;">
                            <button class="btn-flat right waves-effect waves-teal" id="btn_register" type="submit">
                                Submit<i
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

<div id="forgot" class="modal">
  <form submit="javascript:void(0);" onsubmit="forgot()" >
    <div class="modal-content">
      <h4>Passwort vergessen?</h4>
      <div class="forgotform input-field">
        <input id="mail_forgot" type="email" class="validate">
        <label for="mail_forgot">Email</label>
      </div>
      <p id="forgottext" class="center" style="display:none;"></p>
    </div>
    <div class="modal-footer forgotform">
        <button type="submit" class="modal-action waves-effect waves-teal btn-flat">Zurücksetzen<i class="material-icons right">send</i></button>
    </div>
  </form>
</div>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<script>

    $('.modal').modal();
    <?php
    if (isset($data['notifications']))
        foreach ($data['notifications'] as $not) {
            echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
            echo "console.info('Toast: " . $not['msg'] . "');";
        }

    ?>


    function forgot() {
      var mail = $('#mail_forgot').val();
      $.post("", {'type': 'pwdreset', 'console': '', 'pwdreset[mail]': mail}, function (data) {
        if (data.success == true) {
          $('.forgotform').hide();
          $('p#forgottext').show();
          $('p#forgottext').html('<i class="material-icons left teal-text">check</i>Bitte rufen Sie Ihre E-Mails ab, um ihr Passwort zurückzusetzen.');
        } else {
          $('p#forgottext').show();
          $('p#forgottext').html('<i class="material-icons left red-text">clear</i>Etwas stimmt nicht: '+data.message);
        }
      });

    }

    function submitLogin() {
        var pwd = $('#pwd_login').val();
        var usr = $('#usr_login').val();

        $.post("", {'type': 'login', 'console': '', 'login[password]': pwd, 'login[mail]': usr}, function (data) {
            if (data == "true") {
                location.reload();
            } else if (data == "false") {
                Materialize.toast("Email-Addresse oder Passwort falsch", 4000);
                $('#pwd_login').val("");

                $('label[for="pwd_login"]').removeClass("active");
            } else {
                Materialize.toast("Unexpected response: " + data, 4000);
                console.info("Unexcpected response: " + data);
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
        $.post("", {
            'type': 'register',
            'console': '',
            'register[pwd]': pwd.val(),
            'register[mail]': mail,
            'register[name]': nameVal,
            'register[surname]': surnameVal
        }, function (data) {

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
