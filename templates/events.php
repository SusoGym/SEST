<?php
    $data = $this->getDataForView();
    /** @var Guardian $user */
    $user = $data['user'];

    $today = date("Ymd");
    include("header.php");
?>

    <?php foreach($data['months'] as $month) { ?>
	<div class="container col s4 m4 l4">

	<div class="card ">
        <div class="card-content">
            <div class="row">
                <div class="col l12 m12 s12">
                     <ul class="collection with-header teal-text">
                		<li class="collection-header"><?php echo $month['mstring']; ?><li>

			<ul>



                </div>
            </div>
        </div>
        
    </div>
</div>    
<?php } ?>




<?php include("js.php"); ?>

</body>
</html>
