<?php

include("header.php");
$data = $this->getDataForView();
?>
<div class="container">

    <div class="card ">
        <div class="card-content">
			<div class="row ">
				<b>Auswahl der Sprechzeiten für den Elternsprechtag:</b>
			</div>
            <div class="row teal-text">
				Aktuelles Deputat: <?php echo $data['deputat']; ?>
				<br>
				<b>Bitte wählen Sie <?php echo $data['requiredSlots']; ?> verfügbare Termine aus!</b>
			</div>
		</div>
	</div>
</div>

<?php include("js.php"); ?>

</body>
</html>