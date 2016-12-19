<?php
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

    if (count($children) == 0)
    {
        $selectionActive = false;
    }

    if (isset($data['modules']))
    {
        $modules = $data['modules'];
        if (!$modules['vplan'] || !$selectionActive)
        {
            $vplanColor = "grey";
            $vplanLink = "";
        } else
        {
            $vplanColor = "teal";
            $vplanLink = 'href="' . "?type=vplan";
        }
        if (!$modules['events'] || !$selectionActive)
        {
            $eventsColor = "grey";
            $eventsLink = "";
        } else
        {
            $eventsColor = "teal";
            $eventsLink = 'href="' . "?type=events";
        }
        if (!$modules['news'] || !$selectionActive)
        {
            $newsColor = "grey";
            $newsLink = "";
        } else
        {
            $newsColor = "teal";
            $newsLink = 'href="' . "?type=news";
        }
    }
    include("header.php");
?>
<div class="container">
    <div class="card white">
        <div class="card-content">
            <span class="card-title">Startseite</span>


            <!-- Where is my content? ;-; -->
        </div>
    </div>
</div>


<?php include("js.php"); ?>

</body>
</html>
