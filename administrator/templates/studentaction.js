/**
Javascript file to support view on main page when 
students need to be deleteted but books from the library or lockers are still due 
 */
var studentData = [];
var studentToDeleteId = null; //used to keep student whose locker is deleted or who is deleted alive
var lockerToReturnId = null; //used to keep lockerId alive
var elemenAtWork = null; //used to keep current element from list alive - all three needed after confirmation

/*
* create a list of the students
for which an action is needed
*/
function createStudentActionRequiredList() {

document.getElementById("studentlist").innerHTML = "";
this.row = document.getElementById("row_blueprint");
rowcolor = "black-text";
x=0;
studentData.forEach(function(element) {
modeIcon = null;
iconEnabled = true;	
stateIcon = "caution";
rowcolor = "teal-text";
iconEnabled = true;	
this.rowClone = this.row.cloneNode(true);
this.rowClone.id = "row"+element['id'];
this.rowClone.className = rowcolor;	
this.listheader = this.rowClone.childNodes[1];
this.listheader.name = "done";
this.listheader.id = "listheader"+element['id'];
this.listheader.innerHTML = '<span class=" ' + rowcolor+' ">'
		+element['name'] + ' ('
		+ element['klasse'] 
        +')';
this.listbody = this.rowClone.childNodes[3];
this.listbody.name = "done";
this.listbody.id = "listbody"+element['id'];
this.listbody.className += " black-text";
    //div with student data
    this.studentInfoClone = this.listbody.cloneNode(true);
    this.studentInfoClone.id = "studentDiv"+element['id'];
    this.studentInfoDiv = this.studentInfoClone.childNodes[3];
    this.studentInfoDiv.name = "done";
    this.studentInfoDiv.id = "studentInfoDiv" + element['id'];
    this.studentInfoDiv.innerHTML = '<b>ASVID: </b>' + element['asvid'] + 
            '<br/><b>Geburtstag: </b>' + element['bday'];
document.getElementById('studentlist').appendChild(this.rowClone);
document.getElementById('listbody' + element['id']).appendChild(this.studentInfoDiv);
    if (null != element['parent1'] || null != element['parent2']) {
        //div with students parentsData
        this.parentInfoClone = this.listbody.cloneNode(true);
        this.parentInfoClone.id = "parentDiv"+element['id'];
        this.parentInfoDiv = this.parentInfoClone.childNodes[3];
        this.parentInfoDiv.name = "done";
        this.parentInfoDiv.id = "parentInfoDiv" + element['id'];
        parentInfo = '<p><b>Eltern:</b> </p>' ;
        if (null != element['parent1'] ) {
        parentInfo += '</p>' + element['parent1']['fullname'] + ' - ' + element['parent1']['email'] + '</p>';
        }
        if (null != element['parent2'] ) {
        parentInfo += '</p>' + element['parent2']['fullname'] + ' - ' + element['parent2']['email'] + '</p>' ;
        }
        this.parentInfoDiv.innerHTML = parentInfo; 
        document.getElementById('listbody' + element['id']).appendChild(this.parentInfoDiv);
    }
    //check if student has any lockers or library books due
    if (checkForRemoveReady(element) == true) {
        // Remove button can be added
    } else {
        //check if student has hired a locker
        if (null != element['locker'] ) {
            //div with locker data
            this.lockerInfoClone = this.listbody.cloneNode(true);
            this.lockerInfoClone.id = "lockerDiv"+element['id'];
            this.lockerInfoDiv = this.lockerInfoClone.childNodes[3];
            this.lockerInfoDiv.name = "done";
            this.lockerInfoDiv.id = "lockerInfoDiv" + element['id'];
            lockerInfo = '<br/><p><b class="red-text">Schließfach vergeben:</b></p>' +
            '<p class="red-text"><i class="material-icons black-text">lock</i>Schließfach Nr: ' + element['locker']['id'] + ' (' 
            + element['locker']['location'] + ') vergeben am: ' + element['locker']['hiredate'] + '</p>' +
            '<p><button class="btn btn-primary" onClick="confirmReturnLocker(' + x +')" >Schließfach zurückgeben</button></p>';
            this.lockerInfoDiv.innerHTML = lockerInfo;
            document.getElementById('listbody' + element['id']).appendChild(this.lockerInfoDiv);
        }
        //check if books are available
        if(null != element['library']) {
            //div with library data
            this.libraryInfoClone = this.listbody.cloneNode(true);
            this.libraryInfoClone.id = "libraryDiv"+element['id'];
            this.libraryInfoDiv = this.libraryInfoClone.childNodes[3];
            this.libraryInfoDiv.name = "done";
            this.libraryInfoDiv.id = "libraryInfoDiv" + element['id'];
            libraryInfo = '<br/><p><b class="red-text">Bücher aus Schülerbibliothek verliehen:</b></p>';
            dueBooks = [];
            dueBooks = $.parseJSON(element['library']);
            dueBooks.forEach(function(item) {
                libraryInfo += '<p class="red-text"><i class="material-icons black-text">library_books</i>' 
                + item['title'] + ' (' + item['author'] + ') - Barcode: ' 
		        + item['barcode'] + ' [ fällig: ' + item['due']['duedate']  + '</p>'
                });
                libraryInfo += '<p class="red-text"><b>Abmeldung erst möglich nach Rückgabe der Bücher!</b></p>';
            this.libraryInfoDiv.innerHTML = libraryInfo;
            document.getElementById('listbody' + element['id']).appendChild(this.libraryInfoDiv);
        }

    }
this.rowClone.style.display="block";
x++;
});

}


/**
 * confirm returning of locker
 * @param {int} elementNr //ordinal of the action list
 */
function confirmReturnLocker(elementNr) {
    studentToDeleteId = studentData[elementNr]['id'] ;
    lockerToReturnId = studentData[elementNr]['locker']['id'];
    elementAtWorkId = elementNr;
	$('#returnLocker').modal();
	$('#returnLocker').modal('open');
    $('#returnLockerText').html('<h5>Schließfach wirklich zurückgeben?</h5>');
    
}




/**
 * trigger return of locker
 */
function returnLocker() {
	$.post("", {
        'type': 'lockers',
		'return': '',
		'lckr': lockerToReturnId
	}, function (data,status) {
        data = $.parseJSON(data);
        if(data['status'] == "returned") {
            Materialize.toast(data['message'],"2000");
            //delete the div where locker data have been displayed
            removeLockerDiv = document.getElementById('lockerInfoDiv' + studentToDeleteId);
            parentDiv = removeLockerDiv.parentNode;
            parentDiv.removeChild(removeLockerDiv);  
            //now we need to set locker to null!!!
            activeStudent = studentData[elementAtWorkId];
            studentData[elementAtWorkId]['locker'] = null;
            studentToDeleteId = null;
            lockerToReturnId = null;
            elementAtWorkId = null;
            $('#returnLocker').modal();
	        $('#returnLocker').modal('close');	
            checkForRemoveReady(activeStudent);
        }
        
	});

}

/**
* confirm deregistration
* @param nr
*/
function confirmDelete(id) {
    studentToDeleteId = id ;
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
		'dereg': studentToDeleteId
	}, function (data,status) {
        data = $.parseJSON(data);
        if (data['status'] == "deleted") {
            Materialize.toast(data['message'],"2000");
            //remove List line from the document
            listLine = document.getElementById('row' + studentToDeleteId);
            parentDiv = listLine.parentNode;
            parentDiv.removeChild(listLine);  
            studentToDeleteId = null;
            //console.log(studentData);
        }
        
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
 * abort locker returning process
 */
function abortReturnLocker() {
	$('#returnLocker').modal();
	$('#returnLocker').modal('close');	
}

/**
 * check if student is remove ready
 * once there are no lockers 
 * @param array activeStudent
 */
function checkForRemoveReady(activeStudent) {
if (activeStudent['library'] == null && activeStudent['locker'] == null) {
    //locker has been returned and no books are due -> student can be removed
    addRemoveButton(activeStudent);
    return true;
} else {
    return false;
}

}


/**
 * add a student remove button to the document
 * @param array activeStudent
 */
function addRemoveButton(activeStudent){
    this.removeButtonClone = this.listbody.cloneNode(true);
    this.removeButtonClone.id = "lockerDiv" + activeStudent['id'];
    this.removeButtonDiv = this.removeButtonClone.childNodes[3];
    this.removeButtonDiv.name = "done";
    this.removeButtonDiv.id = "removeButtonDiv" + activeStudent['id'];
    removeButton = '<br/><p><button class="btn btn-primary" onClick="confirmDelete(' + activeStudent['id'] + ')" >Schüler*in löschen</button></p>';
    this.removeButtonDiv.innerHTML = removeButton;
    document.getElementById('listbody' + activeStudent['id']).appendChild(this.removeButtonDiv);
}




