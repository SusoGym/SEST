<?php namespace administrator;
    include("header.php");
    $data = \View::getInstance()->getDataForView();

?>

<div class="container">

    <div class="card">
        <div class="card-content ">
            <div class="row">
                <b><?php echo \View::getInstance()->getTitle(); ?></b>
                <?php if (isset($data["backButton"]))
                { ?>
                    <a id="backButton" class="mdl-navigation__link right teal-text"
                       href="<?php echo $data["backButton"]; ?>"><i
                                class="material-icons">arrow_back</i></a>
                <?php } ?>


            </div>
            <div class="row">
                <span id="slot_placeholder"></span>
                <form action="?type=setslots" method="POST">
                    <div id="slot_blueprint" style="display:inline;">
                        <div class="input-field col s6">
                            <input id="start" name="start" type="text" required class="validate">
                            <label for="name">Beginn(DD.MM.YYYY HH:MM)</label>
                        </div>
                        <div class="input-field col s6">
                            <input id="end" name="end" type="text" required class="validate">
                            <label for="name">Ende(DD.MM.YYYY HH:MM)</label>
                        </div>
                    </div>
                    <input type="submit" value="Neue Sprechzeit eintragen"
                           class="btn-flat btn-large waves-effect waves-light teal-text col s12"></input>
                </form>
            </div>
            <?php
                if (isset($data["slots"]))
                { ?>
                    <div class="row col s6">
                        <?php
                            foreach ($data["slots"] as $s)
                            { ?>

                                <?php
                                $anfang = date_format(date_create($s['anfang']), 'd.m.Y H:i');
                                $ende = date_format(date_create($s['ende']), 'd.m.Y H:i');
                                ?>
                                <ul class="teal-text"><?php echo $anfang . " bis " . $ende; ?>
                                    <a id="slot" href="?type=setslots&del=<?php echo $s['id']; ?> " class="teal-text"><i
                                                class="material-icons">delete</i></a>
                                </ul>
                                <?php
                            } ?>
                    </div>
                <?php } ?>


        </div>

    </div>


</div>


<?php include "js.php"; ?>
</body>
</html>
