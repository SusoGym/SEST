<?php

//$model = Model::getInstance();
$data = $this->getDataForView();
$user = $data['user'];
include("header.php");

?>

<div class="container">

    <div class="card">
        <div class="card-content">
             <span class="card-title">
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
						 <i class="material-icons">chevron_left</i>
					</a>
                 Nutzerdaten aktualisieren:
				</span>
            <?php /** @var \User $usr */
            $usr = $data['user']; ?>
            <form onsubmit="submitForm()" action="javascript:void(0);" autocomplete="off">
                <div class="row">
                    <div class="input-field col s4 l4 m4">
                        <label for="f_name">Name:</label>
                        <input name="f_name" id="f_name" type="text" value="<?php echo $usr->getName(); ?>"
                               required="required" class="validate">
                    </div>
                    <div class="input-field col s4 l4 m4">
                        <label for="f_surname">Nachname:</label>
                        <input name="f_surname" id="f_surname" type="text" value="<?php echo $usr->getSurname(); ?>"
                               required="required" class="validate">
                    </div>
                    <div class="input-field col s4 l4 m4">
                        <label for="f_email">Email:</label>
                        <input name="f_email" id="f_email" type="email" value="<?php echo $usr->getEmail(); ?>"
                               required="required" class="validate">
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s6 l6 m6">
                        <label for="f_pwd">Neues Passwort:</label>
                        <input name="f_pwd" id="f_pwd" type="password">
                    </div>
                    <div class="input-field col s6 l6 m6">
                        <label for="f_pwd_repeat">Neues Passwort wiederholen:</label>
                        <input name="f_pwd_repeat" id="f_pwd_repeat" type="password">
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s4 l4 m4">
                        <label for="f_pwd_old">Altes Passwort:</label>
                        <input name="f_pwd_old" id="f_pwd_old" type="password" required="required" class="validate">
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s2 l2 m2 offset-s6 offset-l6 offset-m6">
                        <button class="btn waves-effect waves-light" type="submit">Update
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-action center">
        &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
    </div>
</div>

</div>

<?php include("js.php"); ?>

<script type="application/javascript">
    function submitForm() {
        var name = $('#f_name');
        var surname = $('#f_surname');
        var email = $('#f_email');
        var pwd = $('#f_pwd');
        var pwd_rep = $('#f_pwd_repeat');
        var old_pwd = $('#f_pwd_old');
        var pwdV = pwd.val();
        var pwd_repV = pwd_rep.val();
        var old_pwdV = old_pwd.val()
        if (old_pwdV == "") {
            Materialize.toast("Bitte geben sie ihr altes Passwort an!");
            return;
        }
        if ((pwdV != "" || pwd_repV != "") && pwdV != pwd_repV) {
            Materialize.toast("Die eingegebenen Passwörter stimmen nicht überein!");
            pwd.val("");
            pwd_rep.val("");
            return;
        }

        $.post("", {
            'type': 'parent_editdata',
            'console': '',
            'data[pwd]': pwd.val(),
            'data[mail]': email.val(),
            'data[name]': name.val(),
            'data[surname]': surname.val(),
            'data[oldpwd]': old_pwd.val()


        }, function (data) {

            try {
                if (data.success) {
                    location.reload();
                }
                else { // oh no! ;-;
                    var notifications = data['notifications'];
                    notifications.forEach(function (data) {
                        Materialize.toast(data, 4000);
                    });

                    if ("resetold" in data) {
                        old_pwd.val("");
                    }
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
