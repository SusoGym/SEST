<?php

include("header.php");
$data = $this->getDataForView();
/** @var Teacher $usr */
$usr = $data['usr'];
$today = date("Ymd H:i");
if ($today > $data['assign_end']) {
    $enabled = false;
} else {
    $enabled = true;
}

?>
<div class="container">
    
    <div class="card ">
        <div class="card-content ">
            <span class="card-title"><?php echo $data['card_title']; ?></span>
            <?php if ($enabled) { ?>
                <div class="teal-text">
                    Aktuelles Deputat: <?php echo $data['deputat']; ?> Stunden
                    <br>
                    <b>Sie müssen <?php echo $data['requiredSlots']; ?> Termine angeben!</b>
                </div>
                
                <?php if ($usr->getMissingSlots() > 0): ?>
                    <div class="red-text">
                        <b><?php
                            $required = $data['missing_slots'];
                            
                            if ($required > 1)
                                echo "$required Termine müssen noch festgelegt werden!";
                            else
                                echo "$required Termin muss noch festgelegt werden!";
                            
                            ?></b>
                    </div>
                <?php else: ?>
                    <b>Es wurden <?php echo count($usr->getAssignedSlots()) ?> Termine bestimmt!</b>
                <?php endif; ?>
            <?php } ?>
            <div class="col l9 m12 s 12">
                <ul class="collection with-header">
                    
                    <?php
                    foreach ($data['slots_to_show'] as $slot) {
                        if (isset($slot['assigned'])) {
                            $symbol = "check";
                            $text = "festgelegt";
                            $href = "?type=lest&del=" . $slot['id'];
                            $delete = false;
                        } else {
                            $symbol = "forward";
                            $text = "festlegen";
                            $delete = true;
                            $href = "?type=lest&asgn=" . $slot['id'];
                        } ?>
                        <li class="collection-item">
                            <div>
                                <?php if ($enabled) { ?>
                                    <?php echo date_format(date_create($slot['anfang']), 'H:i') . " - " . date_format(date_create($slot['ende']), 'H:i'); ?>
                                    <a href="<?php echo $href; ?> " class="secondary-content action"><i
                                                class="material-icons green-text"><?php echo $symbol; ?></i></a>
                                    <span class="secondary-content info grey-text"><?php echo $text; ?></span>
                                <?php } else { //Booking period is over - appointments are displayed
                                    echo date_format(date_create($slot['anfang']), 'H:i') . " - " . date_format(date_create($slot['ende']), 'H:i');
                                    if (!isset($slot['assigned'])) { ?>
                                        <a class="secondary-content action"><i
                                                    class="material-icons red-text">not_interested</i></a>
                                        <?php
                                    }
                                    
                                    foreach ($data['teacher_appointments'] as $bookedSlot) {
                                        if ($bookedSlot['slotId'] == $slot['id']) { ?>
                                            <span class="secondary-content info grey-text">
                                <?php echo $bookedSlot['parent']->getFullName(); ?>
                                <?php //prepare Children display
                                $childrenList = null;
                                foreach ($bookedSlot['parent']->getChildren() as $child) {
                                    if (in_array($child->getClass(), $data['teacher_classes'])) {
                                        (isset($childrenList)) ? $separator = "/" : $separator = "";
                                        $childrenList = $childrenList . $separator . $child->getFullName() . ',' . $child->getClass();
                                    }
    
                                }
                                echo ' (' . $childrenList . ')'; ?>
                              </span>
                                        <?php }
                                    }
                                    
                                    
                                } ?>
                            </div>
                        </li>
                    
                    <?php } ?>
                
                
                </ul>
            </div>
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


<?php include("js.php"); ?>

</body>
</html>
