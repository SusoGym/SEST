<?php
include("header.php");
$data = $this->getDataForView();
$cover_lessons = $data['VP_coverLessons'];
$taughtStudents = $data['taughtstudents'];
$taughtClasses = $data['taughtclasses'];
if ($data['welcomeText']) {
$shownotice = "true";
$welcomeText = $data['welcomeText'];	
} else {
$shownotice = "false";	
}
?>

<div class="row">
<!--
<div class="col s12 m6 l6">
    <div class="card white ">
        <div class="card-content">
            <span class="card-title">Hinweise</span>
            <p><?php echo $data['welcomeText']; ?></p>
        </div>
    </div>
</div>
-->
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
<div class="row">
	<div class="col s12 m6 l6">
		<div class="card">
			<div class="card-content">
				<span class="card-title"><?php echo "Abwesenheit eintragen" ?></span>
				 <br>
				 <div class="input-field ">
					<i class="material-icons prefix">search</i>
					<input type="text" id="pupil-input" name="name"></input>
					<label for="pupil-input" class="truncate">Nachname</label>
				</div>
				<div id="pupils"></div>	
				<br/>oder aus Klasse wählen
				<ul class="collapsible" id="classlist"></ul>
			</div>
		</div>
	</div>
	<div class="col s12 m6 l6">
		<div class="card">
			<div class="card-content">
				<span class="card-title">abwesende Schüler</span>
				 <!-- <div id="missingpupils"></div>	-->	
				<ul class="collapsible" id="missingpupils">	
			</div>
		</div>
	</div>
</div>
<!-- blueprint for collapsible list -->

    <li id="row_blueprint" style="display: none;">
      <div class="collapsible-header" name="listheader"></div>
      <div class="collapsible-body" name="listbody"></div>
    </li>
<!-- blueprint for collapsible class list -->

    <li id="class_blueprint" style="display: none;">
      <div class="collapsible-header" name="listheader"></div>
      <div class="collapsible-body" name="listbody"></div>
	  <ul id="students"></ul>
    </li>
    
<?php include("teacher_dashboard_modals.php"); ?>



<?php include("js.php"); ?>
<script type="text/javascript">

<?php include("absence_mgt.js"); ?>
shownotice = <?php echo $shownotice; ?>;
document.addEventListener("DOMContentLoaded", function(event) {
    if (shownotice){
	$('#notes').modal();
	$('#notes').modal('open');
	}
		
  });


teacherUser = 1;
requestReady = true;
studentList = <?php echo json_encode($taughtStudents); ?>;
classList = <?php echo json_encode($taughtClasses); ?>;
console.log(studentList);
createStudentList(studentList);
createClassList();
createAbsenteeList();
</script>


</body>
</html>
