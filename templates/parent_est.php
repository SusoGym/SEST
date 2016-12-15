<?php
    $data = $this->getDataForView();
    /** @var Guardian $user */
    $user = $data['user'];
    
    $today = date("Ymd");
    include("header.php");

?>

<div class="container col s4 m4 l4">
    <div class="card ">
        <div class="card-content">
            <div class="row">
                <div class="col l12 m12 s12">
                    <?php
			   if($today > $data['book_end']) {
				include("show_bookings.php");
				} else {
				 $teachers = $data['teachers'];
   				 $appointments = $data['appointments'];
   				 $maxAppointments = $data['maxAppointments'];
   				 $maxedOutAppointments = count($appointments) >= $maxAppointments;
				include("do_bookings.php");
				}
			   ?>
                        

                </div>
            </div>
        </div>
        <div class="card-action center">
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
    <li>
        <div class="divider"></div>
    </li>
    <li><a class="subheader">Missing</a></li>

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

<ul id='students' class='dropdown-content students'>
    <?php
        foreach ($students as $student)
        {
            echo "<li class='tab'><a href='#stu";
            echo $student["id"];
            echo "'>";
            echo $student["name"];
            echo "</a></li>";
        }
    ?>
</ul>


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
