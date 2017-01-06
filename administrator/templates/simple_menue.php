<?php namespace administrator;
    include("header.php");

    $data = \View::getInstance()->getDataForView();
   
?>


<div class="container">

    <div class="card">
        <div class="card-content">
          <span class="card-title">
            <?php if (isset($data["backButton"]))
            { ?>
                <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                   href="<?php echo $data["backButton"]; ?>"><i
                            class="material-icons">chevron_left</i></a>
            <?php } ?>
              <?php echo \View::getInstance()->getTitle(); ?>
          </span>
            <p style="margin-top: 20px;">
                <?php
                    if (isset($data["menueItems"]))
                    {
                    foreach ($data["menueItems"] as $m)
                    { ?>
            <div class="row">
                <a class="mdl-navigation__link teal-text btn-flat" id="menueItem"
                   href="<?php echo $m['link']; ?>"><?php echo $m['entry']; ?></a>
            </div>

            <?php
                }
                } ?>

            </p>
        </div>

    </div>


</div>

<?php include "js.php"; ?>
</body>
</html>
