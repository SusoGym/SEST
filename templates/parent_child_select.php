<?php

    //$model = Model::getInstance();
	$data = $this->getDataForView();
    $teacherNames = array();
    $teacherObjs = array();
	
    /*
	/** @var Guardian $user */
//    $user = Controller::getUser();
//    $students = array();
//    $children = $user->getChildren();
    /** @var Student $child */
    /*
	foreach ($children as $child)
    {
        $students[$child->getId()]['id'] = $child->getId();
        $students[$child->getId()]['name'] = $child->getFullName();
        $students[$child->getId()]['class'] = $child->getClass();
        $teachers = $child->getTeachers();
        /** @var Teacher $teacher */
//        foreach ($teachers as $teacher)
//        {
//            $students[$child->getId()]['teachers'][$teacher->getId()] = array('id' => $teacher->getId(), 'name' => $teacher->getFullName());
//        }
//    }
//	*/
	$students = $data['children'];
	include("header.php");

?>

<div class="container">

    <pre><?php //echo json_encode($students, JSON_PRETTY_PRINT); ?></pre>

    <div class="card ">
        <div class="card-content">
            <div class="row">
                <div class="col l12 hide-on-med-and-down row">
					<?php if (count($students) == 0) {?>
						<p class="teal-text" style="font-size: 36px;"><span style="font-size: 18px;"><b>Bitte Kinder angeben (Vorname Nachname & Geburtsdatum)</b></span></p>
					<?php } 
					else { ?>
						<p class="teal-text" style="font-size: 36px;"><b>Sie haben folgende Kinder angegeben</b></p>
					<?php foreach ($students as $child) { ?>
							<p class="teal-text" style="font-size: 18px;">
							<?php echo $child->getSurname().", ".$child->getName()." (Klasse ".$child->getClass().")"; ?>
							</p>
						<?php }
						}?>
                    <a class='dropdown-button  col s10 left' href='#' data-activates='students'><br/></a>
                    <a class='dropdown-button fab teal-text col s10 left' style="margin-top: 8px;" href='#addstudent'><i
                                class="material-icons" style="font-size: 48px;">add</i></a>
                    <div class="col s12">
                        &nbsp;
                    </div>

                   


                   


                </div>
               
            </div>
        </div>
        <div class="card-action center">
            
            <br/>
            &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
        </div>
    </div>

</div>
<ul id="mobile-nav" class="side-nav">
    <li>
        <div class="userView">
            <img class="background grey" src="http://materializecss.com/images/office.jpg">
            <img class="circle"
                 src="http://www.motormasters.info/wp-content/uploads/2015/02/dummy-profile-pic-male1.jpg">
            <span class="white-text name"><?php echo $_SESSION['user']['mail']; ?></span>
        </div>
    </li>
    <?php
        include("navbar.php"); ?>
    
</ul>

<div id="addstudent" class="modal">
    <div class="modal-content">
        <h4>Schüler hinzufügen</h4>
        <div class="row">
            <span id="student_placeholder"></span>
            <a onclick="addStudent();" class="btn-flat btn-large waves-effect waves-light teal-text col s12">Feld
                hinzufügen <i class="material-icons right large">add</i></a>
        </div>
        <a onclick="submitStudentForm();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Schüler hinzufügen</a>
    </div>
</div>




<div id="student_blueprint" style="display:none;">
    <div class="input-field col s6">
        <input id="name" name="name" type="text" class="validate">
        <label for="name">Name des Schülers</label>
    </div>
    <div class="input-field col s6">
        <input type="date" name="bday" class="datepicker">
        <label for="date">Geburtstag</label>
    </div>
</div>

<?php include("js.php"); ?>

<script type="application/javascript">
    function submitStudentForm() {
        var url_param = "?console&type=addstudent";

        var studentNodes = document.getElementsByClassName("student_instance");

        var numValidStudents = 0;
        for (var i = 0; i < studentNodes.length; i++) {
            var student = studentNodes[i];
            var name = student.childNodes[1].childNodes[1].value;
            var bday = student.childNodes[3].childNodes[1].value;  // magic numbers op!
            if (name == "" || bday == "")
                continue;
            name = name.replace(/\s/g, '');
            numValidStudents++;
            url_param += "&students[]=" + name + ":" + bday;
        }

        if (numValidStudents == 0) {// No valid Students...
            Materialize.toast("Bitte geben sie mindestens einen Schüler an.");
            return;
        }

        $.get("index.php" + url_param, function (data) {
            try {
                var myData = JSON.parse(data);
                if (myData.success) {
                    location.reload();
                }
                else { // oh no! ;-;
                    var notifications = myData['notifications'];
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
</script>

</body>
</html>
