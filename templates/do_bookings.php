<div class="cols s12 m12 l12">
    <ul class="collapsible white" data-collapsible="accordion">
        <?php foreach ($teachers as $teacherStudent):
            
            $teacher = $teacherStudent['teacher'];
            (in_array($teacher->getId(), $data['bookedTeachers'])) ? $bookedThisTeacher = true : $bookedThisTeacher = false;
            $amountAvailableSlots = count($teacher->getAllBookableSlots($user->getParentId()));
            ?>

            <li>
                <div class="collapsible-header">
                    
                    <?php if ($bookedThisTeacher): ?>
                        <span id="info" class="right green-text" style="font-size: 14px">gebucht!</span>
                    <?php elseif ($amountAvailableSlots == 0): ?>
                        <span id="info" class="right red-text" style="font-size: 14px">ausgebucht!</span>
                    <?php elseif ($maxedOutAppointments): ?>
                        <span id="info" class="right orange-text" style="font-size: 14px">Maximum gebucht!</span>
                    <?php else: ?>
                        <span id="info" class="right orange-text" style="font-size: 14px">möglich</span>
                    <?php endif; ?>

                    <span class="left teal-text hide-on-small-only">
            <?php echo $teacher->getFullname(); ?>
          </span>
                    <span class="teal-text hide-on-med-and-up truncate">
            <?php echo $teacher->getFullname(); ?>
          </span>
                    <span class="left hide-on-med-and-down" style="font-size:12px;">
            &emsp;(
                        <?php
                        $students = 0;
                        /** @var Student $student */
                        foreach ($teacherStudent['students'] as $student) {
                            if ($students > 0) {
                                echo ' / ';
                            }
                            echo $student->getName() . " " . $student->getSurname();
                            $students++;
                        }
                        ?>
                        )
          </span>


                </div>
                
                <?php if ($amountAvailableSlots != 0): ?>
                    <div class="collapsible-body">
                        <ul class="collection">
                            <?php foreach ($teacher->getAllBookableSlots($user->getParentId()) as $slot):
                                $anfang = date_format(date_create($slot['anfang']), 'H:i');
                                $ende = date_format(date_create($slot['ende']), 'H:i');
                                
                                $symbol = $symbolColor = $text = $link = "";
                                ?>
                                
                                <?php
                                if ($slot['eid'] == null) {
                                    if ($bookedThisTeacher || $maxedOutAppointments)
                                        continue;
                                    
                                    if (in_array($slot['slotId'], $appointments)) {
                                        //cannot book a slot at that time because already booked another
                                        $symbol = "clear";
                                        $symbolColor = "red-text";
                                        $text = "anderer Termin bereits gebucht";
                                        $link = "";
                                    } else if ($bookedThisTeacher) {
                                        $symbol = "clear";
                                        $symbolColor = "blue-text";
                                        $text = "es kann nur ein Termin pro Lehrer gebucht werden";
                                        $link = "";
                                    } else if ($maxedOutAppointments) {
                                        $symbol = "clear";
                                        $symbolColor = "orange-text";
                                        $link = "";
                                        $text = "maximale Anzahl von Terminen gebucht!";
                                    } else {
                                        //slot could be booked
                                        $symbol = "forward";
                                        $symbolColor = "teal-text";
                                        $text = "jetzt buchen";
                                        $link = "href='?type=eest&slot=" . $slot['bookingId'] . "&action=book'";
                                    }
                                    
                                } elseif ($slot['eid'] == $user->getParentId()) {
                                    //slot is booked by oneself
                                    $symbol = "check";
                                    $text = "gebucht";
                                    $symbolColor = "green-text";
                                    $link = "href='?type=eest&slot=" . $slot['bookingId'] . "&action=del'";
                                    $showSlot = true;
                                }
                                ?>

                                <li class="collection-item">
                                    <div>
                    <span class="teal-text ">
                      <?php echo $anfang . " - " . $ende; ?>
                    </span>
                                        <a <?php echo $link; ?> class="secondary-content action">
                                            <i class="material-icons <?php echo $symbolColor; ?>">
                                                <?php echo $symbol; ?>
                                            </i>
                                        </a>
                                        <span class="secondary-content info grey-text">
                      <?php echo $text; ?>
                    </span>
                                    </div>
                                </li>
                            
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
            </li>
        
        
        <?php endforeach; ?>

    </ul>
</div>
