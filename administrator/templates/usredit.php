<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
?>
<div class="container">

    <div class="card ">
        <div class="card-content">
            <span class="card-title">
            <?php if (isset($data["backButton"])) { ?>
                <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                   href="<?php echo $data["backButton"]; ?>"><i
                            class="material-icons">chevron_left</i></a>
            <?php } ?>
                <?php echo \View::getInstance()->getTitle(); ?>
             </span>
            <?php /** @var \User $usr */
            $usr = $data['user']; ?>
            <form action="?type=usredit&edit&name=<?php echo $usr->getEmail(); ?>" method="POST" autocomplete="off">
                <div class="row">
                    <div class="input-field col s4 l4 m4">
                        <label for="f_name">Name:</label>
                        <input name="f_name" type="text" value="<?php echo $usr->getName(); ?>" required="required"
                               class="validate">
                    </div>
                    <div class="input-field col s4 l4 m4">
                        <label for="f_surname">Nachname:</label>
                        <input name="f_surname" type="text" value="<?php echo $usr->getSurname(); ?>"
                               required="required"
                               class="validate">
                    </div>
                    <div class="input-field col s4 l4 m4">
                        <label for="f_email">Email:</label>
                        <input name="f_email" type="email" value="<?php echo $usr->getEmail(); ?>" required="required"
                               class="validate">
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s6 l6 m6">
                        <label for="f_pwd">Neues Password:</label>
                        <input name="f_pwd" type="password">
                    </div>
                    <div class="input-field col s6 l6 m6">
                        <label for="f_pwd_repeat">Neues Password wiederholen:</label>
                        <input name="f_pwd_repeat" type="password">
                    </div>
                </div>
                <div class="row">
                    <div class="input-field col s2 l2 m2 offset-s10 offset-l10 offset-m10">
                        <button class="btn waves-effect waves-light" type="submit">Update!
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </form>

        </div>
    </div>

</div>

<!-- Include Javascript -->
<?php include("js.php") ?>
</body>
</html>

