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
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" 
					href="<?php echo $data["backButton"]; ?>">
						 <i class="material-icons">chevron_left</i>
					</a>
                 Passwort ändern für <?php echo $user->getEmail(); ?>:
				</span>
            <?php /** @var \User $usr */
            $usr = $data['user']; ?>
            <form onsubmit="submitForm()" action="javascript:void(0);" autocomplete="off">
                
				<div class="row">
					<div class="input-field col s12 l6 m6">
                        <label for="f_pwd_old">altes Passwort:</label>
                        <input name="f_pwd_old" id="f_pwd_old" type="password" required="required" class="validate">
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s12 s12 l6 m6">
                        <label for="f_pwd">Neues Passwort:</label>
                        <input name="f_pwd" id="f_pwd" type="password">
                    </div>
				</div>
				<div class="row">
                    <div class="input-field col s12 s12 l6 m6">
                        <label for="f_pwd_repeat">Neues Passwort wiederholen:</label>
                        <input name="f_pwd_repeat" id="f_pwd_repeat" type="password">
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
    
</div>

</div>

<?php include("js.php"); ?>

<script type="application/javascript">
    function submitForm() {
        var pwd = $('#f_pwd');
        var pwd_rep = $('#f_pwd_repeat');
        var old_pwd = $('#f_pwd_old');
        var pwdV = pwd.val();
        var pwd_repV = pwd_rep.val();
        var old_pwdV = old_pwd.val();
        if (old_pwdV == "") {
            Materialize.toast("Bitte geben sie ihr altes Passwort an!",4000);
            return;
        }
        if ((pwdV != "" || pwd_repV != "") && pwdV != pwd_repV) {
            Materialize.toast("Die eingegebenen Passwörter stimmen nicht überein!",4000);
            pwd.val("");
            pwd_rep.val("");
            return;
        }

        $.post("", {
            'type': 'admpwd',
            'console': 'true',
            'data[pwd]': pwd.val(),
            'data[oldpwd]': old_pwd.val()
        }, function (data) {
			jdata = JSON.parse(data);
            try {
                if (jdata['success']) {
                    old_pwd.val("");
					pwd.val("");
					pwd_rep.val("");
					Materialize.toast(jdata['notifications'], 4000);
                }
                else { // oh no! ;-;
                    var notifications = jdata['notifications'];
                    notifications.forEach(function (jdata) {
                        Materialize.toast(jdata, 4000);
                    });

                    if ("resetold" in jdata) {
                        old_pwd.val("");
                    }
                }
            } catch (e) {
                Materialize.toast('Interner Server Fehler!',4000);
                console.info('Response: ' + data);
            }

        });

    }
</script>

</body>
</html>
