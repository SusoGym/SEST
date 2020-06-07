var content = "";
var data;
var activeStudent = null;
//var activeInfoDiv = null;
//var activeAbsenceDiv = null;
//var requestId = null;
var requestReady = null;
var activeRequest = null;
//var activeId = null;
//var absenceEntry = null;
var searchList = [];
var hiredLocker = null;
var hiredBooks = null;


function handleServerResponse(data, status)  {
	content = "";
	if (status != "success") {
					Materialize.toast("Interner Server Fehler", "2000");
				}
	data = $.parseJSON(data);
		if (null != data['time']  && data['time'] === "out") {
				Materialize.toast(data['message'],"4000");
				location.reload();
				}
		if (data['status'] == "chosen"){
				//show the data in div
				studentData = $.parseJSON(data['studentdata']);
				activeStudent = studentData['id'];
				showPupilData(studentData);
			} else if (data['status'] == "deleted") {
				//deregistration confirmed
				Materialize.toast(data['message'],"2000");
				//clear info div and hide
				resetInfoDiv();
				//clear search fields
				$('#pupils').html('');
				document.getElementById('pupil-input').value = null;
				activeStudent = null;
			} else if (data['status'] == "returned"){
				Materialize.toast(data['message'],"2000");
				//clear locker div
				document.getElementById('lockerdata').innerHTML = "";
				if (hiredBooks == false) {
					addDeregisterButton();
				}			
				
			}else {
				// show the search result list
				var searchList = [];
				for(x=0;x<data.length;x++) {
						searchList.push(data[x]);
					}
				activeElement = null;
				$('#pupils').html(createResultList(searchList));
			}
	} 

//Trigger the jquery keyup function
$("input[id=pupil-input]").keyup(function(){
	if (null != requestReady) {
	$('#pupils').html('');
	partname = $("input[id=pupil-input]").val();
	if (partname.length > 0) {
	//send request to webserver
	$.post("", {
				'type': 'deregister',
				'console': '',
				'partname': partname
            }, function (data,status) {
				handleServerResponse(data, status);
			});
	}
	}
	
});


/**
 * reset the info div and hide
 */
function resetInfoDiv() {
	document.getElementById('pupildata').innerHTML = "";
	document.getElementById('lockerdata').innerHTML = "";
	document.getElementById('librarydata').innerHTML = "";
	document.getElementById("infocard").style.visibility = "hidden";
	document.getElementById('deregisterButton').innerHTML = "";
	document.getElementById("deregisterButton").style.visibility = "hidden";
	hiredLocker = null;
	hiredBooks = null;
	
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
			
			content += '<a  href="#" onClick="triggerView('+element['id']+')" class="navigation waves-effect waves-light teal-text">';
			
			content += element['name']+', ' 
			+ element['vorname'] + '( '
			+ element['klasse'] 
			+')</a></div>'
			x++;
			});	
		return content;
	}

/**
 * trigger the view of student's data
 * @param  int
 */
function triggerView(id){
	$.post("", {
		'type': 'deregister',
		'console': '',
		'getdata': id
	}, function (data,status) {
		handleServerResponse(data, status);
	});
}

/**
 * show pupil data
 * @param array data
 */
function showPupilData(dta) {
//clear all	
resetInfoDiv();
hiredLocker = false;
hiredBooks = false;
//show card
document.getElementById("infocard").style.visibility = "visible";
//display student basic info
content = '';
header = 'Abmeldung von ' + dta['name'] ;
document.getElementById('title').innerHTML = header;
content += 'Klasse: ' + dta['klasse'] + '<br>';
content += 'ASV-Id: ' + dta['asvid'] + '<br>';
content += 'Geburtstag: ' + dta['bday'] + '<br>';
if (null != dta['parent1'] ||null != dta['parent2']) {
	content += '<b>Eltern:</b> <br>' ;
	if (null != dta['parent1'] ) {
		content += dta['parent1']['fullname'] + ' - ' + dta['parent1']['email'] + '<br>';
		}
	if (null != dta['parent2'] ) {
		content += dta['parent2']['fullname'] + ' - ' + dta['parent2']['email'] + '<br>';
		}
}
document.getElementById('pupildata').innerHTML = content;
//add locker info
if (null != dta['locker']) {
	hiredLocker = true;
	content = '';
	content += '<br><a style="color: #ff0000;"><b>Schließfach vergeben:</b> </br>';
	content += '<i class="material-icons black-text">lock</i> Schließfach-Nr: ' + dta['locker']['id'] + ' gemietet: ' + dta['locker']['hiredate'] + '</a>';
	content += '<br/><button class="btn btn-primary" onClick="returnLocker(' + dta['locker']['id'] + ')" >Schließfach zurückgeben</button>';
	document.getElementById('lockerdata').innerHTML = content;	
	}
//add library info
if (null != dta['library']) {
	hiredBooks = true;
	libDat = $.parseJSON(dta['library']);
	content = '';
	content += '<ul><a style="color: #ff0000;"><b>Bücher aus der Schülerbibliothek entliehen:</b> </br>';
	libDat.forEach(function (item, index) {
		content += '<li ><i class="material-icons black-text">library_books</i>' + item['title'] + ' (' + item['author'] + ') - Barcode: ' 
		+  item['barcode'] + ' [ fällig: ' + item['due']['duedate'] + ' ]<br/></li>';
	  });
	content += '</ul><b>Abmeldung erst möglich nach Rückgabe der Bücher!';
	document.getElementById('librarydata').innerHTML = content;	
	}
//add a button to deregister the student
if (hiredLocker === false && hiredBooks === false) {
	addDeregisterButton();
	}

}


/**
 * trigger return of locker
 * @param int id 
 */
function returnLocker(id) {
	$.post("", {
		'type': 'lockers',
		'return': '',
		'lckr': id
	}, function (data,status) {
		handleServerResponse(data, status);
	});

}

/**
* confirm deregistration
* @param nr
*/
function confirmDelete(elementNr) {
	activeElement = elementNr;
	$('#deregistration').modal();
	$('#deregistration').modal('open');
	$('#deregistrationText').html('<h5>Schüler*in wirklich abmelden? Alle Daten werden gelöscht.</h5>');	
		
	}


/**
 * deregister student, i.e. send a signal to remove the student from the database
  */
function deregisterStudent() {
	$('#deregistration').modal('close');
	$.post("", {
		'type': 'pupilmgt',
		'console':'',
		'dereg': activeStudent
	}, function (data,status) {
		handleServerResponse(data, status);
	});
}


/**
 * abort deregistration
 */
function abortDeregistration() {
	$('#deregistration').modal();
	$('#deregistration').modal('close');	
}


/**
 * add deregister Button
 */
function addDeregisterButton() {
	//add deregister Button if no librarybooks 
	btn = '<button class="btn btn-primary" onClick="confirmDelete(' + activeStudent + ')">Schüler*in abmelden</button>';
	document.getElementById('deregisterButton').style.visibility = "visible";
	document.getElementById('deregisterButton').innerHTML = btn;
}











