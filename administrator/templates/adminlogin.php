<?php namespace administrator; ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <title>Suso-Intern-Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"
          media="screen,projection"/>
<body class="container teal">
<div class="row">
    <div class="col s12 m8 l4 offset-m2 offset-l4" style="margin-top: 100px;">


        <ul class="collapsible white " data-collapsible="accordion">
            <li>
                <div class="collapsible-header active"><i class="material-icons">person</i>Administrator Login</div>
                <div class="collapsible-body" style="padding: 20px;">
                    <form autocomplete="off" onsubmit="submitLogin()" action="javascript:void(0);">
                        <div class="input-field">
                            <i class="material-icons prefix">person</i>
                            <input id="mail_login" type="email" class="validate" required>
                            <label for="mail_login">Email-Addresse</label>
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

        </ul>
    </div>
</div>

<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script type="text/javascript" src="http://materializecss.com/bin/materialize.js"></script>
<script>

    <?php
    $data = \View::getInstance()->getDataForView();
    if (isset($data['notifications']))
        foreach ($data['notifications'] as $not) {
            echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
        }

    ?>


    function submitLogin() {
        var pwd = $('#pwd_login');
        var mail = $('#mail_login');
        var url = "?console&type=login&login[password]=" + pwd.val() + "&login[mail]=" + mail.val();
        console.info(url);

        $.get(url, function (data) {
            if (data == 1) {
                location.reload();
            } else if (data == 0) {
                Materialize.toast("Email-Addresse oder Passwort falsch", 4000);
                $('label[for="pwd_login"').removeClass("active");
                pwd.val("");
            } else {
                Materialize.toast("Dieser Benutzer besitzt nicht die Berechtigung für diesen Bereich. ", 4000);
                pwd.val("");
                mail.val("");
                $('label').removeClass("active");
                $('input').removeClass("valid")
            }
        });

        return false;
    }

</script>
</body>

</html>
