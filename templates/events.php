<?php
    $data = $this->getDataForView();
    /** @var Guardian $user */
    $user = $data['user'];

    $today = date("Ymd");
    include("header.php");

$today=date('d.m.Y');
$todayMonth=date('Ym');
//$today="12.10.2016";//Nur zum Debugging
$todayTimestamp = strtotime($today);
$modeLink='<a href="?type=events&all" class="teal-text right"><i class="material-icons right">select_all</i><span style="font-size:10px;" >alle anzeigen</span></a>';
//Für Anzeige des kompletten Jahres wird todayTimestamp auf das Datum des ersten Termins gesetzt.
if (isset($data['showAllEvents'])){
	$first=$data['events'][0]->sday;
	$todayTimestamp = strtotime($first);
	$todayMonth=$data['events'][0]->jahr.$data['events'][0]->monatNum;
	$modeLink='<a href="?type=events" class="teal-text right"><i class="material-icons right">filter_list</i><span style="font-size:10px;" >aktuelle anzeigen</span></a>';
	}
?>

    <?php foreach($data['months'] as $month) { 
	$yearmonth=$month["jahr"].$month["mnum"];
	if($todayMonth <= $yearmonth) { ?>
		
	<div class="container col s4 m4 l4">
		<div class="card ">
        		<div class="card-content">
            			<div class="row ">
                			<div class="col l12 m12 s12">
                     			<ul class="collection with-header teal-text ">
                					<li class="collection-header"><i class="material-icons left ">today</i>
							<span style="font-size:16px;font-weight:bold;"><?php echo $month['mstring']." ".$month['jahr']; ?></span><?php echo $modeLink; ?><li>
							<?php foreach($data['events'] as $t){ 
								 if($t->monatNum == $month["mnum"]) {
									 //Anzeige
									 ?>
									 <li class="collection-item">
										<p style="font-size:14px;font-weight:bold;"><?php echo  $t->typ ?></p>
										<p style="font-size:10px;">
											<?php echo " ".$t->sweekday." ". $t->sday." ";?>
											<?php if (isset($t->stime)){ ?>  <?php echo ' ('.$t->stime.')';  } ?>
											<?php if (isset($t->eday)) { ?>  <?php echo "-";	?>
												<?php echo  $t->eweekday?>
												<?php echo  $t->eday;
												if (isset($t->etime)){echo ' ('.$t->etime.')';}	?>
										  <?php } ?>
										</p>
									  </li>
									<?php 
									}
						 } ?>

						<ul>
		  			</div>
            			</div>
        		</div>
      		</div>
	</div>    
<?php 
	}
	} ?>




<?php include("js.php"); ?>

</body>
</html>
