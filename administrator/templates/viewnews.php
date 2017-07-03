<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();



?>

<div class="container">

    <div class="card ">
        <div class="card-content">
            <span class="card-title"><?php echo $data["title"] ; ?></span> 
		<div>
	<div class="row"><?php echo $data["newsletter"]->makeViewText(); ?>	</div>
	

    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>
