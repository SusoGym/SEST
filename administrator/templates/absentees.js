var xhttp = new XMLHttpRequest();
var content = "";
var admin = false;
var data;
//var activeInfoDiv = null;
var activeElement = null; // must be the pupil id
//var activeAbsenceDiv = null;
//var requestId = null;
var requestReady = null;
var leaveOfAbsence = null;
var activeRequest = null;
//var activeId = null;
//var pupilListData = []; //needed to keep pupil list after new request but will never be changed until new search request is triggered be reduced 
var searchList = []; //needed to keep pupil list after new request but will never be changed until new search request is triggered be reduced 
//var absentees = [];
//var missingExcuses = [];
var studentList = []; // contains all students being shown (either absent or excuse missing
var addToAbsence = null;
xhttp.addEventListener('load', function(event) {
	content = "";
	if (this.responseText) {
		//console.log(this.responseText);
		try{
			data = $.parseJSON(this.responseText);
			
			//catch timeout an reload page (back to login)
				if (null != data['time']  && data['time'] === "out") {
					Materialize.toast(data['message'],"4000");
					location.reload();
					}
			}catch (e) {
                return; // no valid response
                }
		if (data['status'] == "email_sent"){
				//not valid here, yet
				//Materialize.toast(data['message'],"2000");
				document.getElementById("searchtitle").innerHTML = "Bitte Anfrage auswählen";
				deleteRequest(data['id']);
			} else if (data['status'] == "loaEntered") {
				//leave of absence entered
				Materialize.toast("Beurlaubung erfasst","2000");
				//Remove element from searchList
				document.getElementById('p'+data['id']).remove();
				activeElement = null;
				$('#leaveofabsence').modal('close');
				createStudentList($.parseJSON(data['studentList']));
				createLoaList();
			}else if (data['status'] == "absenceEntered") {
				//a new absence was entered
				Materialize.toast("Abwesenheit erfasst","2000");
				//Remove element from searchList
				document.getElementById('p'+data['id']).remove();
				activeElement = null;
				$('#markabsent').modal('close');
				//refresh studentList
				createStudentList($.parseJSON(data['studentList']));
				createAbsenteeList();
				createMissingExcuseList();
			} else if (data['status'] == "absenceExcused") {
				//a new excuse for an existing absence was entered
				Materialize.toast("Entschuldigung erfasst","2000");
				activeElement = null;
				document.getElementById("row"+data['aid']).remove();
				//refresh studentList
				createStudentList($.parseJSON(data['studentList']));
				createAbsenteeList();
				createMissingExcuseList();
				$('#markexcuse').modal('close');					
			}else if (data['status'] == "absenceDeleted") {
				//existing absence was deleted
				Materialize.toast("Abwesenheitsdaten gelöscht","2000");
				document.getElementById("row"+data['aid']).remove();
				//delete from studentList
				studentList.splice(studentList.findIndex(dataset => dataset.id == data['aid']) ,1);
				$('#deleteexcuse').modal('close');
				activeElement = null;
			}else if (data['status'] == "absenceEdited") {
				//console.log(data);
				Materialize.toast("Daten geändert" ,"2000");
				activeElement = null;
				$('#editabsence').modal('close');
				document.getElementById("row"+data['aid']).remove();
				//refresh studentList
				createStudentList($.parseJSON(data['studentList']));
				createAbsenteeList();
				createMissingExcuseList();
				} else if (data['status'] == "loaEdited") {
				//console.log(data);
				Materialize.toast("Daten der Beurlaubung geändert" ,"2000");
				activeElement = null;
				$('#editabsence').modal('close');
				//refresh studentList
				createStudentList($.parseJSON(data['studentList']));
				createLoaList();
								
			}else if (data['status'] == "previousDayAbsence") {
				//Materialize.toast("previousdatechecked " ,"2000");
				//console.log(data);
				
				if (null != data['aid'] ) { 
					//console.log(data);
					document.getElementById('previousAbsence').innerHTML = "Fehltag am Tag davor - Absenz wird verlängert";	
					addToAbsence = data['aid'];
					document.getElementById('comment').style.display = "none";
					} else {
					addToAbsence = null;
					document.getElementById('previousAbsence').innerHTML = "";
					document.getElementById('comment').style.display = "block"					
					}
				
				
			}else if (data['status'] == "absenceProlonged") {
				Materialize.toast("Zeitraum verlängert","2000");
				//add to absentee List
				//refresh studentList
				createStudentList($.parseJSON(data['studentList']));
				createAbsenteeList();
				createMissingExcuseList();
				$('#markabsent').modal('close');
				
			} else if (data['status'] == "pdfready"){
				window.open('templates/pdfabsentees.php');		
			} else if  (data['status'] == "error") {
				Materialize.toast(data['message'],"4000");
			} else {
				// enter the search request result into an array to keep it after further requests
				searchList = [];
				absenteeList = studentList.filter(dta => dta.type == "absent");
				for(x=0;x<data.length;x++) {
					if (absenteeList.findIndex(dta => dta.id === data[x]['id']) == -1){
						//only enter the students not in absenteelist
						searchList.push(data[x]);	
						}
						
					}
				//console.log(searchList);
				activeElement = null;
				$('#pupils').html(createResultList(searchList));
			}
		} 
	
} );

//Trigger the jquery keyup function
$("input[id=pupil-input]").keyup(function(){
	if (null != requestReady) {
	$('#pupils').html('');
	partname = $("input[id=pupil-input]").val();
	if (partname.length > 0) {
	//send request to webserver
	//console.log("send to ?type=pupilmgt&console&partname="+partname+"&absence="+absenceEntry);
	xhttp.open("POST", "?type=pupilmgt&console&partname="+partname, true);
	xhttp.send();
	}
	}
	
});

/**
* create studentList ( the list which combines all shown pupils either absent or excuse missing - not search display, though
* @param json array 
*/
function createStudentList(jsonData) {
	//console.log(jsonData);
	
	//for IE 11 - other sultion needed array push not working

studentList = [];
for(x=0;x<jsonData.length;x++) {
	studentList.push(jsonData[x]);		
	}
}




/**
* create list of today's absentees old
*/
function createAbsenteeList_old() {
//delete list elements
this.row = document.getElementById("row_blueprint");
this.pupil = document.getElementById("pupil_blueprint");

absentees = studentList.filter(data => data.type == "absent");
x=0;
absentees.forEach(function(element) {
modeIcon = null;
iconEnabled = true;	
//check for status icons
if(element['adminMeldung'] != 0) {
	officeNotice = true;
	rowcolor = "orange-text";
	if (element['adminMeldungTyp'] == 0) {
		stateIcon = "contact_phone";	
		} else{
		stateIcon = "contact_mail";
		}
	if(element['beurlaubt'] == 1) {
		stateIcon = "flight";
		}
	} else if (element['lehrerMeldung'] != 0) {
	stateIcon = "school";
	rowcolor = "red-text";	
	} else if (element['elternMeldung'] != 0) {
	stateIcon = "supervisor_account";
	rowcolor = "orange-text";	
	}
if ( element['entschuldigt'] != "0000-00-00" || element['beurlaubt'] == 1) {
	rowcolor = "green-text";
	if (element['beurlaubt'] == 1) iconEnabled = false;	
	} else {
	rowcolor =	rowcolor;
	}
if (null != document.getElementById("row"+element['absenceId'])) {
	document.getElementById("row"+element['absenceId']).remove();
	}
this.rowClone = this.row.cloneNode(true);
this.rowClone.id = "row"+element['absenceId'];
this.rowClone.className = rowcolor;
this.rowClone.innerHTML = 
		'<span   class="' + rowcolor+' ">'
		+element['name'] + ' ('
		+ element['klasse'] 
		+')'
		+'<i class="material-icons left">'+stateIcon+'</i>';
this.rowClone.innerHTML += '</span>';
if (element['beurlaubt'] == 0 )
	this.rowClone.innerHTML += '<a  href="#" onClick="confirmDelete('+element['absenceId']+')" class="black-text "><i class="material-icons right">delete</i></a>';
if(iconEnabled) {
this.rowClone.innerHTML += '<a  href="#" onClick="excuseNotice('+element['absenceId']+')" class="black-text"><i class="material-icons right">playlist_add_check</i></a>';
this.rowClone.innerHTML += '<a  href="#" onClick="editAbsence('+element['absenceId']+')" class="black-text"><i class="material-icons right">edit</i></a>';
}
this.rowClone.innerHTML +='<hr/>';
x++;
document.getElementById('absenteelist').appendChild(this.rowClone);
this.rowClone.style.display="block";
});



}

/**
* create absentee list admin view
*/
function createAbsenteeList() {
//delete list elements
document.getElementById("absenteelist").innerHTML = "";

this.row = document.getElementById("row_blueprint");
rowcolor = "black-text";
x=0;
absentees = studentList.filter(data => data.type == "absent");
//console.log(absentees);
absentees.forEach(function(element) {
modeIcon = null;
iconEnabled = true;	

stateIcon = "healing";
rowcolor = (element['entschuldigt'] == "0000-00-00") ? "red-text" : "green-text";
if (element['beurlaubt'] == 1 ) rowcolor = "green-text";
iconEnabled = true;	
this.rowClone = this.row.cloneNode(true);
this.rowClone.id = "row"+element['absenceId'];
this.rowClone.className = rowcolor;	
//console.log(this.rowClone.childNodes);
this.listheader = this.rowClone.childNodes[1];
this.listheader.name = "done";
this.listheader.id = "listheader"+element['absenceId'];
this.listheader.innerHTML = '<span class=" ' + rowcolor+' ">'
		+element['name'] + ' ('
		+ element['klasse'] 
		+')';
if (element['adminMeldung'] != "0") {
		if (element['beurlaubt'] == 1 ) {
			listheader.innerHTML +=	'<i class="material-icons left">flight</i>';
		} else {
			if (element['adminMeldungTyp'] == 0) {
				listheader.innerHTML +=	'<i class="material-icons left">contact_phone</i>';
			} else {
				listheader.innerHTML += '<i class="material-icons left">contact_mail</i>';
			}	
		}
		
	}
if (element['lehrerMeldung'] != "0") {
			listheader.innerHTML += '<i class="material-icons left">school</i>';
	}
if (element['elternMeldung'] != "0") {
			listheader.innerHTML += '<i class="material-icons left">supervisor_account</i>';
	}
this.listbody = this.rowClone.childNodes[3];
this.listbody.name = "done";
this.listbody.id = "listbody"+element['absenceId'];
this.listbody.className += " black-text";
this.listbody.innerHTML = showAbsenceDetails(element['absenceId']);
if(element['beurlaubt'] == 0 ) 
	{
	this.listbody.innerHTML += '<a  href="#" onClick="confirmDelete('+element['absenceId']+')" class="black-text "><i class="material-icons right">delete</i></a>';
	if (element['entschuldigt'] == "0000-00-00") {
		this.listbody.innerHTML += '<a  href="#" onClick="excuseNotice('+element['absenceId']+')" class="black-text"><i class="material-icons right">playlist_add_check</i></a>';
		}
	this.listbody.innerHTML += '<a  href="#" onClick="editAbsence('+element['absenceId']+')" class="black-text"><i class="material-icons right">edit</i></a>';
	}
document.getElementById('absenteelist').appendChild(this.rowClone);
this.rowClone.style.display="block";	

x++;	
});
//MUST INCLUDE SHOWING NO ABSENCES
if (absentees.length == 0) {
document.getElementById("absenteelist").ClassName = "green-text";
document.getElementById("absenteelist").innerHTML = "keine Abwesenheiten";
	
} else {
document.getElementById('printit').style.display = "block";
}
}


/**
* create leave of absence list admin view
*/
function createLoaList() {
//delete list elements
document.getElementById("loalist").innerHTML = "";

this.row = document.getElementById("row_blueprint");
rowcolor = "black-text";
x=0;
loas = studentList.filter(data => data.beurlaubt == 1);
if (loas.length == 0) {
	document.getElementById('loalist').innerHTML = "keine Beurlaubungen";
	} else {
	loas.forEach(function(element) {
	modeIcon = null;
	iconEnabled = true;	
	rowcolor = "green-text";
	iconEnabled = true;	
	this.rowClone = this.row.cloneNode(true);
	this.rowClone.id = "row"+element['absenceId'];
	this.rowClone.className = rowcolor;	
	//console.log(this.rowClone.childNodes);
	this.listheader = this.rowClone.childNodes[1];
	this.listheader.name = "done";
	this.listheader.id = "listheader"+element['absenceId'];
	this.listheader.innerHTML = '<span class=" ' + rowcolor+' ">'
			+element['name'] + ' ('
			+ element['klasse'] 
			+')';
	
	this.listbody = this.rowClone.childNodes[3];
	this.listbody.name = "done";
	this.listbody.id = "listbody"+element['absenceId'];
	this.listbody.className += " black-text";
	this.listbody.innerHTML = showAbsenceDetails(element['absenceId']);
	
		this.listbody.innerHTML += '<a  href="#" onClick="confirmDelete('+element['absenceId']+')" class="black-text "><i class="material-icons right">delete</i></a>';
		this.listbody.innerHTML += '<a  href="#" onClick="editAbsence('+element['absenceId']+')" class="black-text"><i class="material-icons right">edit</i></a>';
	
	document.getElementById('loalist').appendChild(this.rowClone);
	this.rowClone.style.display="block";	

	x++;	
	});
	}

}

/**
* prepare file as basis for PDF creation
*/
function printAbsence() {
xhttp.open("POST", "?type=printabsence", true);
xhttp.send();	

}



/**
* create list of missing excuses  // NOT READY needs seperate blueprint!
*/
function createMissingExcuseList() {
//delete list elements
this.row = document.getElementById("row_blueprint");
this.pupil = document.getElementById("pupil_blueprint");
missingExcuses = studentList.filter(data => data.type == "missingExcuse");
x=0;
missingExcuses.forEach(function(element) {
modeIcon = null;
//check for status icons
if(element['adminMeldung'] != 0) {
	officeNotice = true;
	rowcolor = "orange-text";
	if (element['adminMeldungTyp'] == 0) {
		stateIcon = "contact_phone";	
		} else{
		stateIcon = "contact_mail";
		}	
	} else if (element['lehrerMeldung'] != 0) {
	stateIcon = "school";
	rowcolor = "red-text";	
	} else if (element['elternMeldung'] != 0) {
	stateIcon = "supervisor_account";
	rowcolor = "orange-text";	
	}
if ( element['entschuldigt'] != "0000-00-00") {
	rowcolor = "green-text";	
	} else {
	rowcolor =	rowcolor;
	}
if (null != document.getElementById("row"+element['absenceId'])) {
	document.getElementById("row"+element['absenceId']).remove();
	}
this.rowClone = this.row.cloneNode(true);
this.rowClone.id = "row"+element['absenceId'];
this.rowClone.className = rowcolor;
this.rowClone.innerHTML = 
		'<span' + rowcolor+' ">'
		+element['name'] + ' ('
		+ element['klasse'] 
		+')'
		+'<i class="material-icons left">'+stateIcon+'</i>';
this.rowClone.innerHTML += '</span>';
this.rowClone.innerHTML += '<a  href="#" onClick="excuseNotice('+element['absenceId']+')" class="grey-text"><i class="material-icons right">playlist_add_check</i></a>';
this.rowClone.innerHTML +='<hr/>';
x++;
document.getElementById('missingexcuseslist').appendChild(this.rowClone);
this.rowClone.style.display="block";
});



}

/**
* show list of matches after search
* @param json
*/
function createResultList(dta){
x = 0;
content = "";
dta.forEach(function(element) {
		
		content += '<div id="p'+element['id']+'"  > ';
		if (null != leaveOfAbsence) {
		content += '<a  href="#" onClick="leaveOfAbsenceNotice('+element['id']+')" class="navigation waves-effect waves-light teal-text">';
		} else {
		content += '<a  href="#" onClick="absentNotice('+element['id']+')" class="navigation waves-effect waves-light teal-text">';
		}
		content += element['name']+', ' 
		+ element['vorname'] + '( '
		+ element['klasse'] 
		+')</a></div>'
		//+'<div id="'+element['name']+'"></div></div>';
		
		x++;
		});	
	return content;
}



/**
* trigger showing of the modal Window to mark absence
*/
function absentNotice(elementNr) {
activeElement = elementNr;
$('#markabsent').modal();
$('#markabsent').modal('open');
$('#absenceName').html('<h4>' + searchList.find(result => result.id == activeElement )['name'] + ', ' + searchList.find(result => result.id == activeElement )['vorname'] + '</h4>');
//get absences one day before
checkPreviousDayAbsence();
initDatepick();
}

/**
* trigger showing of the modal Window to enter a leaveOfAbsence
*/
function leaveOfAbsenceNotice(elementNr) {
activeElement = elementNr;
$('#leaveofabsence').modal();
$('#leaveofabsence').modal('open');
$('#loaName').html('<h4>' + searchList.find(result => result.id == activeElement )['name'] + ', ' + searchList.find(result => result.id == activeElement )['vorname'] + '</h4>');
initDatepick();
}

/**
* check previousAbsence
*/
function checkPreviousDayAbsence() {
//console.log("?type=checkprevabs&console&id="+searchList.find(result => result.id == activeElement )['id']+"&date="+formatDateDash(document.getElementById('sickstart').value));
xhttp.open("POST", "?type=checkprevabs&console&id="+searchList.find(result => result.id == activeElement )['id']+"&date="+formatDateDash(document.getElementById('sickstart').value), true);
xhttp.send();	
}

/**
* trigger showing of the modal Window to enter excuse reception
*/
function excuseNotice(elementNr) {
activeElement = elementNr;
activeDataSet = studentList.find( arr => arr.absenceId  == activeElement ); 
this.id = elementNr;
$('#markexcuse').modal();
$('#markexcuse').modal('open');
$('#excuseName').html('<h4>' + activeDataSet['name'] + ' (' + activeDataSet['klasse'] +')</h4>');
$('#absencedata').html(showAbsenceDetails() );
//document.getElementById("saveButton").onclick = function(elementNr) {saveAbsence(elementNr) }; //seems not to be working
//document.getElementById("saveButton").setAttribute("onclick","saveAbsence()"); //NoParameters can be passed here - why?
initDatepick();
}


/**
* edit absence details
*/
function editAbsence(elementNr) {
activeElement = elementNr;
//console.log(activeElement);
activeDataSet = studentList.find( arr => arr.absenceId  == activeElement ); 
$('#editabsence').modal();
$('#editabsence').modal('open');
$('#editName').html('<h4>' + activeDataSet['name'] + ' (' + activeDataSet['klasse'] + ')</h4>');
$('#editdata').html(showAbsenceDetails() );
//Detect the via parameter and set the correct radio to active
if (activeDataSet['adminMeldungTyp'] == 0) {
	document.getElementById('ephone').checked = true;
	} else {
	document.getElementById('email').checked = true;
	}

document.getElementById('esickstart').value = formatDateDot(activeDataSet['beginn']);
document.getElementById('esickend').value = formatDateDot(activeDataSet['ende']);
document.getElementById('ecomment').value = activeDataSet['kommentar'];
document.getElementById('saveButton').onClick = function() {saveEdited();};
initDatepick();
}

/**
* sending the data of a noted absence
*/
function saveAbsence(id){
activeElementDiv = 'p' + activeElement;
sickend = formatDateDash(document.getElementById("sickend").value);
sickstart = formatDateDash(document.getElementById("sickstart").value);
comment = document.getElementById("comment").value;
type = document.querySelector('input[name="via"]:checked').value;
if (null != addToAbsence) {
//console.log("send to ?type=addtoabsence&console&aid="+addToAbsence+"&via="+type+"&end="+sickend);
xhttp.open("POST", "?type=addtoabsence&console&aid="+addToAbsence+"&via="+type+"&end="+sickend, true);
	
} else {
//console.log("send to ?type=markabsent&console&id="+activeElement+"&via="+type+"&start="+sickstart+"&end="+sickend+"&comment="+comment);
xhttp.open("POST", "?type=markabsent&console&id="+activeElement+"&via="+type+"&start="+sickstart+"&end="+sickend+"&comment="+comment, true);
}
xhttp.send();

}

/**
* sending the data of a noted absence
*/
function saveloa(){
activeElementDiv = 'p' + activeElement;
sickend = formatDateDash(document.getElementById("loaend").value);
sickstart = formatDateDash(document.getElementById("loastart").value);
comment = document.getElementById("comment").value;
//console.log("send to ?type=leaveofabsence&console&id="+activeElement+"&start="+sickstart+"&end="+sickend+"&comment="+comment);
xhttp.open("POST", "?type=leaveofabsence&console&id="+activeElement+"&start="+sickstart+"&end="+sickend+"&comment="+comment, true);
xhttp.send();

}

function saveEdited(){
	activeDataSet = studentList.find( arr => arr.absenceId  == activeElement ); 
	xhttp.open("POST", "?type=editabsence&console&aid="+activeDataSet['absenceId']
	+"&id="+activeElement
	+"&start="+formatDateDash(document.getElementById('esickstart').value)
	+"&end="+formatDateDash(document.getElementById('esickend').value)
	+"&ecomment="+document.getElementById('ecomment').value
	+"&loa="+leaveOfAbsence
	+"&evia="+document.querySelector('input[name="evia"]:checked').value, true);
	xhttp.send();	
}
/**
* sending data of noted excuse
*/
function saveAbsenceExcuse() {
activeDataSet = studentList.find( arr => arr.absenceId  == activeElement ); 
excusein = document.getElementById("excusein").value;
comment = document.getElementById("excusecomment").value;
date = excusein.split('.');
excuseindate = date[2] + '-' + date[1] + '-' +date [0];	
//console.log("send to ?type=excuse&console&aid=" + activeDataSet['absenceId'] + "&date=" + excuseindate + "&comment=" + comment);
xhttp.open("POST", "?type=excuse&console&aid=" + activeDataSet['absenceId'] + "&date=" + excuseindate + "&comment=" + comment, true);
xhttp.send();
}


/**
* confirm Delete
* @param nr
*/
function confirmDelete(elementNr) {
activeElement = elementNr;
//find the data of the active absence in the pupilList
activeDataSet = studentList.find( arr => arr.absenceId == activeElement );
$('#deleteexcuse').modal();
$('#deleteexcuse').modal('open');
$('#deleteExcuseName').html('<h5>Abwesenheit von ' + activeDataSet['name'] + ' wirklich löschen?</h5>');	//pupilListData[elementNr]['name']
$('#deletedata').html(showAbsenceDetails() );
}

/**
* delete Absence
*/
function deleteAbsence() {
//find the data of the active absence in the pupilList
activeData = studentList.find( arr => arr.absenceId  == activeElement );
//console.log("?type=deleteabsence&console&aid="+activeDataSet['absenceId']+"&sid="+activeDataSet['id']);	
xhttp.open("POST", "?type=deleteabsence&console&aid="+activeDataSet['absenceId']+"&sid="+activeDataSet['id'], true);
xhttp.send();
}





/**
* create the absence details view
*/
function showAbsenceDetails_old() {
content = "Abwesenheit";
activeDataSet = studentList.find(dataset => dataset.absenceId == activeElement);
if (activeDataSet['ende'] != activeDataSet['beginn']) {
content += " vom <b>" + formatDateDot(activeDataSet['beginn']) + "</b> bis <b>" +formatDateDot(activeDataSet['ende']);	
} else {
content += " am <b>" + formatDateDot(activeDataSet['beginn']) + "</b>";	
}
anzeige = "";
if (activeDataSet['adminMeldung'] != 0) {
anzeige = "Eintrag Sekretariat am: " + formatDateDot(activeDataSet['adminMeldungDatum'])+'<br/>';	
}
if (activeDataSet['lehrerMeldung'] != "0") {
anzeige += "Meldung Lehrer am: " + formatDateDot(activeDataSet['lehrerMeldungDatum'])+'<br/>';	
}
if (activeDataSet['elternMeldung'] != "0") {
anzeige += "Eintrag Eltern am: " + formatDateDot(activeDataSet['elternMeldungDatum']);	
}
content += '<br/>' + anzeige;

return content;	
}


/**
* create the absence details view
*/
function showAbsenceDetails(nr) {
if (nr != undefined) {
elementToCheck = nr;	
} else {
elementToCheck = activeElement;
}
content = "Abwesenheit";
activeDataSet = studentList.find(dataset => dataset.absenceId == elementToCheck);

if (activeDataSet['ende'] != activeDataSet['beginn']) {
content += " vom <b>" + formatDateDot(activeDataSet['beginn']) + "</b> bis <b>" +formatDateDot(activeDataSet['ende'])+'</b>';	
} else {
content += " am <b>" + formatDateDot(activeDataSet['beginn']) + "</b>";	
}
anzeige = "";
if (activeDataSet['adminMeldung'] != 0) {
anzeige = "Eintrag Sekretariat am: <b>" + formatDateDot(activeDataSet['adminMeldungDatum'])+'</b><br/>';	
}
if (activeDataSet['beurlaubt'] == 0) {
	if (activeDataSet['lehrerMeldung'] != "0") {
	anzeige += "Meldung Lehrer am: <b>" + formatDateDot(activeDataSet['lehrerMeldungDatum'])+'</b><br/>';	
	}
	if (activeDataSet['elternMeldung'] != "0") {
	anzeige += "Eintrag Eltern am: <b>" + formatDateDot(activeDataSet['elternMeldungDatum'])+'</b>';	
	}
	if (activeDataSet['entschuldigt'] != "0000-00-00") {
	anzeige += "Entschuldigung am: <b>" + formatDateDot(activeDataSet['entschuldigt'])+'</b>';	
	}	
}

content += '<br/>' + anzeige;

return content;	
}


/**
* turn date format into dd.mm.yyyy
*/
function formatDateDot(datum) {
	timepart = datum.split(" ");
	
	if (timepart.length == 1 ){
	dateparts = datum.split('-');
	newDate = dateparts[2] + '.' + dateparts[1] + '.' + dateparts[0];
	} else {
	dateparts = timepart[0].split('-');
	newDate = dateparts[2] + '.' + dateparts[1] + '.' + dateparts[0] + ' um '+ timepart[1];
	}
	return newDate;
}

/**
* turn date format into dd.mm.yyyy
*/
function formatDateDash(datum) {
	dateparts = datum.split('.');
	newDate = dateparts[2] + '-' + dateparts[1] + '-' + dateparts[0];
	return newDate;
}

/**
* aborting excuse
*/
function abortExcuse() {
$('#markexcuse').modal('close');	
}

/**
* aborting asence
*/
function abortAbsence() {
$('#markabsent').modal('close');	
}

/**
* aborting leaveofabsence
*/
function abortloa() {
$('#leaveofabsence').modal('close');	
}

/**
* aborting Delete
*/
function abortAbsenceDelete() {
$('#deleteexcuse').modal('close');	
}

/**
* aborting Edit
*/
function abortEdit() {
$('#editabsence').modal('close');	
}


/**
* show Datepicker
*/
function initDatepick() {
		
        $('.datepicker').pickadate({
            selectMonths: true,
            selectYears: 20,
			min: -5,
			startdate: new Date(),
            max: 0,
            format: "dd.mm.yyyy",

            labelMonthNext: 'Nächster Monat',
            labelMonthPrev: 'Vorheriger Monat',
            labelMonthSelect: 'Monat wählen',
            labelYearSelect: 'Jahr wählen',
            monthsFull: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
            monthsShort: ['Jan', 'Feb', 'Mär', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
            weekdaysFull: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag'],
            weekdaysShort: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa'],
            weekdaysLetter: ['S', 'M', 'D', 'M', 'D', 'F', 'S'],
            today: 'Heute',
            clear: 'Löschen',
            close: 'Ok',
            firstDay: 1,
            container: 'body'

        });
    }

