<?php

include("header.php");
$slotlink = true;
$slotcolor = "teal";
$slotdue = null;
$data = $this->getDataForView();
$today = date("Ymd"); 

if(isset($data['assign_end'])){
		if ( $data['assign_end'] < $today || $data['assign_start'] > $today) {
			$slotlink = false;
			$slotcolor = "grey";
		} 
	} 
	
if(isset($data['missing_slots']) >0){
	$slotdue = $data['missing_slots'];
	}
?>
<div class="container">

    <div class="card ">
        <div class="card-content ">
			 <div class="row hide-on-med-and-down teal-text" style="font-size: 36px;">
				<b>Folgende Funktionen stehen zur Verfügung</b>
			</div>
			 
			 <div class="row">
			 
			<div class="col s6 m6 l6"> 
			<?php if ($slotlink) { ?> <a id="home" href="?type=lest" title="Elternsprechtag"> <?php } ?>
				<div class="center promo <?php echo $slotcolor; ?>" style="border:solid; border-color:<?php echo $slotcolor; ?>; border-style: outset; border-radius:5px;">
					<i class="material-icons  white-text" style="font-size: 96px;">supervisor_account</i></li>
					<p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">Elternsprechtag</p>
					<p class="hide-on-med-and-down promo-caption red-text " style="font-size: 14px;"><b>
						<?php 
						if (isset($slotdue)) {
							echo  $slotdue. " Termine festlegen!";
							} 
						else { ?>
								&nbsp;
							<?php } ?>
							</b></p>
					<p class="light-center"></p>
				</div>
			<?php if ($slotlink) { ?>  </a> <?php } ?>
			</div>
			
			<div class="col s6 m6 l6"> 
			<a id="home" href="?type=home" title="Vertretungsplan">
				<div class="center promo teal" style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
					<i class="material-icons  white-text " style="font-size: 96px;">business</i></li>
					<p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">Vertretungsplan</p>
					<p class="hide-on-med-and-down promo-caption white-text " style="font-size: 14px;">&nbsp;</p>
					<p class="light-center"></p>
				</div>
			</a>
			</div>
			 
			<div class="row">
			
			</div>
			<div class="col s6 m6 l6"> 
			<a id="home" href="?type=home" title="Termine">
				<div class="center promo teal" style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
					<i class="material-icons  white-text " style="font-size: 96px;">today</i></li>
					<p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">Termine</p>
					<p class="light-center"></p>
				</div>
			</a>
			</div>
			
			<div class="col s6 m6 l6"> 
			<a id="home" href="?type=home" title="Newsletter">
				<div class="center promo teal" style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
					<i class="material-icons  white-text " style="font-size: 96px;">library_books</i></li>
					<p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">Newsletter</p>
					<p class="light-center"></p>
				</div>
			</a>
			</div>
			 
			</div>
			
            
		</div>
	</div>
</div>

<?php include("js.php"); ?>

</body>
</html>