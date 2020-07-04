<?php namespace administrator;
    include("header.php");
    $data = \View::getInstance()->getDataForView();
	$studentData = $data['studentData'];
?>


	<div class="row">
	<div class="col s12 m12 l12">
		<div class="card">
			<div class="card-content">
				<span class="card-title"><?php echo $data['title'] ?></span>
				 	<div >
                    <ul class="collapsible" id="studentlist"></ul>	
					<?php //var_dump($studentData) ?>
					</div>
				<div id="pupils"></div>
					
			</div>
		</div>
	</div>


    <!-- blueprint for collapsible list -->

    <li id="row_blueprint" style="display: none;">
      <div class="collapsible-header" name="listheader"></div>
      <div class="collapsible-body" name="listbody">
        <div name="pupilinfo"></div>
        <div name="parentinfo"></div>
        <div name="lockerinfo"></div>
        <div name="libraryinfo"></div>
      </div>
    </li>	

    <!-- check in absentees.js to remember how a new line is added -->
	
	<div id = "infocard" class="col s12 m6 l6" style="visibility: hidden">
		<div class="card">
			<div class="card-content">
				
				<span class="card-title" id="title"><?php echo "Übersicht" ?></span>
				<div class="teal-text" id="pupildata"></div>
                <div class="teal-text" id="lockerdata"></div>
                <div class="teal-text" id="librarydata"></div>
                <div id="deregisterButton" style="visibility: hidden"></div>
                
				
			</div>
		</div>
	</div>
	
	<div id="deregistration" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="deregistrationText">Überschrift</div>
		
		<div class="modal-footer">
		<a onclick="deregisterStudent();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Bestätigen</a>
		<a onclick="abortDeregistration();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
        
		</div>
    </div>
    </div>

	<div id="returnLocker" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="returnLockerText">Überschrift</div>
		
		<div class="modal-footer">
		<a onclick="returnLocker();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Bestätigen</a>
		<a onclick="abortReturnLocker();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
        
		</div>
    </div>
    </div>
	

</div>



<script src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<!-- Include Javascript -->
<script type="application/javascript">
<?php include("studentaction.js") ?>
</script>
<script type="text/javascript">
//requestReady = "true";
studentData = <?php echo json_encode($studentData) ?>;
createStudentActionRequiredList();
</script>
</body>
</html>
