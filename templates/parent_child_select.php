<?php

//$model = Model::getInstance();
$data = $this->getDataForView();
$students = $data['children'];
$user = $data['user'];
include("header.php");

?>

<div class="container">
    
    <div class="card">
        <div class="card-content">
            <?php if (count($students) == 0)
            { ?>
                <span class="card-title">
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
						 <i class="material-icons">chevron_left</i>
					</a>
					Bitte registrieren Sie Ihr Kind:
				</span>
				<a style="position: absolute; bottom:20px; right:20px;" class="btn-floating btn-large teal"
                   href="#addstudent"><i class="material-icons">add</i></a>
            <?php }
            else
            { ?>
            <span class="card-title">
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
						 <i class="material-icons">chevron_left</i>
					</a>
					Ihre Kinder:
				</span>
            <a class='btn-floating btn-large teal' style="position: absolute; bottom:80px; right:20px;"
               href='#addstudent'><i class="material-icons">add</i></a>
            <div class="row">
                <ul class="collection col s12">
                    <?php foreach ($students as $child) { ?>
                        <li class="collection-item">
                            <?php echo $child->getSurname() . ", " . $child->getName() . " (Klasse " . $child->getClass() . ")"; ?>
                        </li>
                    <?php } ?>
                </ul>
                <?php } ?>
            <span style="font-size=10px;">Sie benötigen zur Registrierung weiterer Kinder einen Registrierungsschlüssel! <br><a href="#requirekey" class="teal-text text-darken-2">Ich habe keinen Registrierungsschüssel oder kenne das nicht!</a></span>
            
            </div>
        </div>
        <div class="card-action center">
            &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
        </div>
    </div>

</div>
<div id="addstudent" class="modal">
    <div class="modal-content">
        <h4>Schüler hinzufügen</h4>
        <div class="row">
            <span id="student_placeholder"></span>
            <a onclick="addStudent();" class="btn-flat btn-large waves-effect waves-light teal-text col s12">Feld
                                                                                                             hinzufügen
                <i
                        class="material-icons right large">add</i></a>
        </div>
        <a onclick="submitStudentForm();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Schüler hinzufügen</a>
    </div>
</div>

<div id="requirekey" class="modal">
    <div class="modal-content">
        <h4>Registrierungsschlüssel anfordern</h4>
        <div class="row">
		Aus Sicherheitsgründen können Sie Ihre/e Kind/er nur mittels einesSchlüssels registrieren, 
		welchen Sie mit der Anmeldebestätigung erhalten haben. Sie können diesen erneut anfordern.
		Tragen Sie hierzu bitte die geforderten Daten ein.
		Der Schlüssel wird Ihnen dann an die hinterlegte Emailadresse gesendet.
		</div>
		<div class="row">
			<div class="input-field col l4 m4 s4">
				<input id="name" name="name" type="text" class="validate">
				<label for="name" class="truncate">Name des Kindes</label>
			</div>
			<div class="input-field col l4 m4 s4">
				<input id="klasse" name="klasse" type="text" class="validate">
				<label for="klasse" class="truncate">Klasse</label>
			</div>
			<div class="input-field col l4 m6 s4">
				<input id="bday" type="date" name="bday" class="datepicker">
				<label for="date" class="truncate">Geburtstag</label>
			</div>   
        </div>
        <a onclick="submitKeyRequestForm();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Jetzt anfordern</a>
    </div>
</div>


<div id="student_blueprint" style="display:none;">
    <div class="input-field col l12 m12 s12">
        <input id="id" name="id" type="text" class="validate">
        <label for="name" class="truncate">Registrierungscode</label>
    </div>
    
</div>

<?php include("js.php"); ?>

<script type="application/javascript">
    function submitStudentForm() {
		var url_param = "?console&type=addstudent";
        var studentNodes = document.getElementsByClassName("student_instance");
		var students = [];
        var numValidStudents = 0;
        for (var i = 0; i < studentNodes.length; i++) {
            var student = studentNodes[i];
            var id = student.childNodes[1].childNodes[1].value;
			//console.info(student.childNodes[1].childNodes[1].value);
            if (id == "" )
                continue;
            //students.push(id);
            numValidStudents++;
            url_param += "&students[]=" + id;
        }
		//url_param += "&students[]=" + students;
		console.info(url_param);
        if (numValidStudents == 0) {// No valid Students...
            Materialize.toast("Bitte geben sie mindestens einen Schüler an.",4000);
            return;
        }

        $.get("index.php" + url_param, function (data) {
            try {
                //var myData = JSON.parse(data);
                if (data.success) {
                    location.reload();
                }
                else { // oh no! ;-;
					var notifications = data.notifications; //myData['notifications'];
                    notifications.forEach(function (data) {
                        Materialize.toast(data, 4000);
                    });
                }
            } catch (e) {
                Materialize.toast('Interner Server Fehler!');
                console.error(e);
                console.info('Request: ' + url_param);
                console.info('Response: ' + data);
            }
        });


    }

    var counter = 0;
	function submitKeyRequestForm() {
	var url_param = "?console&type=requestkey";	
	name = $('#name').val();
	klasse = $('#klasse').val(); 
	bday = $('#bday').val();
	userEmail = "<?php echo $user->getEmail(); ?>";
	if (name == "" || klasse == "" || bday == "") {
		Materialize.toast("Bitte geben sie die Daten vollständig ein.",4000);
		} else {
		$.post("index.php" + url_param, {email:userEmail,student:name,kl:klasse,dob:bday},function (data) {
            try {
                //var myData = JSON.parse(data);
				//console.info(data);
                if (data.success) {
                    //location.reload();
					Materialize.toast("Ihre Anfrage wird bearbeitet!", 4000);
					//reset form
					$('#name').val('');
					$('#name').blur();
					$('#klasse').val('');
					$('#klasse').blur();
					$('#bday').val('');
					$('#bday').blur();
					$('#requirekey').trigger('reset');
					$('#requirekey').modal('close')
                }
                else { // oh no! ;-;
					var notifications = data.notifications; //myData['notifications'];
                    notifications.forEach(function (data) {
                        Materialize.toast(data, 4000);
                    });
                }
            } catch (e) {
                Materialize.toast('Interner Server Fehler!',4000);
                console.error(e);
                console.info('Request: ' + url_param);
                console.info('Response: ' + data);
            }
        });
		}
	
	}
    function addStudent() {
        counter++;
        if (counter <= 100) {
            var parent = document.getElementById('student_blueprint');
            if (parent == null)
                return; // not in parent view?
            var clonedNode = parent.cloneNode(true);
            clonedNode.id = ''; // reset id name of clone
            clonedNode.style.display = 'block'; // remove display: none; from clone
            clonedNode.className = 'student_instance';
            var childNodes = clonedNode.childNodes;

            for (var i = 0; i < childNodes.length; i++) {
                var childNode = childNodes[i];

                var nodeName = childNode.name;
                if (nodeName)
                    childNode.name = nodeName + "[" + counter + "]";
            }


            var insertHere = document.getElementById('student_placeholder');
            insertHere.parentNode.insertBefore(clonedNode, insertHere);
        }

        initDatepick();
    }

    $(document).ready(function () {

        addStudent(); // -> create one default student field
    });

</script>

</body>
</html>
