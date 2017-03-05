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
        $modeLink = '<a href="?type=vplan&all=0" class="teal-text right"><i class="material-icons left">filter_list</i><span style="font-size:10px;" >nur eigene anzeigen</span></a>';
    } else {
        $modeLink = '<a href="?type=vplan&all=1" class="teal-text right"><i class="material-icons left">select_all</i><span style="font-size:10px;" >alle anzeigen</span></a>';
    }

} elseif ($user instanceOf Guardian) {

}


?>

<?php
$dayNr = 0;
foreach ($data["VP_allDays"] as $day) { ?>
    <div class="container col s4 m4 l4">
        <div class="card ">
            <div class="card-content">
                <div class="row ">
                    <div class="col l12 m12 s12">
                        <ul class="collection with-header teal-text ">
                            <li class="collection-header"><i class="material-icons left ">event</i>
                                <span style="font-size:20px;font-weight:bold;"><?php echo $day['dateAsString']; ?>
									 </span><span
                                        style="font-size: 6px;"><?php echo "Stand: " . $data["VP_lastUpdate"]; ?></span>
                                <?php if (isset($modeLink)) {
                                    echo $modeLink;
                                } ?>
                                <!-- Zeige Termine des aktuellen Tages -->
                                <?php
                                foreach ($data["VP_termine"] as $t) {
                                    if ($day["timestamp"] == $t->sTimeStamp) {

                                        ?>
                                        <span><br><b><a class="teal-text"><?php echo $t->typ; ?></b></a><a
                                                    class="teal-text">
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
										</a>
										</span>
                                        <?php
                                    }
                                }
                                ?>
                                <!-- Ende Termine -->

                                <?php if (isset($data["VP_coverLessons"][$day['timestamp']])) { ?>
                                <?php if ($user instanceOf Teacher || isset($_GET['all'])) { ?>
                                <span style="font-size: 9px;color: #000000"><br><br><b>Abwesende Lehrer:</b><?php echo $data["VP_absentTeachers"][$day["timestamp"]]; ?></span>
                                <span style="font-size: 9px;color: #000000"><br><b><?php echo mb_convert_encoding("blockierte Räume:", 'UTF-8') ?></b>
                                    <?php echo $data["VP_blockedRooms"][$day["timestamp"]]; ?>
											</span>
                            </li>
                            <?php } ?>


                            <table class="striped hide-on-small-only">
                                <thead>
									<tr>
										<th>Stunde</th>
										<th>Klasse</th>
										<th>Vertretung</th>
										<th>Fach</th>
										<th>Raum</th>
							  <?php if ($user instanceOf Teacher){ ?> <th>statt Lehrer</th> <?php } ?>
							  <?php if ($user instanceOf Teacher){ ?> <th>statt Fach</th> <?php } ?>
										<th>Kommentar</th>
									</tr>
                                
                                </thead>
                                <tbody class="black-text">
                                <?php /** @var CoverLesson $lesson */
                                foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson) {
                                    if ($lesson->timestampDatum == $day["timestamp"]) {
                                        if (isset($this->form) && ($this->form[0] == "K" || $lesson->eFach == "ev" || $lesson->eFach == "SP" || $lesson->eFach == "rk" || $lesson->eFach == "NWT" || $lesson->eFach == "F")) {
                                            $showPupilsDetails = true;
                                        } ?>
                                        <tr>
                                            <td><?php echo $lesson->stunde ?></td>
											<td><?php echo $lesson->klassen; ?></td>
                                            <td><?php echo $lesson->vTeacherObject->getUntisName(); ?></td>
                                            <td><?php echo $lesson->vFach ?></td>
                                            <td><?php echo $lesson->vRaum ?></td>
                                              <?php if ($user instanceOf Teacher){ ?> <td><?php echo $lesson->eTeacherObject->getShortName() ?> </td><?php } ?>
                                             <?php if ($user instanceOf Teacher){ ?> <td><?php echo $lesson->eFach ?></td><?php } ?>
											 <td><?php echo $lesson->kommentar ?></td>
                                        </tr>
                                    <?php }
                                } ?>
                                </tbody>
                            </table>

                            <div class="collection hide-on-med-and-up black-text">

                                <div class="collection-item">
                                    <div class="row hide-on-med-and-up" style="margin-bottom: 0px;">
                                        <div class="col s6 center-align"><span
                                                    class="truncate">Stunde</span></div>
                                        <div class="col s6 center-align"><span
                                                    class="truncate">Fach</span>
                                        </div>
                                        <div class="col s6 center-align"><span
                                                    class="truncate">Vertretung</span>
                                        </div>
                                        <div class="col s6 center-align"><span
                                                    class="truncate">Raum</span></div>
                                        <div class="col s12 center-align"><span
                                                    class="truncate">Kommentar</span>
                                        </div>
                                    </div>
                                </div>

                                <?php /** @var CoverLesson $lesson */
                                foreach ($data["VP_coverLessons"][$day['timestamp']] as $lesson) {
                                    if ($lesson->timestampDatum == $day["timestamp"]) {
                                        if (isset($this->form) && ($this->form[0] == "K" || $lesson->eFach == "ev" || $lesson->eFach == "SP" || $lesson->eFach == "rk" || $lesson->eFach == "NWT" || $lesson->eFach == "F")) {
                                            $showPupilsDetails = true;
                                        } ?>

                                        <div class="collection-item">
                                            <div class="row hide-on-med-and-up" style="margin-bottom: 0px;">
                                                <div class="col s6 center-align"><span
                                                            class="truncate"><?php echo $lesson->stunde ?></span></div>
                                                <div class="col s6 center-align"><span
                                                            class="truncate"><?php echo $lesson->eFach ?></span>
                                                </div>
                                                <div class="col s6 center-align"><span
                                                            class="truncate"><?php echo $lesson->vTeacherObject->getUntisName() ?></span>
                                                </div>
                                                <div class="col s6 center-align"><span
                                                            class="truncate"><?php echo $lesson->vRaum ?></span></div>
                                                <div class="col s12 center-align"><span
                                                            class="truncate"><?php echo $lesson->kommentar ?></span>
                                                </div>
                                            </div>
                                        </div>

                                    <?php }
                                } ?>
                            </div>

                            <?php
                            }
                            else { ?>
                                <table class="black-text">
                                    <tr>
                                        <td>keine Vertretungen</td>
                                    </tr>
                                </table>

                            <?php } ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    $dayNr++;
} ?>




<?php include("js.php"); ?>

</body>
</html>
