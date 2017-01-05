<?php namespace administrator;
    include("header.php");
    $data = \View::getInstance()->getDataForView(); 
?>


<div class="container">
    <div class="card white">
        <div class="card-content">
            <span class="card-title"><?php echo \View::getInstance()->getTitle(); ?></span>
	     <div class="row">
		<div class="col s12 l12 m12 teal-text">
		<?php echo $data['cardtext']; ?>
		</div>
		</div>
        </div>
    </div>
</div>



<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>