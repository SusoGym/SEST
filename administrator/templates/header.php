<?php namespace administrator; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>Adminpanel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/ico" href="../favicon.ico">
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"
          media="screen,projection"/>

    <script type="application/javascript"> // not that nice here but where else?


    </script>

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
<body class="grey lighten-2">
<div id="insert"></div>


<nav>
    <div class="nav-wrapper teal">
        <a href="?" style="margin-left: 20px" class="brand-logo">Adminpanel</a>
        <a href="#" data-activates="mobile-nav" class="button-collapse" style="padding-left:20px;padding-right:20px;"><i
                    class="material-icons">menu</i></a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
            <li><a id="news" href="?type=updmgt" title="Datenableich"><i class="material-icons">input</i></a></li>
            <li><a id="news" href="?type=settings" title="Einstellungen"><i class="material-icons">settings</i></a></li>
            <li><a id="news" href="?type=usrmgt" title="Benutzerverwaltung"><i class="material-icons">people</i></a>
            </li>
            <li><a id="news" href="?type=news" title="Newslettereintrag"><i class="material-icons">comment</i></a></li>
            <li><a id="home" href="?" title="Home"><i class="material-icons">home</i></a></li>
            <li><a id="logout" href="?type=logout" title="Logout"><i class="material-icons">power_settings_new</i></a>
            </li>
        </ul>
    </div>
</nav>

<ul id="mobile-nav" class="side-nav">
    <li>
        <div class="userView">
            <img class="background grey" src="http://materializecss.com/images/office.jpg">
            <a href="#!user"><img class="circle"
                                  src="http://www.motormasters.info/wp-content/uploads/2015/02/dummy-profile-pic-male1.jpg"></a>
            <a href="#!name"><span class="white-text name"><?php echo $_SESSION['user']['mail']; ?></span></a>
        </div>
    </li>
    <li><a class="waves-effect" href="?type=updmgt"><i class="material-icons">input</i>Datenabgleich</a></li>
    <li><a class="waves-effect" href="?type=settings"><i class="material-icons">settings</i>Einstellungen</a></li>
    <li><a class="waves-effect" href="?type=usrmgt"><i class="material-icons">people</i>Benutzerverwaltung</a></li>
    <li><a class="waves-effect" href="?type=news"><i class="material-icons">comment</i>Newslettereintrag</a></li>
    <li><a class="waves-effect" href="?type=home"><i class="material-icons">home</i>Home</a></li>
    <li><a class="waves-effect" href="?type=logout"><i class="material-icons">power_settings_new</i>Logout</a></li>
    <li>
        <div class="divider"></div>
    </li>

</ul>