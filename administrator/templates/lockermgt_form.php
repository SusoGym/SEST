


				<div id="status">
				<table border = "0">
					<tr>
						<td >
							<h5 class="teal-text"> Belegte Schließfächer : <?php echo count($lockers['hired']); ?> </h5> 
						</td>
						<td> 
							<a class="mdl-navigation__link teal-text btn-flat" href="#" onClick="openHiredOutView()">Belegung einsehen</a>
						</td>
					</tr>
					<tr>
						<td>
							<h5 class="teal-text">Freie Schließfächer: <?php echo count($lockers['empty']); ?> </h5>  
						</td>
						<td> 
							<a class="mdl-navigation__link teal-text btn-flat" href="#" onClick="showLockers()">Schließfach vergeben</a>
						</td>
					</tr>
				</table>
				</div>
				<div id = "lockers" class="input-field " style="visibility: hidden;">
					
					<!-- <i class="material-icons prefix">search</i>  -->
					<!-- <a class="mdl-navigation__link teal-text btn-flat" href="#" onClick="showLockers()">Schließfach wählen</a> -->
					<div id="lockerList">
					</div>
					
				</div>
				
				<div id = "student" class="input-field " style="visibility: hidden;">
					<i class="material-icons prefix">search</i>
					<input type="text" id="pupil-input" name="name"></input>
					<label for="pupil-input" class="truncate">Schülersuche (Nachname)</label>
				</div>
				<div id="pupils"></div>
			</div>
			

		
	


<!-- Include Javascript -->
<?php include("js.php") ?>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"
            integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<script type="application/javascript">
<?php include("lockerpupilmgt.js") ?>
var lockers;
var lockerToHire;
/**
* open management view
*/
function openManagementView() {
document.getElementById("lockers").style.visibility='visible' ;
}
/**
*show lockerlist of available lockers
*/
function showLockers() {
document.getElementById("lockers").style.visibility='visible' ;
document.getElementById("student").style.visibility='hidden' ;
document.getElementById("lockerList").innerHTML = "";
content = '<ul class="collapsible">	' ;
freeLockers = lockers['empty'];
for (i = 0; i < freeLockers.length ; i++) {

content += '<li><div class="collapsible-header "><a  class="teal-text" href="#" onClick="showStudent(' + freeLockers[i]['locker'] + ',\'' + freeLockers[i]['location'] +'\');">' + freeLockers[i]['locker'] +  ' | Standort: ' + freeLockers[i]['location'] + '</a></div></li>';

}
content += '</ul>';
document.getElementById("lockerList").innerHTML = content;


}

/**
*
* open view for hired out lockers
*/
function openHiredOutView() {
//make all other fields invisible
document.getElementById("student").style.visibility='hidden';
document.getElementById("lockers").style.visibility='visible';

content = '<ul class="collapsible">	' ;
hiredLockers = lockers['hired'];
for (i = 0; i < hiredLockers.length ; i++) {

content += '<li><div class="collapsible-header ">' + hiredLockers[i]['locker'] + ' | Standort: ' + hiredLockers[i]['location'] +' [ ' 
+ hiredLockers[i]['student']['data']['surname'] + ', ' + hiredLockers[i]['student']['data']['name']
+ '(' + hiredLockers[i]['student']['data']['class'] + ') ]'
+' <a  class="teal-text right" href="#" onClick="unhireLocker(' + hiredLockers[i]['locker'] + ');"> zurückgeben </a></div></li>';

}
content += '</ul>';
document.getElementById("lockerList").innerHTML = content;
}

/**
*show student search bar
*@param active locker
*/
function showStudent(chosenLockerId, chosenLockerLocation) {
content = "Vergebe Schließfach Nr. " + chosenLockerId + " | Standort: " + chosenLockerLocation;
lockerToHire = chosenLockerId; 
document.getElementById("lockerList").innerHTML = content;
document.getElementById("lockerList").classList.add("teal-text");
document.getElementById("student").style.visibility='visible';
}




</script>
<script type="text/javascript">
requestReady = true;
</script>

