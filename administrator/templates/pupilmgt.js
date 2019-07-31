var xhttp = new XMLHttpRequest();
var content = "";
var data;

var activeInfoDiv = null;
var activeAbsenceDiv = null;
var requestId = null;
var requestReady = null;
var activeRequest = null;
var activeId = null;
var absenceEntry = null;
xhttp.addEventListener('load', function(event) {
	content = "";
	
	if (this.responseText) {
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
				Materialize.toast(data['message'],"2000");
				document.getElementById("searchtitle").innerHTML = "Bitte Anfrage auswählen";
				
				deleteRequest(data['id']);
			} else if (data['status'] == "request_deleted") {
				activeRequest = null;
				Materialize.toast(data['message'],"2000");
				//delete request card
				document.getElementById("request"+data['id']).remove();
				
			}else if  (data['status'] == "error") {
				Materialize.toast(data['message'],"4000");
			}else {
				list = createResultList(data);
				$('#pupils').html(list);
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
	xhttp.open("POST", "?type=pupilmgt&console&partname="+partname+"&absence="+absenceEntry, true);
	xhttp.send();
	}
	}
	
});


function createResultList(data){
x = 0;
content = "";
data.forEach(function(element) {
		
		content += '<div id="p'+element['id']+'" > '+
		'<a  href="#" onClick="showDetails('+x+')" class="navigation waves-effect waves-light teal-text">'
		+element['name']+', ' 
		+ element['vorname'] + '( '
		+ element['klasse'] 
		+')</a></div>'
		//+'<div id="'+element['name']+'"></div></div>';
		
		x++;
		});	
	return content;
}

function showDetails(elementNr) {
//delete activeDiv
if (null != activeInfoDiv) activeInfoDiv.remove();
if (null != activeAbsenceDiv) activeAbsenceDiv.remove();
//create info Div
if (null == absenceEntry) {
new infoDiv(elementNr);
} else {
new absenceManageDiv(elementNr);	
}
}

/**
* creates an info div
* @ param int
*/
function infoDiv(elementNr){
	console.info(data);
this.parentDiv = 'p'+data[elementNr]['id'];
this.infoDiv = document.createElement("div");
this.infoDiv.id = 'info'+data[elementNr]['id'];
this.infoDiv.className += "card teal lighten-5 black-text ";
document.getElementById(this.parentDiv).appendChild(this.infoDiv);
//create card content div
	this.content = document.createElement("div");
	this.content.id = this.infoDiv.id + "_content";
	this.content.classname += "card-content white-text"
	//place content div into card div
	this.infoDiv.appendChild(this.content);
this.dob = document.createElement("div");
this.dob.innerHTML = "Geburtsdatum: " +data[elementNr]['dob'];
this.asvId = document.createElement("div");
this.asvId.innerHTML = "ASV-ID: "+data[elementNr]['asvId'];
this.eId = document.createElement("div");
this.eId.innerHTML = "Eltern: "+data[elementNr]['parent'] + " (ElternId: " 
    + data[elementNr]['eid'] + ") - Email: " + data[elementNr]['mail'] +
    "<br/>" + data[elementNr]['parent2'] + " (ElternId: "
    + data[elementNr]['eid2'] + ") - Email: " + data[elementNr]['mail2'];

this.content.appendChild(this.dob);
this.content.appendChild(this.asvId);
this.content.appendChild(this.eId);

if (null != requestId) {
this.infoDiv.action = document.createElement("div");
this.infoDiv.action.id = this.infoDiv.id + "_action"
this.infoDiv.action.className += "card-action teal";
//place action div into content div
this.infoDiv.appendChild(this.infoDiv.action);
this.actionb = new ActionButton("transmit","ÜBERNEHMEN",this.infoDiv.action.id,elementNr)
//add relevant Data to ActionButton
this.actionb.actionButton.data.push(data[elementNr]['name'] 
									+ ', '+ data[elementNr]['vorname'] 
									+ ' (' + data[elementNr]['klasse'] + ')'); //Student Name in String
this.actionb.actionButton.data.push(data[elementNr].id); //pupil Nr
this.actionb.actionButton.data.push(this.infoDiv.id);
}
activeInfoDiv = this.infoDiv;	
}

/**
* class manage Absences
* @param int
*/
/*
function absenceManageDiv(elementNr) {
this.parentDiv = 'p'+data[elementNr]['id'];
this.absenceDiv = document.createElement("div");
this.absenceDiv.id = 'absence'+data[elementNr]['id'];
this.absenceDiv.className += "card white black-text ";
document.getElementById(this.parentDiv).appendChild(this.absenceDiv);	
//create card content div
	this.content = document.createElement("div");
	this.content.id = this.absenceDiv.id + "_content";
	this.content.classname += "card-content white-text"
	//place content div into card div
	this.absenceDiv.appendChild(this.content);
	//insert Buttons manage absences
	this.absencePhone = document.createElement("span");
	this.absencePhone.id = "absencePhone";
	this.absencePhone.innerHTML = '<a class="btn-flat blue" href="#" text="telefonisch" onClick="markabsent(1)" ><i class="material-icons">phone</i></a>&nbsp;&nbsp;';
	this.content.appendChild(this.absencePhone);
	this.absenceMail = document.createElement("span");
	this.absenceMail.id = "absenceMail";
	this.absenceMail.innerHTML = '<a  class="btn-flat orange " href="#" text="per Mail" onClick="markabsent(2)" ><i class="material-icons">mail</i></a>&nbsp;&nbsp;';
	this.content.appendChild(this.absenceMail);
	this.excuse = document.createElement("span");
	this.excuse.id = "excuse";
	this.excuse.innerHTML = '<a  class="btn-flat green " href="#" text="entschuldigt" onClick="markabsent(3)" ><i class="material-icons">check</i></a>';
	this.content.appendChild(this.excuse);
activeAbsenceDiv = this.absenceDiv;
}

*/

/**
* class ActionButton
* creates a button/link in a Material Design Card Action div
* @param String actionName
* @param String Id der Karte
*/
function ActionButton(actionName, caption,cardId,elementNr) {

this.studentData = null;
if (null != data) {this.studentData = data;}
this.actionButton = document.createElement("a");
this.actionButton.data = [];
this.actionButton.cardId = cardId;
this.actionButton.actionName = actionName;
this.actionButton.id = cardId + "_" + actionName;
this.actionButton.href="#";
this.actionButton.innerHTML = caption;
this.actionButton.className += "card-action";
this.actionButton.onclick = this.performAction;


//add Button to cards Action field
document.getElementById(this.actionButton.cardId).appendChild(this.actionButton);

}



ActionButton.prototype.performAction =  function() {
	switch(this.actionName) {
	case "transmit":
	//send Student Data to request Card
	receivePupilId(1,this.data); //this.data = data of actionButton
	//delete Info Div
	activeInfoDiv = null;
	document.getElementById(this.data[2]).remove();
	$("input[id=pupil-input]").val('');
	$('#pupils').html('');
	break;
	case "sendmail":
	//needs call to server to send pupilId and parentId
	
	linkt = this.data[3] +"&console&pupil="+this.data[1]+"&request="+this.data[4];
	
	xhttp.open("POST", linkt, true);
	xhttp.send();	
	break;
	default:
	break;
	}
}


/**
* pass id of request Card to student search routine
* @param int request id
*/
function passRequestId(id) {
	
	$("input[id=pupil-input]").val("");
	if (null != activeInfoDiv) activeInfoDiv.remove(); //deletes Student info data in student list
	//delete pupil info and button - prevents double entries in request card
	requestCard = "request" + id;
	if (null != document.getElementById(requestCard + "_requested-pupil") ) 
			document.getElementById(requestCard + "_requested-pupil").remove();
	if (null != document.getElementById(requestCard + "_action") )
		document.getElementById(requestCard + "_action").remove();
	$('#pupils').html("");
	requestId = id;
	requestReady = true;
	if (null != activeRequest) document.getElementById("request"+activeRequest).className = "card greylighten-4 black-text "; 
	document.getElementById("request"+id).className = "card teal lighten-5 ";
	activeRequest = id;
	$("span[id=searchtitle]").html("Bearbeite AnfrageID: " + requestId );	

}

/**
*
* receives student id and request id
*/
function receivePupilId(pupilId,studentData) {
	
//active Request Card
activeCard = document.getElementById("request"+activeRequest)
//create Div with student Info
pupilInfo = document.createElement("div");
pupilInfo.id = activeCard.id+"_requested-pupil";
pupilInfo.innerHTML = studentData[0];
activeCard.appendChild(pupilInfo);
	
//create action div (for links etc)
action = document.createElement("div");
action.id = activeCard.id + "_action"
action.className += "card-action teal";
//place action div into content div
activeCard.appendChild(action);
actionb = new ActionButton("sendmail","ANFRAGE BEANTWORTEN",action.id);
//add informationen to be sent when button is clicked to array (part of action button)
studentData.push("?type=handleregister&console&confirm");
studentData.push(activeRequest); // requestID
actionb.actionButton.data = studentData;	
}

function deleteRequest(id) {
	xhttp.open("POST", "?type=handleregister&console&delete&id="+id, true);
	xhttp.send();
	
}



