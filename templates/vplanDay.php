<div class="card">
    <div class="card-content">
                <span class="card-title"><i
                            class="material-icons left ">event</i><?php echo $day['dateAsString']; ?></span>
        
        <?php
        foreach ($events as $t) {
            if ($timestamp != $t->sTimeStamp)
                continue;
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
        
        
        if ($much) { ?>
            <p>
                <br/>
                <b>Abwesende Lehrer:</b>
                <?php echo $absentTeachers; ?>
                <br/>
                <b><?php echo mb_convert_encoding("Blockierte Räume:", 'UTF-8') ?></b>
                <?php echo $blockedRooms; ?>
            </p>
        <?php } ?>


        <ul class="collection">
            <?php if ($coverLessons != null):
                
                $desktop = "";
                
                /** @var CoverLesson $lesson */
                for ($i = 0;
                     $i < sizeof($coverLessons);
                     $i++) {
                    
                    $lesson = $coverLessons[$i];
                    if ($lesson->timestampDatum != $timestamp)
                        continue;
                    
                    //Desktop stuff
                    
                    if ($i == 0)
                        $desktop .= '
                        <table class="striped hide-on-small-only">
                        <thead>
                        <tr>
                            <th>Stunde</th>'
                            . ($much ? '<th>Klasse</th>' : '') .
                            '<th>Lehrer</th>
                            <th>Fach</th>
                            <th>Raum</th>' .
                            ($showDetails ? '
                                <th>statt Lehrer:</th>
                                <th>statt Fach:</th>' : '') .
                            '<th>Kommentar</th>
                        </tr>
                        </thead>
                        <tbody>';
                    
                    $comment = $lesson->kommentar;
                    $hour = $lesson->stunde;
                    $classes = $lesson->klassen;
                    $subTeacher = $lesson->vTeacherObject->getUntisName();
                    $subSubject = $lesson->vFach;
                    $subRoom = $lesson->vRaum;
                    $orgTeacher = $lesson->eTeacherObject->getShortName();
                    $orgSubject = $lesson->eFach;
                    
                    $lowerCaseSubject = strtolower($lesson->eFach);
                    
                    if (($lowerCaseSubject == "ev" || $lowerCaseSubject == "rk" || $lowerCaseSubject == "sp" || $lowerCaseSubject == "nwt" || $lowerCaseSubject == "f") && !$showDetails)
                        $comment = "(Statt: $orgTeacher) $comment";
                    
                    $desktop .= "<tr><td>$hour</td>";
                    if ($much)
                        $desktop .= "<td>$classes</td>";
                    $desktop .= "<td>$subTeacher</td><td>$subSubject</td><td>$subRoom</td>";
                    if ($showDetails)
                        $desktop .= "<td>$orgTeacher</td><td>$orgSubject</td>";
                    $desktop .= "<td>$comment</td>";
                    
                    
                    if ($i == sizeof($coverLessons) - 1)
                        $desktop .= "</tbody></table>";
                    
                    // Done with desktop
                    
                    
                }
                
                echo $desktop;
                
                ?>

                <table id="mobilevptable" class="responsive-table hide-on-med-and-up">
                    <thead>
                    <tr>
                        <th>Stunde</th>
                        <?php foreach ($coverLessons as $lesson):?>
                            <th><?php echo $lesson->stunde ?></th>
                        <?php endforeach; ?>
                    </tr>
                    </thead>
                    <tbody>
                    
                    <?php
                    if ($much):
                        ?>
                        <tr>
                            <th>Klasse</th>
                            <?php foreach ($coverLessons as $lesson): ?>
                                <?php if ($lesson->timestampDatum == $timestamp): ?>
                                    <td><?php echo $lesson->klassen; ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <?php
                    endif;
                    ?>
                    <?php
                    if ($showDetails):
                        ?>
                        <tr>
                            <th>statt Fach</th>
                            <?php foreach ($coverLessons as $lesson): ?>
                                <?php if ($lesson->timestampDatum == $timestamp): ?>
                                    <td><?php echo $lesson->eFach ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                        <tr>
                            <th>statt Lehrer</th>
                            <?php foreach ($coverLessons as $lesson): ?>
                                <?php if ($lesson->timestampDatum == $timestamp): ?>
                                    <td><?php echo $lesson->eTeacherObject->getShortName() ?></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tr>
                    <?php endif; ?>
                    
                    <tr>
                        <th>Fach</th>
                        <?php foreach ($coverLessons as $lesson): ?>
                            <?php if ($lesson->timestampDatum == $timestamp): ?>
                                <td><?php echo $lesson->vFach ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Lehrer</th>
                        <?php foreach ($coverLessons as $lesson): ?>
                            <?php if ($lesson->timestampDatum == $timestamp): ?>
                                <td><?php echo $lesson->vTeacherObject->getUntisName(); ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>



                    <tr>
                        <th>Raum</th>
                        <?php foreach ($coverLessons as $lesson): ?>
                            <?php if ($lesson->timestampDatum == $timestamp): ?>
                                <td><?php echo $lesson->vRaum ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    <tr>
                        <th>Kommentar:</th>
                        <?php foreach ($coverLessons as $lesson):
                            $comment = $lesson->kommentar;
                            $lowerCaseSubject = strtolower($lesson->eFach);
                            $orgTeacher = $lesson->eTeacherObject->getShortName();
                            if (($lowerCaseSubject == "ev" || $lowerCaseSubject == "rk" || $lowerCaseSubject == "sp" || $lowerCaseSubject == "nwt" || $lowerCaseSubject == "f") && !$showDetails)
                                $comment = "(Statt: $orgTeacher) $comment";
                            
                            ?>
                            <?php if ($lesson->timestampDatum == $timestamp): ?>
                                <td><?php echo $comment ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                    </tbody>
                </table>
                
                <?php
            else: ?>
                <table class="black-text">
                    <tr>
                        <td>keine Vertretungen</td>
                    </tr>
                </table>
            
            <?php endif; ?>
    </div>
</div>


</ul>