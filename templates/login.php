<!DOCTYPE html>
<html lang="de">
  <head>
    <title>ESPT - Login</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"  media="screen,projection"/>
  <body class="container teal">
    <div class="row">
      <div class="col s12 m8 l4 offset-m2 offset-l4" style="margin-top: 100px;">


        <ul class="collapsible white" data-collapsible="accordion">
          <li>
            <div class="collapsible-header active"><i class="material-icons">filter_drama</i>Anmelden</div>
            <div class="collapsible-body" style="padding: 20px;">
              <form method="post" autocomplete="off">
                  <input type="hidden" name="type" value="login">
                <div class="input-field">
                  <i class="material-icons prefix">person</i>
                  <input id="usr" name="login[user]" type="text" required>
                  <label for="usr">Benutzername</label>
                </div>
                <div class="input-field ">
                  <i class="material-icons prefix">vpn_key</i>
                  <input id="pwd" name="login[password]" type="password" required>
                  <label for="pwd">Passwort</label>
                </div>
                      <button class="btn waves-effect waves-light" type="submit" name="action">Submit<i class="material-icons right">send</i>
              </form>
            </div>
          </li>
          <li>
            <div class="collapsible-header"><i class="material-icons">place</i>Registrieren</div>
            <div class="collapsible-body" style="padding: 20px;">
              <form method="post" action="login.php" autocomplete="off">
                <div class="input-field">
                  <i class="material-icons prefix">person</i>
                  <input id="name" name="name" type="text" required>
                  <label for="name">Name</label>
                </div>
                <div class="input-field">
                  <i class="material-icons prefix">face</i>
                  <input id="student" name="student" type="text" required>
                  <label for="student">Sch&uuml;ler</label>
                </div>
                <div class="input-field">
                  <i class="material-icons prefix">mail</i>
                  <input id="mail" name="mail" type="text" required>
                  <label for="mail">Email</label>
                </div>
                <div class="input-field ">
                  <i class="material-icons prefix">vpn_key</i>
                  <input id="pwd" name="pwd" type="password" required>
                  <label for="pwd">Passwort</label>
                </div>
                <div class="input-field ">
                  <i class="material-icons prefix">cached</i>
                  <input id="pwdrep" name="pwdrep" type="password" required>
                  <label for="pwdrep">Passwort wiederholen</label>
                </div>
              </form>
            </div>
          </li>
        </ul>

      </div>
    </div>

    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="http://materializecss.com/bin/materialize.js"></script>
    <script>
      <?php
          if(isset($data['notifications']))
            foreach ($data['notifications'] as $not) {
                   echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
            }
      ?>
    </script>
  </body>

</html>
