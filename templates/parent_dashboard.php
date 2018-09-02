<?php
include("header.php");
$data = $this->getDataForView();
$cover_lessons_text = isset($data['VP_coverLessons']) ? "es liegen Vertretungen vor" : "keine Vertretungen";
$cover_lessons_link = isset($data['VP_coverLessons']) ? true : false;
$children = (count($data['children']) > 0) ? $data['children'] : null;
$welcomeText = (isset($children) ) ? $data['welcomeText'] : "Sie müssen zunächst Ihre Kinder registrieren, bevor Sie die Angebote nutzen können!";
?>

    <div class="row">
		<div class="col s12 ">
			<div class="card white">
				<div class="card-content">
					<span class="card-title">Hinweise</span>
					<p ><?php echo $welcomeText; ?></p>
				</div>
			</div>
		</div>
		
		<?php if (isset($children) ) { ?>
		<div class="col l6 s12 m6">
			<div class="card white ">
				<div class="card-content">
					<span class="card-title">Ihre Kinder</span>
					
					<?php foreach ($children as $child) { ?>
					<div id="<?php echo $child->getId(); ?>">
					<table>
					<tr>
						
						<td>
						<?php echo $child->getSurname().', '.$child->getName().' ('.$child->getClass().')' ; ?>
						</td>
						<!-- Abwesenheitsmeldung Testphase
						<td>
						<a class="secondary-content action" href="#" onClick="illNote('<?php echo $child->getId(); ?>');"><i class="material-icons right">chat</i></a>
						<span class="secondary-content info grey-text">Abwesenheit melden</span>
						</td>
						-->
						
					</tr>
					</table>
					<div id="<?php echo "ill_".$child->getId(); ?>" style="display:none;"></div>
						
					</div>
					<?php } ?>
					
				</div>
			</div>
		</div>
		<?php } ?>
		<?php if (isset($children) ) { ?>
		<div class="col l6 s12 m6">
			<div class="card white ">
				<div class="card-content">
					<span class="card-title">Vertretungen</span>
					<p><?php echo $cover_lessons_text ?></p>
        		<?php if ($cover_lessons_link) { ?>
				<div class="card-action">
					<a class="secondary-content action" href="?type=vplan">zum Vertretungsplan</a>
				</div>
				<?php } ?>
			</div>
		
			</div>
		</div>
		<?php } ?>
		<?php if (isset($children) ) { ?>
		<div class="col s12 ">
			<div class="card white">
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
		<?php } ?>
	</div>



<?php include("js.php"); ?>
<script type="text/javascript">
//Abwesenheitsmeldung Testphase
function illNote(id) {
document.getElementById('ill_'+id).style.display = "block";
document.getElementById('ill_'+id).innerHTML = "Krankmeldung";
	
}
</script>

</body>
</html>
