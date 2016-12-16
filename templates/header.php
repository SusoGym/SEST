
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Suso-Gymnasium</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!--suppress HtmlUnknownTarget -->
    <link rel="icon" type="image/ico" href="favicon.ico">
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"
          media="screen,projection"/>
    <style>
        .action {
            margin-left: 10px;
        }

        .info {
            margin-left: 10px;
            font-style: italic;
        }
    </style>
</head>
<body class="grey lighten-2" id="body">

<form id="logoutform" action="" method="post"><!-- logout form -->
    <input type="hidden" name="type" value="logout">
</form>

<nav>
    <div class="nav-wrapper teal">
        <a href="#" data-activates="mobile-nav" class="button-collapse" style="padding-left:20px;padding-right:20px;"><i
                    class="material-icons">menu</i></a>
        <ul class="left hide-on-med-and-down">
            <?php include("navbar.php"); ?>
        </ul>
        <ul class="right hide-on-med-and-down">
          <li><a id="logout" href="?type=logout" title="Logout"><i class="material-icons right">power_settings_new</i>Log Out</a></li>
        </ul>
    </div>
</nav>
