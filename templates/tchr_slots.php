<?php

include("header.php");
$data = $this->getDataForView();
//echo $data['user']->getUsername();
?>
<div class="container">

    <div class="card ">
        <div class="card-content ">
			 <div class="row hide-on-med-and-down teal-text" style="font-size: 36px;">
				<b>Festlegung der Sprechzeiten</b>
			</div>
			 
			
			
            <div class="row teal-text">
				Aktuelles Deputat: <?php echo $data['deputat']; ?>
				<br>
				<b>Sie müssen <?php echo $data['requiredSlots']; ?> Termine angeben!</b>
			</div>
			<div class="row red-text">
				<b><?php echo  $data['missing_slots']; ?>  Termine müssen noch festgelegt werden!</b>
			</div>
		</div>
	</div>
</div>

<?php include("js.php"); ?>

</body>
</html>