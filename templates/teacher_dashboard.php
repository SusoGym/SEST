<?php
include("header.php");
$data = $this->getDataForView();
$cover_lessons = $data['VP_coverLessons'];
?>

<div class="row">
<div class="col s12">
    <div class="card white ">
        <div class="card-content">
            <span class="card-title">Hinweise</span>
            <p><?php echo $data['welcomeText']; ?></p>
        </div>
    </div>
</div>
<!--
<div class="col l6 s12 m6">
	<div class="card white ">
        <div class="card-content">
            <span class="card-title">Blog</span>
            <p>Keine neuen Nachrichten</p>
        </div>
    </div>
</div>
-->
<div class="col l6 s12 m6">
	<div class="card white ">
        <div class="card-content">
            <span class="card-title">Vertretungen</span>
            <p><?php echo count($cover_lessons)." aktuelle Vertretungen" ?></p>
        
		<div class="card-action">
			<a class="secondary-content action" href="?type=vplan">zum Vertretungsplan</a>
		</div>
		</div>
		
    </div>
</div>
<div class="col l6 s12 m6">
    <div class="card white ">
        <div class="card-content">
            <span class="card-title">Demnächst</span>
            <?php
            if (isset($data["upcomingEvents"]) && count($data["upcomingEvents"]) > 0) {
				foreach ($data["upcomingEvents"] as $t) {
					
					
					?>
					<span><br><b><a class="teal-text"><?php echo $t->typ; ?></b></a><a class="teal-text">
				<?php echo $t->sweekday . " " . $t->sday;
				if (isset($t->stime)) {
					echo ' (' . $t->stime . ')';
				}
				if (isset($t->eday)) {
					echo "-";
				}
				echo " " . $t->eweekday . " " . $t->eday;
				if (isset($t->etime)) {
					echo ' (' . $t->etime . ')';
				}
				
				?>
				</a>
				</span>
					<?php
					
				}
			}
            ?>
        </div>
    </div>
</div>
</div>



<?php include("js.php"); ?>

</body>
</html>
