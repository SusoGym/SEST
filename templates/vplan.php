<?php
$data = $this->getDataForView();
/** @var Guardian $user */
$user = $data['user'];
$today = date("Ymd");
include("header.php");
$today = date('d.m.Y');
$todayMonth = date('Ym');
//$today="12.10.2016";//Nur zum Debugging
$todayTimestamp = strtotime($today);
if ($user instanceOf Teacher) {
  if ($data['VP_showAll']) {
    $modeLink = '<a href="?type=vplan" class="teal-text right"><i class="material-icons left">filter_list</i><span style="font-size:10px;" >nur eigene anzeigen</span></a>';
  } else {
    $modeLink = '<a href="?type=vplan&all" class="teal-text right"><i class="material-icons left">select_all</i><span style="font-size:10px;" >alle anzeigen</span></a>';
  }

} elseif ($user instanceOf Guardian) {

}


?>
<div class="container">

  <?php if (isset($modeLink)) {
    ?>
    <div class="card">
      <div class="card-content">
        <div class="center">
          <?php echo $modeLink; ?>
          <a href="<?php echo $data['icsPath']; ?>" class="teal-text btn-flat">
            <i class="material-icons left">file_download</i>
            Herunterladen
          </a>
        </div>
      </div>
    </div>
    <?php
  } ?>

  <?php
  $dayNr = 0;
  foreach ($data["VP_allDays"] as $day) { ?>
    <div class="card">
      <div class="card-content">
        <span class="card-title"><i class="material-icons left ">event</i><?php echo $day['dateAsString']; ?></span>

        <?php
        foreach ($data["VP_termine"] as $t) {
          if ($day["timestamp"] == $t->sTimeStamp) {

            ?>
            <span>
              <b class="black-text">
                <?php echo $t->typ; ?>
              </b>
              <font class="grey-text">
                <?php echo $t->sweekday . " " . $t->sday;
                if (isset($t->stime)) {
                  echo ' (' . $t->stime . ')';
                }
                if (isset($t->eday)) {
                  echo "-";
                }
                echo " " . $t->eweekday . " " . $t->eday;
                if (isset($t->etime)) {
                  echo ' (' . $t->etime . ')';
                }
                ?>
              </font>
            </span>
            <?php
          }
        }
        ?>

        <?php if ($user instanceOf Teacher || isset($_GET['all'])) { ?>
          <p>
            <br />
            <b>Abwesende Lehrer:</b>
            <?php echo $data["VP_absentTeachers"][$day["timestamp"]]; ?>
            <br />
            <b><?php echo mb_convert_encoding("Blockierte Räume:", 'UTF-8') ?></b>
            <?php echo $data["VP_blockedRooms"][$day["timestamp"]]; ?>
          </p>
          <?php } ?>
          <ul class="collection">

            <?php if (isset($data["VP_coverLessons"][$day['timestamp']])) { ?>


              <table class="striped hide-on-small-only">
                <thead>
                  <tr>
                    <th>Stunde</th>
                    <th>Lehrer</th>
                    <th>Fach</th>
                    <th>Raum</th>
                    <th>statt Lehrer:</th>
                    <th>statt Fach:</th>
                    <th>Kommentar</th>
                  </tr>
                </thead>
                <tbody>
                  <?php /** @var CoverLesson $lesson */
                  foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson) {
                    if ($lesson->timestampDatum == $day["timestamp"]) {
                      if (isset($this->form) && ($this->form[0] == "K" || $lesson->eFach == "ev" || $lesson->eFach == "SP" || $lesson->eFach == "rk" || $lesson->eFach == "NWT" || $lesson->eFach == "F")) {
                        $showPupilsDetails = true;
                      } ?>
                      <tr>
                        <td><?php echo $lesson->stunde ?></td>
                        <td><?php echo $lesson->vTeacherObject->getUntisName(); ?></td>
                        <td><?php echo $lesson->vFach ?></td>
                        <td><?php echo $lesson->vRaum ?></td>
                        <td><?php echo $lesson->eTeacherObject->getShortName() ?></td>
                        <td><?php echo $lesson->eFach ?></td>
                        <td><?php echo $lesson->kommentar ?></td>
                      </tr>
                      <?php }
                    } ?>
                  </tbody>
                </table>

                <table id="mobilevptable" class="responsive-table hide-on-med-and-up">
                  <thead>
                    <tr>
                      <th>Stunde</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <th><?php echo $lesson->stunde ?></th>
                      <?php endforeach; ?>
                    </tr>
                  </thead>
                  <tbody>

                    <tr>
                      <th>Lehrer</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <?php if ($lesson->timestampDatum == $day["timestamp"]): ?>
                          <td><?php echo $lesson->vTeacherObject->getUntisName(); ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tr>

                    <tr>
                      <th>Fach</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <?php if ($lesson->timestampDatum == $day["timestamp"]): ?>
                          <td><?php echo $lesson->vFach ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tr>

                    <tr>
                      <th>Raum</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <?php if ($lesson->timestampDatum == $day["timestamp"]): ?>
                          <td><?php echo $lesson->vRaum ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tr>

                    <tr>
                      <th>statt Lehrer</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <?php if ($lesson->timestampDatum == $day["timestamp"]): ?>
                          <td><?php echo $lesson->eTeacherObject->getShortName() ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tr>

                    <tr>
                      <th>statt Fach</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <?php if ($lesson->timestampDatum == $day["timestamp"]): ?>
                          <td><?php echo $lesson->eFach ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tr>

                    <tr>
                      <th>Kommentar:</th>
                      <?php foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson): ?>
                        <?php if ($lesson->timestampDatum == $day["timestamp"]): ?>
                          <td><?php echo $lesson->kommentar ?></td>
                        <?php endif; ?>
                      <?php endforeach; ?>
                    </tr>
              </tbody>
            </table>

                <?php
              }
              else { ?>
                <table class="black-text">
                  <tr>
                    <td>keine Vertretungen</td>
                  </tr>
                </table>

                <?php } ?>
              </div>
            </div>


          </ul>
          <?php
          $dayNr++;
        } ?>

        <div class="card">
          <div class="card-content">
            <div class="center grey-text">
              <?php echo "Stand: " . $data["VP_lastUpdate"]; ?>
            </div>
          </div>
        </div>
      </div>

      <?php include("js.php"); ?>

    </body>
    </html>
