<?php

$model = Model::getInstance();
$teacherNames = array();
$teacherObjs = array();
$user = Controller::getUser();
if ($user instanceof Guardian) {
  $children = $user->getChildren();
  foreach ($children as $child) {
    $students[$child->getId()]['id'] = $child->getId();
    $students[$child->getId()]['name'] = $child->getFullName();
    $teachers = $child->getTeachers();
    foreach ($teachers as $teacher) {
      $students[$child->getId()]['teachers'][$teacher->getTeacherId()] = array('id' => $teacher->getTeacherId(), 'name' => $teacher->getFullName());
    }
  }
} else if($user instanceof Admin)
{
    $teacherObjs = $model->getTeachers();
}

include("header.php");

?>

<div class="container">

<pre><?php //echo json_encode($students, JSON_PRETTY_PRINT); ?></pre>

    <div class="card ">
        <div class="card-content">
            <div class="row">
                <div class="col l3 hide-on-med-and-down">

                  <a class='dropdown-button btn' style="width:100%;" href='#' data-activates='students'>Schüler auswählen</a>

                  <ul id='students' class='dropdown-content students'>

                    <?php
                      foreach ($students as $student) {
                        echo "<li class=\"tab\"><a href='#stu";
                        echo $student["id"];
                        echo "'>";
                        echo $student["name"];
                        echo "</a></li>";
                      }
                    ?>
                  </ul>



                      <ul class="teachers collection">
                          <?php foreach ($students as $student) { ?>
                            <div id='stu<?php echo $student['id']; ?>'>
                              <?php foreach ($student['teachers'] as $teacher) { ?>
                                  <li class="tab"><a class="collection-item"
                                                     onclick="$('html, body').animate({ scrollTop: 0 }, 200);"
                                                     href="#tchr<?php echo $teacher['id']; ?>"><?php echo $teacher['name']; ?></a>
                                  </li>
                              <?php } ?>
                            </div>
                        <?php  } ?>
                      </ul>

                </div>
                <div class="col l9 m12 s12">
                  <?php foreach ($students as $student) { ?>
                      <?php foreach ($student['teachers'] as $teacher) { ?>
                          <div id="tchr<?php echo $teacher['id']; ?>" class="col s12">
                              <ul class="collection with-header">
                                  <li class="collection-header">
                                    <h4>Termin bei <span class="teal-text"><?php echo $teacher['name']; ?></span> buchen</h4></li>

                                  <li class="collection-item">
                                    <div>
                                      slot
                                      <a href class="secondary-content action"><i class="material-icons green-text">forward</i></a>
                                      <span class="secondary-content info grey-text">jetzt buchen</span>
                                    </div>
                                  </li>
                                  <li class="collection-item">
                                    <div>
                                      slot
                                      <span class="secondary-content action"><i class="material-icons grey-text">check</i></span>
                                      <span class="secondary-content info grey-text">gebucht</span>
                                    </div>
                                  </li>
                                  <li class="collection-item">
                                    <div>
                                      slot
                                      <span class="secondary-content action"><i class="material-icons red-text">clear</i></span>
                                      <span class="secondary-content info grey-text">nicht verfügbar</span>
                                    </div>
                                  </li>
                              </ul>
                          </div>
                      <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="card-action center">
            <div class="divider"></div>
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
    <?php $mobile = true;
    include("navbar.php"); ?>
    <li>
        <div class="divider"></div>
    </li>
    <li><a class="subheader">Teachers</a></li>
    <?php foreach ($teacherNames as $id => $name) { ?>
        <li class="tab"><a class="waves-effect"
                           onclick="$('ul.teachers').tabs('select_tab', 'tchr<?php echo $id; ?>');$('.button-collapse').sideNav('hide');"><?php echo $name; ?></a>
        </li>
    <?php } ?>
</ul>

<?php include("js.php"); ?>

</body>
</html>

