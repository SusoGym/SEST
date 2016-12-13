<?php namespace administrator;
    include("header.php");
?>

<div class="container">

    <div class="card ">
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
        </div>

    </div>

</div>

<!-- Include Javascript -->
<?php include("js.php") ?>

</body>
</html>
