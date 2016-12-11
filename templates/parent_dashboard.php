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
		$selectionColor = "grey";
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
                        { ?> <a id="home" href="?home" title="Vertretungsplan"> <?php } ?>
                        <div class="center promo <?php echo $selectionColor; ?>"
                             style="border:solid; border-color:<?php echo $selectionColor; ?>; border-style: outset; border-radius:5px;">
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
                        { ?> <a id="home" href="?home" title="Termine"> <?php } ?>
                        <div class="center promo <?php echo $selectionColor; ?>"
                             style="border:solid; border-color:<?php echo $selectionColor; ?>; border-style: outset; border-radius:5px;">
                            <i class="material-icons  white-text " style="font-size: 96px;">today</i></li>
                            <p class="hide-on-med-and-down promo-caption white-text " style="font-size: 36px;">
                                Termine</p>
							<p class="hide-on-med-and-down promo-caption style="font-size: 14px;"><b>&nbsp;</b></p>
                        </div>
                    </a>
                </div>

                <div class="col s4 m4 l4">
                    <?php if ($selectionActive)
                        { ?> <a id="home" href="?home" title="Newsletter"> <?php } ?>
                        <div class="center promo <?php echo $selectionColor; ?>"
                             style="border:solid; border-color:<?php echo $selectionColor; ?>; border-style: outset; border-radius:5px;">
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

<?php include("js.php"); ?>

</body>
</html>