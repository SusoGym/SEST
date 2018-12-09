<div id="markabsent" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="absenceName">Name</div>
		<div id="editdata" class="col s12"></div>
		<div id = "previousAbsence" class="col s12 red-text"></div>
		
		<div class="input-field col s6 m6 l6"> 
			<input type="text" onChange="checkPreviousDayAbsence()" id="sickstart" name="start" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="sickend" class="truncate">Beginn Abwesenheit</label>
		</div>	
		<div class="input-field col s6 m6 l 6"> 
			<input type="text" id="sickend" name="end" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="sickend" class="truncate">Ende Abwesenheit</label>
		</div>
		<div class="input-field col s12"> 
			<input type="text" id="comment" name="comment" value="">
			<label for="sickend" class="truncate">Kommentar</label>
		</div>
		<div class="modal-footer">
        <a id="saveButton" onclick="saveAbsence();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Speichern</a>
		<a onclick="abortAbsence();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
		</div>
    </div>
</div>

<div id="deleteexcuse" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="deleteExcuseName">Überschrift</div>
		<div id="deletedata" class="col s12"> 
			Daten zur Abwesenheit
		</div>
		<div class="modal-footer">
		<a onclick="deleteAbsence();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Löschen</a>
		<a onclick="abortAbsenceDelete();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
        
		</div>
    </div>
</div>

<div id="markexcuse" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="excuseName">Überschrift</div>
		
			
		<div id="absencedata" class="col s12"> 
			Daten zur Abwesenheit
		</div>
		<div class="input-field col s6"> 
			<input type="text" id="excusein" name="excusein" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="excusein" class="truncate">Enschuldigungseingang</label>
		</div>
		<div class="input-field col s6"> 
			<input type="text" id="excusecomment" name="excusecomment" >
			<label for="excusecomment" class="truncate">Kommentar</label>
		</div>
		<div class="modal-footer">
		<a onclick="saveAbsenceExcuse();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Entschuldigung speichern</a>
		<a onclick="abortExcuse();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
		   
        
		</div>
    </div>
</div>

<div id="notes" class="modal" style = "display: none;">
    <div class="modal-content">
        <div class="card-title" id="titel"><h5>Hinweis</h3></div>
		<div class="col s12">
		<?php echo $welcomeText; ?>
		</div>
		
		<div class="modal-footer">
        <a onclick="closenote();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>gelesen</a>
		</div>
    </div>
</div>

<script type="text/javascript">


function closenote() {
$('#notes').modal('close');		
}
</script>

