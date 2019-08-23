/**********************************************
***javascvript file to manage locker hireout***
**********************************************/



//var xhttp = new XMLHttpRequest();
var content = "";
var lockerToHire;

var data;
var searchList = []; //needed to keep pupil list after new request but will never be changed until new search request is triggered be reduced 
var studentList = []; // contains all students being shown (either absent or excuse missing
var classList = [];
var isSingle = false;
var singleLessonEntry = null;

/*
* handle the Server response
* @param JSON string
*/
function handleServerResponse(data, status) {
	content = "";
    console.log(data);
    data = $.parseJSON(data);
	if (status != "success") {
					Materialize.toast("Interner Server Fehler", "2000");
				}

	
	if (data['status'] == "hired"){
			Materialize.toast(data['message'],"2000");
			location.reload();
		
    } else if ((data['status'] == "returned")) {
        Materialize.toast(data['message'], "2000");
        location.reload();

    }else {
			// enter the search request result into an array to keep it after further requests
			searchList = [];
			searchList = data;
			console.log(searchList);
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
    $.post("", {
        'type': 'lockers',
        'console': '',
        'hire': '',
        'lckr': lockerToHire,
		'stdnt': id
    }, function (data, status) {
        handleServerResponse(data, status);
    });
 


}

/**
 * unhire the locker
 * @param {any} id
 */
function unhireLocker(id) {
    $.post("", {
        'type': 'lockers',
        'console': '',
        'return': '',
        'lckr': id
    }, function (data, status) {
        handleServerResponse(data, status);
    });
}

