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
            <?php
                if (isset($data["menueItems"]))
                {
                    foreach ($data["menueItems"] as $m)
                    { ?>
                        <div class="row">
                            <ul><a class="mdl-navigation__link teal-text" id="menueItem"
                                   href="<?php echo $m['link']; ?>"><?php echo $m['entry']; ?></a></ul>
                        </div>

                        <?php
                    }
                } ?>


        </div>

    </div>


</div>

<?php include "js.php"; ?>
</body>
</html>
