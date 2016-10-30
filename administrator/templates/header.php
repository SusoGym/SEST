<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>suso-intern-admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/ico" href="../favicon.ico">
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"  media="screen,projection"/>
    
	<style>
      .action { margin-left: 10px; }
      .info { margin-left: 10px; font-style: italic; }
    </style>
  </head>
  <body class="grey lighten-2" >

<form id="logoutform" action="" method="post"><!-- logout form -->
      <input type="hidden" name="type" value="logout">
  </form>

<nav>
      <div class="nav-wrapper teal">
        <a href="#" style="margin-left: 20px" class="brand-logo">suso-intern-admin</a>
        <a href="#" data-activates="mobile-nav" class="button-collapse" style="padding-left:20px;padding-right:20px;"><i class="material-icons">menu</i></a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
		  <li><a id="news" href="index.php?type=updmgt"><i class="material-icons">input</i></a></li>
		  <li><a id="news" href="index.php?type=settings"><i class="material-icons">settings</i></a></li>
		  <li><a id="news" href="index.php?type=usrmgt"><i class="material-icons">people</i></a></li>
		  <li><a id="news" href="index.php?type=news"><i class="material-icons">comment</i></a></li>
		  <li><a id="home" href="index.php"><i class="material-icons">home</i></a></li>
          <li><a id="logout" onclick="document.getElementById('logoutform').submit()" href="#"><i class="material-icons">power_settings_new</i></a></li>
        </ul>
      </div>
</nav>
	
	<ul id="mobile-nav" class="side-nav">
      <li>
        <div class="userView">
          <img class="background grey" src="http://materializecss.com/images/office.jpg">
          <a href="#!user"><img class="circle" src="http://www.motormasters.info/wp-content/uploads/2015/02/dummy-profile-pic-male1.jpg"></a>
          <a href="#!name"><span class="white-text name"><?php echo $_SESSION['user']['name']; ?></span></a>
        </div>
      </li>
      <li><a class="waves-effect" href="index.php?type=updmgt"><i class="material-icons">input</i>Datenabgleich</a></li>
	  <li><a class="waves-effect" href="index.php?type=settings"><i class="material-icons">settings</i>Einstellungen</a></li>
	  <li><a class="waves-effect" href="index.php?type=usrmgt"><i class="material-icons">people</i>Benutzerverwaltung</a></li>
	  <li><a class="waves-effect" href="index.php?type=news"><i class="material-icons">comment</i>Newslettereintrag</a></li>
	  <li><a class="waves-effect" href="index.php"><i class="material-icons">home</i>Home</a></li>
	  <li><a class="waves-effect" onclick="document.getElementById('logoutform').submit()" href="#!"><i class="material-icons">power_settings_new</i>Logout</a></li>
      <li><div class="divider"></div></li>
      
    </ul>