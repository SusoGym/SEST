<?phpecho "Loginseite";
?>
<!DOCTYPE html>
<html lang="de">
  <head>
    <title>Suso-Intern-Admin</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"  media="screen,projection"/>
  <body class="container teal">
    <div class="row">
      <div class="col s12 m8 l4 offset-m2 offset-l4" style="margin-top: 100px;">


        <ul class="collapsible white" data-collapsible="accordion">
          <li>
            <div class="collapsible-header active"><i class="material-icons">person</i>admin anmelden</div>
            <div class="collapsible-body" style="padding: 20px;">
              <form autocomplete="off" onsubmit="submitLogin()" action="javascript:void(0);">
                <div class="input-field">
                  <i class="material-icons prefix">person</i>
                  <input id="usr_login" type="text" required <?php if(isset($_SESSION['failed_login']['name'])){echo 'value="' . $_SESSION['failed_login']['name'] . '"';}?>>
                  <label for="usr">Benutzername</label>
                </div>
                <div class="input-field ">
                  <i class="material-icons prefix">vpn_key</i>
                  <input id="pwd_login" type="password" required>
                  <label for="pwd">Passwort</label>
                </div>
                <div class="row" style="margin-bottom: 0px;">
                    <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i class="material-icons right">send</i></button>
                </div>
              </form>
            </div>
          </li>
         
        </ul>
      </div>
    </div>
    <div id="read" class="row" style="display: none;">
      <input id="student" class="col s6" name="student" type="text" class="autocomplete" placeholder="Name">
      <input id="bday" class="col s6" name="bday" type="text" placeholder="tt.mm.yyyy">
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

      var counter = 0;

      function moreFields() {
      	counter++;
        if (counter <= 100) {
        	var newFields = document.getElementById('read').cloneNode(true);
        	newFields.id = '';
        	newFields.style.display = 'block';
          newFields.required;
        	var newField = newFields.childNodes;
        	for (var i=0;i<newField.length;i++) {
        		var theName = newField[i].name
        		if (theName)
        			newField[i].name = theName + "[" + counter + "]";
        	}
        	var insertHere = document.getElementById('write');
        	insertHere.parentNode.insertBefore(newFields,insertHere);
        }
      }

      function submitLogin()
      {
          var pwd = $('#pwd_login').val();
          var usr = $('#usr_login').val();
          var url = "?console&type=login&login[password]=" + pwd + "&login[user]=" + usr;
          console.info(url);

          $.get( "index.php?console&type=login&login[password]=" + pwd + "&login[user]=" + usr, function (data) {

              if(data === "true")
              {
                  location.reload();
              } else {
                  Materialize.toast("Benutzername oder Passwort falsch", 4000);
                  $('#pwd_login').val("");
              }
          });

          return false;
      }

    </script>
  </body>

</html>
