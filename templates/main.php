<?php
$model = Model::getInstance();
//$tchrs = $model->classGetTeachers($model->studentGetClass(/*$_SESSION['user']['id']*/1));
$tchrsids = $model->getTeachers();
foreach ($tchrsids as $tchr) {
  $tchrs[$tchr] = $model->teacherGetName($tchr);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>ESPT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"  media="screen,projection"/>
    <style>
      .action { margin-left: 10px; }
      .info { margin-left: 10px; font-style: italic; }
    </style>
  </head>
  <body class="grey lighten-2">

  <form id="logoutform" action="" method="post"><!-- logout form -->
      <input type="hidden" name="type" value="logout">
  </form>

    <nav>
      <div class="nav-wrapper teal">
        <a href="#" style="margin-left: 20px" class="brand-logo">Elternsprechtag</a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
          <li><a id="logout" onclick="document.getElementById('logoutform').submit()" href="#"><i class="material-icons">power_settings_new</i></a></li>
        </ul>
      </div>
    </nav>

    <div class="container">

      <div class="card ">
        <div class="card-content">
          <span class="card-title">Termin buchen</span>
          <div class="row">
            <div class="col l3">
              <ul class="teachers collection">
                <?php foreach ($tchrs as $id => $name) { ?>
                  <li class="tab"><a class="collection-item" onclick="$('html, body').animate({ scrollTop: 0 }, 200);" href="#test<?php echo $id; ?>"><?php echo $name['name'].', '.$name['surname']; ?></a></li>
                <?php } ?>
              </ul>
            </div>
            <div class="col l9">
              <?php foreach ($tchrs as $id => $name) { ?>
                <div id="test<?php echo $id; ?>" class="col s12">
                  <ul class="collection with-header">
                    <li class="collection-header"><h4>Termin bei <font class="teal-text"><?php echo $name['surname'] . " " . $name['name']; ?></font> buchen</h4></li>

                    <li class="collection-item">
                      <div>
                        slot
                        <a href="#!" class="secondary-content action">
                          <i class="material-icons">assignment</i>
                        </a>
                        <span href="#!" class="secondary-content info grey-text">
                          freier Termin
                        </span>
                      </div>
                    </li>

                    <li class="collection-item">
                      <div>
                        slot
                        <a href="#!" class="secondary-content action">
                          <i class="material-icons">assignment</i>
                        </a>
                        <span href="#!" class="secondary-content info grey-text">
                          bereits von Ihnen gebucht
                        </span>
                      </div>
                    </li>

                    <li class="collection-item">
                      <div>
                        slot
                        <a href="#!" class="secondary-content action">
                          <i class="material-icons">assignment</i>
                        </a>
                        <span href="#!" class="secondary-content info grey-text">
                          bereits belegter Termin
                        </span>
                      </div>
                    </li>

                  </ul>
                </div>
              <?php } ?>
            </div>
          </div>
        </div>
        <div class="card-action center">
          &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
        </div>
      </div>

    </div>

    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="http://materializecss.com/bin/materialize.js"></script>
    <script>
        $(document).ready(function(){
          $('ul.teachers').tabs();
        });
    </script>
  </body>
</html>
