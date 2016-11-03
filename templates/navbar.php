<?php if ($mobile) { ?>


    <?php if (isset($_SESSION['user']['id']) && Model::getInstance()->userGetType($_SESSION['user']['id']) == 0) { //only if user == admin?>

        <li><a class="waves-effect" href="/administrator"><i class="material-icons">adb</i>Administration</a></li>

    <?php } ?>

    <li><a class="waves-effect" href="?type=logout"><i class="material-icons">power_settings_new</i>Logout</a></li>

<?php } else { ?>


    <?php if (isset($_SESSION['user']['id']) && Model::getInstance()->userGetType($_SESSION['user']['id']) == 0) { //only if user == admin?>

        <li><a id="admin" href="/administrator" title="Administration"><i class="material-icons">adb</i></a></li>

    <?php } ?>

    <li><a id="logout" href="?type=logout" title="Logout"><i class="material-icons">power_settings_new</i></a></li>

<?php } ?>