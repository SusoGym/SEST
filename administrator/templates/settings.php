<?php namespace administrator;
    include("header.php") ?>

<div class="container">

    <div class="card ">
        <div class="card-content">

            <span class="card-title"><?php echo \View::getInstance()->getDataForView()['action']; ?></span>
            <a class="btn-flat" id="home" href="?type=sestconfig">Elternsprechtag konfigurieren</a>
            <a class="btn-flat" id="home" href="?type=newsconfig">Newsletter konfigurieren</a>
        </div>

    </div>

</div>

<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>
