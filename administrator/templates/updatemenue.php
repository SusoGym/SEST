<?php namespace administrator;
    include("header.php");
?>


<div class="container">

    <div class="card">
        <div class="card-content ">
            <div class="row">
                <b><?php echo \View::getInstance()->getDataForView()['action']; ?></b>
            </div>
            <div class="row">
                <ul><a id="home" href="?type=update_s">Abgleich Schülerdaten</a></ul>
            </div>
            <div class="row">
                <ul><a id="home" href="?type=update_t">Abgleich Lehrerdaten</a></ul>
            </div>
        </div>

    </div>


</div>


<!-- Include Javascript -->
<?php include("js.php") ?>

</body>
</html>
