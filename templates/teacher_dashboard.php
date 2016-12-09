<?php

    include("header.php");
    $slotlink = true;
    $slotcolor = "teal";
    $data = $this->getDataForView();
    $today = date("Ymd");

    /** @var Teacher $usr */
    $usr = $data['usr'];


    $slotdue = $usr->getMissingSlots();

    if ((isset($data['assign_end']) && $data['assign_end'] < $today) || (isset($data['assign_start']) && $data['assign_start'] > $today))
    {
        ChromePhp::info("Slot choice is disabled as date has expired!");
        $slotlink = false;
        $slotcolor = "grey";
    }

?>
<div class="container">

    <div class="card ">
        <div class="card-content ">
            <div class="row hide-on-med-and-down teal-text" style="font-size: 36px;">
                <b>Folgende Funktionen stehen zur Verfügung</b>
            </div>

            <div class="row">

                <div class="col s6 m6 l6">
                    <?php if ($slotlink)
                        { ?> <a id="home" href="?type=lest" title="Elternsprechtag"> <?php } ?>
                        <div class="center promo <?php echo $slotcolor; ?>"
                             style="border:solid; border-color:<?php echo $slotcolor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text" style="font-size: 96px;">supervisor_account</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Elternsprechtag</p>
                            <p class="hide-on-med-and-down promo-caption <?php if ($slotdue > 0) echo "red-text"; else echo "white-text" ?>"
                               style="font-size: 14px;"><b>
                                    <?php

                                        if ($slotdue > 1)
                                            echo "$slotdue Termine festlegen!";
                                        else if ($slotdue == 1)
                                            echo "$slotdue Termin festlegen!";
                                        else
                                        {
                                            $assigned = count($usr->getAssignedSlots());
                                            if ($assigned > 1)
                                                echo "$assigned Termine festgelegt!";
                                            else
                                                echo "$assigned Termin festgelegt!";
                                        }
                                    ?>
                                </b></p>
                            <p class="light-center"></p>
                        </div>
                        <?php if ($slotlink)
                            { ?>
                    </a> <?php } ?>
                </div>

                <div class="col s6 m6 l6">
                    <a id="home" href="?type=home" title="Vertretungsplan">
                        <div class="center promo teal"
                             style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">business</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Vertretungsplan</p>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 14px;">
                                &nbsp;</p>
                            <p class="light-center"></p>
                        </div>
                    </a>
                </div>

                <div class="row">

                </div>
                <div class="col s6 m6 l6">
                    <a id="home" href="?type=home" title="Termine">
                        <div class="center promo teal"
                             style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">today</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Termine</p>
                            <p class="light-center"></p>
                        </div>
                    </a>
                </div>

                <div class="col s6 m6 l6">
                    <a id="home" href="?type=home" title="Newsletter">
                        <div class="center promo teal"
                             style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">library_books</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Newsletter</p>
                            <p class="light-center"></p>
                        </div>
                    </a>
                </div>

            </div>


        </div>
    </div>
</div>

<?php include("js.php"); ?>

</body>
</html>