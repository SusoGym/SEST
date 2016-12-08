<?php

    include("header.php");
    $data = $this->getDataForView();
    /** @var Teacher $usr */
    $usr = $data['usr'];

?>
<div class="container">

    <div class="card ">
        <div class="card-content ">
            <div class="row hide-on-med-and-down teal-text" style="font-size: 36px;">
                <b><?php echo $data['card_title']; ?></b>
            </div>


            <div class="row teal-text">
                Aktuelles Deputat: <?php echo $data['deputat']; ?> Stunden
                <br>
                <b>Sie müssen <?php echo $data['requiredSlots']; ?> Termine angeben!</b>
            </div>
            <?php if ($usr->getMissingSlots() > 0): ?>
                <div class="row red-text">
                    <b><?php
                            $required = $data['missing_slots'];

                            if ($required > 1)
                                echo "$required Termine müssen noch festgelegt werden!";
                            else
                                echo "$required Termin muss noch festgelegt werden!";

                        ?></b>
                </div>
            <?php else: ?>
                <div class="row">
                    <b>Es wurden <?php echo count($usr->getAssignedSlots()) ?> Termine bestimmt!</b>
                </div>
            <?php endif; ?>
            <div class="col l9 m12 s 12">
                <ul class="collection with-header">

                    <?php
                        foreach ($data['slots_to_show'] as $slot)
                        {
                            if (isset($slot['assigned']))
                            {
                                $symbol = "check";
                                $text = "festgelegt";
                                $href = "#";
                                $delete = false;
                            } else
                            {
                                $symbol = "forward";
                                $text = "festlegen";
                                $delete = true;
                                $href = "?type=lest&asgn=" . $slot['id'] . '"';
                            } ?>
                            <li class="collection-item">
                                <div>
                                    <?php echo date_format(date_create($slot['anfang']), 'd.m.Y H:i') . " - " . date_format(date_create($slot['ende']), 'H:i'); ?>
                                    <a href="<?php echo $href; ?> " class="secondary-content action"><i
                                                class="material-icons green-text"><?php echo $symbol; ?></i></a>
                                    <span class="secondary-content info grey-text"><?php echo $text; ?></span>
                                </div>
                            </li>

                        <?php } ?>


                </ul>
            </div>
        </div>
    </div>
</div>

<?php include("js.php"); ?>

</body>
</html>