<?php namespace administrator;
include("header.php");

$data = \View::getInstance()->getDataForView();
$isText = false;
foreach ($data["options"] as $options) {
    if ($options['field']) {
        $isText = true;
        break;
    }
}

?>


<div class="container">

    <div class="card">
        <form autocomplete="off" action="?type=options&sbm" class="row" method="POST">
            <div class="card-content">
        <span class="card-title">
          <?php if (isset($data["backButton"])) { ?>
              <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                 href="<?php echo $data["backButton"]; ?>"><i
                          class="material-icons">chevron_left</i></a>
          <?php } ?>
            <?php echo \View::getInstance()->getTitle(); ?>
        </span>
                <p style="margin-top: 20px;">
                    <?php
                    // TODO: textareas, datepicker, etc..
                    foreach ($data["options"] as $options){
                    if (!$options['field']) { ?>
                <div class="input-field col s12 l4 m6">
                    <input id="<?php echo $options["type"]; ?>" name="<?php echo $options["type"]; ?>" type="text"
                           class="" value="<?php echo $options['value']; ?>" required>
                    <label for="<?php echo $options["type"]; ?>">  <?php echo $options['kommentar']; ?> </label>
                </div>
                <?php
                }
                } ?>

                </p>
                <?php if ($isText) {
                    foreach ($data["options"] as $options) {
                        if ($options['field']) {
                            ?>
                            <div class="row"></div>
                            <div class="row">
                                <p><span class="teal-text"><?php echo $options['kommentar']; ?></span></p>
                                <textarea wrap="soft" name="<?php echo $options["type"]; ?>" row="5">
                  <?php echo $options['value']; ?>
                </textarea>
                            </div>
                        <?php }
                    }
                    
                } ?>

                <div class="row" style="margin-bottom: 0;">
                    <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i
                                class="material-icons right">send</i></button>
                </div>
            </div>
        </form>

    </div>
</div>

<?php include "js.php"; ?>
</body>
</html>
