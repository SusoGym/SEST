
<?php
$userObj = null;
if (isset($_SESSION['user']['id'])) {
    $userObj = Controller::getUser();
}

if ($mobile) { ?>


    <?php if ($userObj != null && $userObj->getType() == 0) { //only if user == admin ?>

        <li><a class="waves-effect" href="/administrator"><i class="material-icons">adb</i>Administration</a></li>

    <?php } ?>

    <li><a class="waves-effect" href="?type=logout"><i class="material-icons">power_settings_new</i>Logout</a></li>

<?php } else { ?>


    <?php if ($userObj != null && $userObj->getType() == 0) { //only if user == admin?>

        <li><a id="admin" href="/administrator" title="Administration"><i class="material-icons">adb</i></a></li>

    <?php } ?>

    <li><a id="home" href="." title="Home"><i class="material-icons left">home</i><font style="font-size: 24px;">Suso-Intern</font></a></li>
	  <li><a id="home" href="?type=childsel" title="Home"><i class="material-icons left">face</i>Kinder verwalten</a></li>
	  <?php // if($estActive){ ?>
      <li><a id="home" href="?type=eest" title="Home"><i class="material-icons left">supervisor_account</i>Elternsprechtag</a></li>
    <?php // } if($selectionActive) { ?>
	  <li><a id="home" href="?type=news" title="Home"><i class="material-icons left">dashboard</i>Vertretungsplan</a></li>
	  <li><a id="home" href="?type=events" title="Home"><i class="material-icons left">today</i>Termine</a></li>
	  <li><a id="home" href="?type=news" title="Home"><i class="material-icons left">library_books</i>Newsletter</a></li>
    <?php // } ?>


<?php } ?>
