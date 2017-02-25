<?php
    include("header.php");
	$data = $this->getDataForView();
?>
<div class="container">
    <div class="card white">
        <div class="card-content">
            <span class="card-title">Startseite</span>
            <p><?php echo $data['welcomeText']; ?></p>
        </div>
    </div>
	<div class="card white">
        <div class="card-content">
			<span class="card-title">Demnächst</span>
            <?php 
			foreach ($data["upcomingEvents"] as $t){
			
				
			?>
			<span ><br><b><a class="teal-text"><?php echo  $t->typ; ?></b></a><a class="teal-text">
			<?php	echo  $t->sweekday." ".$t->sday; 
			if (isset($t->stime)){echo ' ('.$t->stime.')';}
			if (isset($t->eday)) {echo "-";}
			echo  " ".$t->eweekday." ".$t->eday;
			if (isset($t->etime)){echo ' ('.$t->etime.')';}
			?>
			</a>
			</span>				
			<?php
			  
			}
			?>
        </div>
    </div>
</div>


<?php include("js.php"); ?>

</body>
</html>
