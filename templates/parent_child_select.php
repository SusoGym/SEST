<?php

    //$model = Model::getInstance();
    $data = $this->getDataForView();
    $students = $data['children'];
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
					Bitte Kinder angeben:
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
                hinzufügen <i class="material-icons right large">add</i></a>
        </div>
        <a onclick="submitStudentForm();" class="modal-action waves-effect waves-green btn-flat right teal-text"
           style="margin-bottom: 20px;"><i class="material-icons right">send</i>Schüler hinzufügen</a>
    </div>
</div>


<div id="student_blueprint" style="display:none;">
    <div class="input-field col l6 m6 s6">
        <input id="name" name="name" type="text" class="validate">
        <label for="name" class="truncate">Name des Schülers</label>
    </div>
    <div class="input-field col l6 m6 s6">
        <input type="date" name="bday" class="datepicker">
        <label for="date" class="truncate">Geburtstag</label>
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
