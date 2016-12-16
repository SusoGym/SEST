<?php
    $data = $this->getDataForView();
    $children = $data['children'];
    $selectionActive = $est = $vplan = $events = $news = true;
    $today = date("Ymd");
    if (isset($_SESSION['user']['id']))
    {
        $userObj = Controller::getUser();
    }
    if ((isset($data['book_end']) && $data['book_end'] < $today) || (isset($data['book_start']) && $data['book_start'] > $today) || count($children) == 0)
    {
        ChromePhp::info("No children selected by guardian or booking time expired");
        $est = false;
    }

    if (count($children) == 0)
    {
        $selectionActive = false;
    }

    $color = array(true => 'white-text', false => 'teal-text text-lighten-3');

    if (isset($data['modules']))
    {
        $modules = $data['modules'];
        if (!$modules['vplan'] || !$selectionActive)
        {
            $vplan = false;
        }
        if (!$modules['events'] || !$selectionActive)
        {
            $events = false;
        }
        if (!$modules['news'] || !$selectionActive)
        {
            $news = false;
        }
    }
?>


<li><a id="home" href="." title="Home"><i class="material-icons left">home</i><font
                style="font-size: 24px;">Suso-Intern</font></a></li>
<li><a id="childsel" href="?type=childsel" title="Home"><i class="material-icons left">face</i>Kinder verwalten</a></li>
<li><a id="est" <?php if ($est)
    {
        echo 'href="?type=eest"';
    } ?> title="Home" class="<?php echo $color[$est]; ?>"><i class="material-icons left">supervisor_account</i>Elternsprechtag</a>
</li>
<li><a id="vplan" <?php if ($vplan)
    {
        echo 'href="?type=vplan"';
    } ?> title="Home" class="<?php echo $color[$vplan]; ?>"><i class="material-icons left">dashboard</i>Vertretungsplan</a>
</li>
<li><a id="events" <?php if ($events)
    {
        echo 'href="?type=events"';
    } ?> title="Home" class="<?php echo $color[$events]; ?>"><i class="material-icons left">today</i>Termine</a></li>
<li><a id="news" <?php if ($news)
    {
        echo 'href="?type=news"';
    } ?> title="Home" class="<?php echo $color[$news]; ?>"><i
                class="material-icons left">library_books</i>Newsletter</a></li>
