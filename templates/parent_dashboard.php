<?php

    include("header.php");
    $estActive = true;
    $estColor = "teal";
    $data = $this->getDataForView();
    $today = date("Ymd");
	$selectionActive = true;
	$selectionColor = "teal";
    /** @var Guardian $usr */
    $usr = $data['usr'];
	$children = $data['children'];
	
	if ((isset($data['book_end']) && $data['book_end'] < $today) || (isset($data['book_start']) && $data['book_start'] > $today) || count($children) == 0)
		{
        ChromePhp::info("No children selected by guardian or booking time expired");
        $estActive = false;
        $estColor = "grey";
		}
	if(count($children) == 0){
		$selectionActive = false;
		}
	
	if(isset($data['modules']) ){
		$modules = $data['modules'];
		if(!$modules['vplan'] || !$selectionActive){
				$vplanColor  = "grey";
				$vplanLink = "";
		} else {
				$vplanColor  = "teal";
				$vplanLink = 'href="'."?type=vplan";
		}
		if(!$modules['events'] || !$selectionActive){
				$eventsColor  = "grey";
				$eventsLink = "";
		} else {
				$eventsColor  = "teal";
				$eventsLink = 'href="'."?type=events";
		}
		if(!$modules['news'] || !$selectionActive){
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
				<div class="col s4 m4 l4">
                    <a id="home" href="?type=childsel" title="Kinder">
                        <div class="center promo teal"
                             style="border:solid; border-color:teal; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">face</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Kinder</p>
							<p class="hide-on-med-and-down promo-caption <?php if (count($children) == 0) echo "red-text"; else echo "white-text" ?>"
                               style="font-size: 14px;"><b>
                                    <?php
                                        if (count($children) == 0)
                                            echo "Bitte Kinder angeben!";
										else
											echo "&nbsp;";
                                        
                                    ?>
                                </b></p>
                        </div>
                    </a>
                </div>
				<div class="col s4 m4 l4">
					<?php if ($estActive)
                        { ?> <a id="home" href="?type=eest" title="Elternsprechtag"> <?php } ?>
                        <div class="center promo <?php echo $estColor; ?>"
                             style="border:solid; border-color:<?php echo $estColor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text" style="font-size: 96px;">supervisor_account</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Elternsprechtag</p>
                            <p class="hide-on-med-and-down promo-caption style="font-size: 14px;"><b>&nbsp;</b></p>
                        </div>
                        <?php if ($estActive)
                            { ?>
                    </a> <?php } ?>
				</div>
				
				<div class="col s4 m4 l4">
                    <?php if ($selectionActive)
                        { ?> <a id="vplan" <?php echo $vplanLink; ?> title="Vertretungsplan"> <?php } ?>
                        <div class="center promo <?php echo $vplanColor; ?>"
                             style="border:solid; border-color:<?php echo $vplanColor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">business</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Vertretungsplan</p>
							<p class="hide-on-med-and-down promo-caption style="font-size: 14px;"><b>&nbsp;</b></p>
                        </div>
                    </a>
                </div>
				
			</div>
            <div class="row">

                <div class="row">

                </div>
                <div class="col s4 m4 l4">
                    <?php if ($selectionActive)
                        { ?> <a id="events" <?php echo $eventsLink; ?> title="Termine"> <?php } ?>
                        <div class="center promo <?php echo $eventsColor; ?>"
                             style="border:solid; border-color:<?php echo $eventsColor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">today</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Termine</p>
							<p class="hide-on-med-and-down promo-caption style="font-size: 14px;"><b>&nbsp;</b></p>
                        </div>
                    </a>
                </div>

                <div class="col s4 m4 l4">
                    <?php if ($selectionActive)
                        { ?> <a id="home" <?php echo $newsLink; ?> title="Newsletter"> <?php } ?>
                        <div class="center promo <?php echo $newsColor; ?>"
                             style="border:solid; border-color:<?php echo $newsColor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">library_books</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Newsletter</p>
                            <p class="hide-on-med-and-down promo-caption style="font-size: 14px;"><b>&nbsp;</b></p>
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
   <li><a class="waves-effect" href="?type=childsel"><i class="material-icons">face</i>Kinder</a></li>
   <?php if ($estActive) { ?>
   <li><a class="waves-effect" href="?type=eest"><i class="material-icons">supervisor_account</i>Elternsprechtag</a></li>
   <?php } ?>
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