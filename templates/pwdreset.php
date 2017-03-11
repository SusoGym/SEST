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
  <div class="col s12 m8 l4 offset-m2 offset-l4 card" style="margin-top: 100px;">
    <div class="card-content">
      <span class="card-title sblue-text">Passwort zurücksetzen</span>
      <form onsubmit="newpwd();" action="javascript:void(0);">
        <div class="input-field">
          <i class="material-icons prefix">vpn_key</i>
          <input id="pwd" type="password" name="pwd" required autocomplete="off">
          <label for="pwd">Neues Password</label>
        </div>
        <div class="input-field">
          <i class="material-icons prefix">refresh</i>
          <input id="pwdrep" type="password" name="pwdrep" required autocomplete="off">
          <label for="pwdrep">Passwort wiederholen</label>
        </div>
        <input type="submit" style="display:none;"/>
        <a onclick="newpwd();" class="waves-effect waves-light btn sblue white-text right" style="margin-bottom: 20px;"><i class="material-icons right">send</i>RESET</a>
      </form>
    </div>
  </div>
</div>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<script>
  function newpwd() {
    var pwd = $('#pwd').val();
    var pwdrep = $('#pwdrep').val();
    if (pwd == pwdrep) {
      $.post("", {'type': 'pwdreset', 'console': '', 'pwdreset[pwd]': pwd}, function(data){
        if (data.success == true) {
          location.reload();
        } else {
          Materialize.toast('Ein Fehler ist aufgetreten: '+data.message);
        }
      });
    } else {
      Materialize.toast("Die Passwörter stimmen nicht überein!", 4098);
      $('#pwdrep').val('');
      $('#pwdrep').focus();
    }
  }
</script>
</body>
</html>
