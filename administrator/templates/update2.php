<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
?>


<div class="container">

    <div class="card ">
        <div class="card-content">
            <span class="card-title">
                Daten wurden gelesen
            </span>
            <p>
                überprüfte Datensätze: <?php echo $data['fileData'][0]; ?><br>
                eingefügte Datensätze: <?php echo $data['fileData'][1]; ?><br>
                gelöschte Datensätze: <?php echo $data['fileData'][2]; ?>
            </p>

            <p>
                <?php echo $this->action; ?></br />
            </p>


        </div>

    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>
