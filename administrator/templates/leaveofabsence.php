<?php namespace administrator;
    include("header.php");
    $data = \View::getInstance()->getDataForView();
	$studentList = $data['studentList'];
?>


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
					
			</div>
		</div>
	</div>
	
	<div class="col s12 m6 l6">
		<div class="card">
			<div class="card-content">
				
				<span class="card-title" ><?php echo "kommende Beurlaubungen" ?></span>
				<ul class="collapsible" id="loalist">	</ul>
				
			</div>
		</div>
	</div>
	
	<?php include('absentpupil_modals.php'); ?>
	<!-- blueprint for collapsible list -->

    <li id="row_blueprint" style="display: none;">
      <div class="collapsible-header" name="listheader"></div>
      <div class="collapsible-body" name="listbody"></div>
    </li>

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
leaveOfAbsence = "true";

studentList = <?php echo $studentList ?>;
createStudentList(studentList);
createLoaList();



</script>
</body>
</html>
