<div id="markabsent" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="absenceName">Name</div>
		<div id="editdata" class="col s12"></div>
		<div id = "previousAbsence" class="col s12 red-text"></div>
		
		<div class="col s12 " id="noticevia">
		<label for="via">Meldung per</label>
		<input class="with-gap" name="via" type="radio" id="phone" value="0" checked />
				<label for="phone">Telefon</label>
			<input class="with-gap" name="via" id="mail" value="1" type="radio"  />
				<label for="mail">Email</label>
		</div>	
		<div class="input-field col s6"> 
			<input type="text" onChange="checkPreviousDayAbsence()" id="sickstart" name="start" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="sickstart" class="truncate">Beginn Abwesenheit</label>
		</div>	
		<div class="input-field col s6"> 
			<input type="text" id="sickend" name="end" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="sickend" class="truncate">Ende Abwesenheit</label>
		</div>
		<div class="input-field col s12"> 
			<input type="text" id="comment" name="comment" value="">
			<label for="comment" class="truncate">Kommentar</label>
		</div>
		<div class="modal-footer">
        <a id="saveButton" onclick="saveAbsence();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Speichern</a>
		<a onclick="abortAbsence();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
		</div>
    </div>
</div>

<div id="leaveofabsence" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="loaName">Name</div>
		<div id="editdata" class="col s12"></div>
		
		<div class="input-field col s6"> 
			<input type="text"  id="loastart" name="start" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="loastart" class="truncate">Beginn Beurlaubung</label>
		</div>	
		<div class="input-field col s6"> 
			<input type="text" id="loaend" name="end" class="datepicker" value="<?php echo date('d.m.Y'); ?>">
			<label for="loaend" class="truncate">Ende Beurlaubung</label>
		</div>
		<div class="input-field col s12"> 
			<input type="text" id="comment" name="comment" value="">
			<label for="comment" class="truncate">Grund</label>
		</div>
		<div class="modal-footer">
        <a id="saveButton" onclick="saveloa();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Speichern</a>
		<a onclick="abortloa();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">close</i>Abbrechen</a>
		</div>
    </div>
</div>

<div id="editabsence" class="modal" style = "display: none;">
    <div class="modal-content">
        <div id="editName"></div>
		<div id="editdata" class="col s12"></div>
		<div class="col s12 ">
		<label for="via">Meldung per</label>
			<input class="with-gap" name="evia" type="radio" id="ephone" value="0" />
				<label for="ephone">Telefon</label>
			<input class="with-gap" name="evia" type="radio" id="email" value="1"   />
				<label for="email">Email</label>
		</div>	
		<div class="input-field col s6"> 
			<input type="text" onChange="checkPreviousDayAbsence()" id="esickstart" name="start" class="datepicker" value=" ">
			<label for="sickend" class="truncate">Beginn Abwesenheit</label>
		</div>	
		<div class="input-field col s6"> 
			<input type="text" id="esickend" name="end" class="datepicker" value=" ">
			<label for="sickend" class="truncate">Ende Abwesenheit</label>
		</div>
		<div class="input-field col s12"> 
			<input type="text" id="ecomment" name="comment" value=null>
			<label for="sickend" class="truncate">Kommentar</label>
		</div>
		<div class="modal-footer">
        <a id="esaveButton" onclick="saveEdited();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Speichern</a>
		<a onclick="abortEdit();" class="modal-action waves-effect waves-green btn-flat right teal-text"
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