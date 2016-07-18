<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>ESPT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"  media="screen,projection"/>
  </head>
  <body class="grey lighten-2">

    <nav>
      <div class="nav-wrapper teal">
        <a href="#" style="margin-left: 20px" class="brand-logo">Elternsprechtag</a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
          <li><a href="index.php?type=logout"><i class="material-icons">power_settings_new</i></a></li>
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
                  <li class="tab"><a class="collection-item" href="#test1">Lehrer 1</a></li>
                  <li class="tab"><a class="collection-item" href="#test2">Lehrer 2</a></li>
                  <li class="tab"><a class="collection-item" href="#test3">Lehrer 3</a></li>
                  <li class="tab"><a class="collection-item" href="#test4">Lehrer 4</a></li>
                </ul>
            </div>
	    <div class="col l9">
                <div id="test1" class="col s12">Buchen für Lehrer 1</div>
                <div id="test2" class="col s12">Buchen für Lehrer 2</div>
                <div id="test3" class="col s12">Buchen für Lehrer 3</div>
                <div id="test4" class="col s12">Buchen für Lehrer 4</div>
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
