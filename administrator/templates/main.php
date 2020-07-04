<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
$absentees = $data['missingStudents'];
$missingExcuses = $data['missingExcuses'];
$studentList=$data['studentList'];
$admin = $data['isadmin'];
$coverLessons = $data['VP_coverLessons'];
?>
<div class="row">
	<div class="col s6 m6 l6">
		<div class="card">
			<div class="card-content">
				<span class="card-title"><?php echo \View::getInstance()->getTitle(); ?></span>
				<div id = "action_required" >
					<div>
					<!--registration requests -->
					<?php if ($data['reg_requests'] != null) {
							echo '<a class="red-text" href="?type=handleregister">Neue Registrierungsanfragen ('.count($data['reg_requests']).')</a>';
						}
					?>
					</div>
					<div>
						<?php if ($data['student_action_req'] != null) {
								echo '<a class="red-text" href="?type=studentaction">Zu löschende Schüler*innen ('.count($data['student_action_req']).')</a>';
							}
						?>
					</div>
				</div>
				
			</div>
		</div>
	</div>
	<!-- should there be a link to a new display or rather a modal showing the coverLessons? -->
	<div class="col s6 m6 l6">
		<div class="card">
			<div class="card-content">
				<span class="card-title">Vertretungen: <?php echo count($coverLessons); ?></span>
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
					<?php include('absentpupils_form.php'); ?>
					
			</div>
		</div>
	</div>

	<div class="col s12 m6 l6">
		<div class="card">
			<div class="card-content">
				
				<span class="card-title" ><?php echo "Abwesenheiten am ".date('d.m.Y'); ?></span>
				<!-- <div id="absenteelist"> -->
					<ul class="collapsible" id="absenteelist"></ul>	
					<!-- <span class="row" id="row_blueprint" style="display: none;"></span> -->
			<div id="printit" class="card-action" style="display:none;">
			<a class="btn-flat teal-text" href="#" onClick="printAbsence()">Abwesenheiten drucken</a>
			</div>		
			</div>
					
			</div>
		</div>
	</div>
	
	<?php include('absentpupil_modals.php'); ?>

<!-- blueprint for collapsible list -->

    <li id="row_blueprint" style="display: none;">
      <div class="collapsible-header" name="listheader"></div>
      <div class="collapsible-body" name="listbody"></div>
    </li>	

<div class="row">
<div class="col s12 m12 l6">
	<div class="card" style="display: none">
		<div class="card-content">
			<span class="card-title"><?php echo "offene Entschuldigungen" ?></span>
				<div id="missingexcuseslist">
				<ul>
					<span class="row grey lighten-5" id="row_blueprint" style="display: none;"></span>
				</ul>
				</div>
		
		</div>
	</div>
</div>
</div>



<script src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<!-- Include Javascript -->
<script type="application/javascript">
<?php include("absentees.js") ?>
</script>
<script type="text/javascript">
requestReady = "true";
//leaveOfAbsence = null;
//console.log(leaveOfAbsence);
//absentees = <?php echo $absentees; ?>;
//missingExcuses = <?php echo $missingExcuses; ?>;

//createPupilListData(absentees,missingExcuses);
studentList = <?php echo $studentList ?>;
createStudentList(studentList);
createAbsenteeList();
//createMissingExcuseList();



</script>



</body>
</html>
