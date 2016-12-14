<?php

$data = $this->getDataForView();
/** @var Guardian $user */
$user = $data['user'];
$teachers = $data['teachers'];
$appointments = $data['appointments'];
include("header.php");
?>

<div class="container">
  <div class="card ">
    <div class="card-content">
      <span class="card-title">
        <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
          <i class="material-icons">chevron_left</i>
        </a>
        Termine buchen
      </span>
      <div class="row">

        <div class="col l12 m12 s12">
          <?php
          foreach ($teachers as $teacherStudent)
          {
            /** @var Teacher $teacher */
            $teacher = $teacherStudent['teacher'];
            $amountAvailableSlots = count($teacher->getAllBookableSlots($user->getParentId()));
            ?>
            <div id="tchr<?php echo $teacher->getId(); ?>" class="col s12">
              <ul class="collection with-header">
                <li class="collection-header">
                  <span style="font-size:22px;"><?php if($amountAvailableSlots != 0)echo "Termin bei " ?><span
                    class="teal-text"><?php echo $teacher->getFullname(); ?></span><?php if($amountAvailableSlots != 0)echo" buchen"?>
                    <span style="font-size:12px;">&nbsp;(
                      <?php
                      $students = 0;
                      /** @var Student $student */
                      foreach ($teacherStudent['students'] as $student)
                      {
                        if ($students > 0)
                        {
                          echo ' / ';
                        }
                        echo $student->getName() . " " .$student->getSurname();
                        $students++;
                      }
                      ?>
                      )</span>
                      <?php
                      if ($amountAvailableSlots == 0): ?>

                      <span class="right red-text" style="font-size: 18px">ausgebucht!</span>
                      <?php //TODO: text?
                    endif; ?>
                  </span>
                </li>
                <?php

                if ($amountAvailableSlots != 0)
                {

                  foreach ($teacher->getAllBookableSlots($user->getParentId()) as $slot)
                  {
                    $anfang = date_format(date_create($slot['anfang']), 'd.m.Y H:i');
                    $ende = date_format(date_create($slot['ende']), 'H:i');
                    if ($slot['eid'] == null)
                    {
                      //slot could be booked
                      $symbol = "forward";
                      $symbolColor = "teal-text";
                      $text = "jetzt buchen";
                      $link = "href='?type=eest&slot=" . $slot['bookingId'] . "&action=book'";
                      if (in_array($slot['slotId'], $appointments))
                      {
                        //cannot book a slot at that time because already booked another
                        $symbol = "clear";
                        $symbolColor = "red-text";
                        $text = "anderer Termin bereits gebucht";
                        $link = "";
                      }
                    } else
                    {
                      //slot is booked by oneself
                      $symbol = "check";
                      $text = "gebucht";
                      $symbolColor = "green-text";
                      $link = "href='?type=eest&slot=" . $slot['bookingId'] . "&action=del'";
                    }
                    ?>
                    <li class="collection-item">
                      <div><span class="teal-text ">
                        <?php
                        echo $anfang . " - " . $ende;
                        ?>
                      </span>
                      <a <?php echo $link; ?> class="secondary-content action"><i
                        class="material-icons <?php echo $symbolColor; ?>"><?php echo $symbol; ?></i></a>
                        <span class="secondary-content info grey-text"><?php echo $text; ?></span>
                      </div>
                    </li>
                    <?php }
                  } ?>
                </ul>
              </div>
              <?php } ?>

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
