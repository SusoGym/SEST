<?php namespace administrator;
include("header.php") ?>

<div class="container">

    <div class="card ">
        <div class="card-content">
            <div class="row">

                <b><?php echo \View::getInstance()->getAction(); ?></b>

            </div>
            <div class="row">
                <ul><a id="home" href="?type=sestconfig">Elternsprechtag konfigurieren</a></ul>
            </div>
            <div class="row">
                <ul><a id="home" href="?type=newsconfig">Newsletter konfigurieren</a></ul>
            </div>
        </div>

    </div>

</div>

<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>