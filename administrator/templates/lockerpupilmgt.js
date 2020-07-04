/**********************************************
***javascvript file to manage locker hireout***
**********************************************/



//var xhttp = new XMLHttpRequest();
var content = "";
var lockerToHireId = null;
var lockerToHireNr = null;

var data;
var searchList = []; //needed to keep pupil list after new request but will never be changed until new search request is triggered be reduced 
var studentList = []; // contains all students being shown (either absent or excuse missing
var classList = [];
var isSingle = false;
var singleLessonEntry = null;
var actionData = {};

/*
* handle the Server response
* @param JSON string
*/
function handleServerResponse(data, status) {
	content = "";
    //console.log(data);
    data = $.parseJSON(data);
	if (status != "success") {
					Materialize.toast("Interner Server Fehler", "2000");
				}

	
	if (data['status'] == "hired"){
            Materialize.toast(data['message'],"2000");
            $('#confirm_modal').modal('close'); 
            actionData = {};
           	location.reload(); //a bit rough
		
    } else if ((data['status'] == "returned")) {
        Materialize.toast(data['message'], "2000");
        $('#confirm_modal').modal('close'); 
        actionData = {}; 
        location.reload(); //a bit rough

    }else {
			// enter the search request result into an array to keep it after further requests
			searchList = [];
			searchList = data;
			
			//console.log(searchList);
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
                'type': 'pupilmgt',
                'console': '',
                'partname': partname
            }, function (data,status) {
				handleServerResponse(data, status);
			});
	
	}
	}
	
});


/**
* show list of matches after search
* @param json
*/
function createResultList(dta) {
    x = 0;
    content = "";
    searchList = [];
    dta.forEach(function (element) {
        searchList.push(element);
        if (element['absent'] != true) {
            //console.log(searchList);
            content += '<div id="p' + element['id'] + '"  > ' +
                '<a  href="#" onClick="bookLocker(' + element['id'] + ')" class="navigation waves-effect waves-light teal-text">'
                + element['name'] + ', ' + element['vorname'] + '( '
                + element['klasse']
                + ')</a></div>'
            //+'<div id="'+element['name']+'"></div></div>';
        }
        x++;
    });

    return content;
}

/**
 * 
 * * book the locker
 * @param {any} id
 */
function bookLocker(id) {
    actionData ={"action":"hire","lockerNr":lockerToHireNr,"lockerId":lockerToHireId,"student":id,}
    openConfirmModal(actionData);
}

/**
 * perform the booking action
 * 
 */
function confirmAction(){
    if(actionData['action'] == "hire")
    {
        //booking a locker
        $.post("", {
            'type': 'lockers',
            'console': '',
            'hire': '',
            'lckr': actionData['lockerId'],
            'stdnt': actionData['student']['id']
        }, function (data, status) {
            handleServerResponse(data, status);
        });
    } else if(actionData['action'] == "return")
    {
        //returning a locker
        $.post("", {
            'type': 'lockers',
            'console': '',
            'return': '',
            'lckr': actionData['lockerId']
        }, function (data, status) {
            handleServerResponse(data, status);
        });
    }   

}

/**
 * unhire the locker
 * @param {any} id
 */
function unhireLocker(id) {
    lockerToReturn = lockers['hired'].filter(item => item.id == id);
    actionData ={"action":"return","lockerId":id,"lockerNr":lockerToReturn[0]['locker']}
    openConfirmModal(actionData);
}

/**
 * confirm any action in a modal window
 * @param array
 */
function openConfirmModal(mydata) {
    //open modal
    $('#confirm_modal').modal();
    $('#confirm_modal').modal('open');
    if (mydata['action'] == "hire") {
        activeStudent = searchList.filter(item => item.id == mydata['student'])
        currentStudent = activeStudent[0]['vorname'] + ' '  +activeStudent[0]['name'] + ' (' + activeStudent[0]['klasse']+ ')';
        $('#confirm_header').html("<h3>Ausgabe eines Schließfachs</h3>");
        $('#confirm_content').html("<h5>Schließfach " + mydata['lockerNr'] + " an " + currentStudent + " ausgeben?</h5>") ; 
        actionData = {"action":"hire","lockerNr":mydata['lockerNr'],"lockerId":mydata['lockerId'],"student":activeStudent[0]}; 
    } else if (mydata['action'] == "return") {
        $('#confirm_header').html("<h3>Rückgabe eines Schließfachs</h3>");
        $('#confirm_content').html("<h5>Schließfach " + mydata['lockerNr'] + " zurückgeben?</h5>") ;
        actionData = {"action":"return","lockerId":mydata['lockerId']}; 
    }
}

/**
 * abort any action
 */
function abortAction(){
    $('#confirm_modal').modal('close');  
}
