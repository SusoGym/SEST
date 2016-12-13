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
	
	if(isset($data['modules']) ){
		$modules = $data['modules'];
		if(!$modules['vplan']){
				$vplanColor  = "grey";
				$vplanLink = "";
		} else {
				$vplanColor  = "teal";
				$vplanLink = 'href="'."?type=vplan";
		}
		if(!$modules['events']){
				$eventsColor  = "grey";
				$eventsLink = "";
		} else {
				$eventsColor  = "teal";
				$eventsLink = 'href="'."?type=events";
		}
		if(!$modules['news']){
				$newsColor  = "grey";
				$newsLink = "";
		} else {
				$newsColor  = "teal";
				$newsLink = 'href="'."?type=news";
			
		}
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
                    <a id="vplan" <?php echo $vplanLink; ?> title="Vertretungsplan">
                        <div class="center promo <?php echo $vplanColor; ?>"
                             style="border:solid; border-color:<?php echo $vplanColor; ?>; border-style: outset; border-radius:5px;">
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
                    <a id="events" <?php echo $eventsLink; ?> title="Termine">
                        <div class="center promo <?php echo $eventsColor; ?>"
                             style="border:solid; border-color:<?php echo $eventsColor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">today</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Termine</p>
                            <p class="light-center"></p>
                        </div>
                    </a>
                </div>

                <div class="col s6 m6 l6">
                    <a id="news" <?php echo $newsLink; ?> title="Newsletter">
                        <div class="center promo <?php echo $newsColor; ?>"
                             style="border:solid; border-color:<?php echo $newsColor; ?>; border-style: outset; border-radius:5px;">
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
   <li><a class="waves-effect" href="?type=lest"><i class="material-icons">supervisor_account</i>Elternsprechtag</a></li>
   <?php if($vplanLink <>"") { ?>
	<li><a class="waves-effect" href="<?php echo $vplanLink; ?>"><i class="material-icons">business</i>Vertretungsplan</a></li>
   <?php }
	if($eventsLink <> "") { ?>	   
	<li><a class="waves-effect" href="<?php echo $eventsLink; ?>"><i class="material-icons">today</i>Termine</a></li>
   <?php }
	if ($newsLink <>"") { ?>
   <li><a class="waves-effect" href="<?php echo $newsLink; ?>"><i class="material-icons">library_books</i>newsletter</a></li>
   <?php } ?>

    
</ul>

<?php include("js.php"); ?>

</body>
</html>