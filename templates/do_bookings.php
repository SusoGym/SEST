<?php

    foreach ($teachers as $teacherStudent)
    {
        /** @var Teacher $teacher */
        $teacher = $teacherStudent['teacher'];

        (in_array($teacher->getId(), $data['bookedTeachers'])) ? $bookedThisTeacher = true : $bookedThisTeacher = false;
        $amountAvailableSlots = count($teacher->getAllBookableSlots($user->getParentId()));
        ?>
        <div id="tchr<?php echo $teacher->getId(); ?>" class="col s12">
            <ul class="collection with-header">
                <li class="collection-header">
					<span style="font-size:22px;"><?php if ($amountAvailableSlots != 0) echo "Termin bei " ?>
                        <span
                                class="teal-text"><?php echo $teacher->getFullname(); ?></span><?php if ($amountAvailableSlots != 0) echo " buchen" ?>
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
                                    echo $student->getName() . " " . $student->getSurname();
                                    $students++;
                                }
                            ?>
                            )</span>
                        <?php
                            if ($amountAvailableSlots == 0)
                            { ?>

                                <span class="right red-text"
                                      style="font-size: 18px">ausgebucht!</span>
                            <?php } elseif ($maxedOutAppointments)
                            { ?>
                                <span class="right orange-text"
                                      style="font-size: 18px">Maximum gebucht!</span>

                            <?php } ?>
					</span>
                </li>
                <?php

                    if ($amountAvailableSlots != 0)
                    {

                        foreach ($teacher->getAllBookableSlots($user->getParentId()) as $slot)
                        {
                            $anfang = date_format(date_create($slot['anfang']), 'd.m.Y H:i');
                            $ende = date_format(date_create($slot['ende']), 'H:i');

                            $symbol = $symbolColor = $text = $link = "";

                            if ($slot['eid'] == null)
                            {
                                if (in_array($slot['slotId'], $appointments))
                                {
                                    //cannot book a slot at that time because already booked another
                                    $symbol = "clear";
                                    $symbolColor = "red-text";
                                    $text = "anderer Termin bereits gebucht";
                                    $link = "";
                                } else if ($bookedThisTeacher)
                                {

                                    $symbol = "clear";
                                    $symbolColor = "blue-text";
                                    $text = "es kann nur ein Termin pro Lehrer gebucht werden";
                                    $link = "";

                                } else if ($maxedOutAppointments)
                                {
                                    $symbol = "clear";
                                    $symbolColor = "orange-text";
                                    $link = "";
                                    $text = "maximale Anzahl von Terminen gebucht!";
                                } else
                                {
                                    //slot could be booked
                                    $symbol = "forward";
                                    $symbolColor = "teal-text";
                                    $text = "jetzt buchen";
                                    $link = "href='?type=eest&slot=" . $slot['bookingId'] . "&action=book'";
                                }


                            } elseif ($slot['eid'] == $user->getParentId())
                            {
                                //slot is booked by oneself
                                $symbol = "check";
                                $text = "gebucht";
                                $symbolColor = "green-text";
                                $link = "href='?type=eest&slot=" . $slot['bookingId'] . "&action=del'";
                                $showSlot = true;
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
                            <?php
                            ?>
                        <?php }
                    } ?>
            </ul>
        </div>
    <?php } ?>