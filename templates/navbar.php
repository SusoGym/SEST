<?php
    $data = $this->getDataForView();

    $selectionActive = $est = $vplan = $events = $news = true;
    $today = date("Ymd");

    $userObj = Controller::getUser();

    if ($userObj instanceof Guardian)
    {
        $children = $data['children'];
        if (count($children) == 0)
        {
            $est = false;
            $selectionActive = false;
        }
    }

    if ($est && (isset($data['book_end']) && $data['book_end'] < $today) || (isset($data['book_start']) && $data['book_start'] > $today))
    {
        ChromePhp::info("No children selected by guardian or booking time expired");
        $est = false;
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


    $modules = array();

    array_push($modules, array("id" => "home", "href" => ".", "title" => "Home", "icon" => "home", "inner" => "<font style='font-size: 24px;'>Suso-Intern</font>"));

    if ($userObj instanceof Guardian)
    {

        array_push($modules, array("id" => "est", "href" => $est ? "?type=eest" : "", "title" => "Elternsprechtag", "icon" => "supervisor_account", "classes" => $color[$est]));

    } else
    {

        array_push($modules, array("id" => "est", "href" => $est ? "?type=lest" : "", "title" => "Elternsprechtag", "icon" => "supervisor_account", "classes" => $color[$est]));

    }

    array_push($modules, array("id" => "vplan", "href" => $vplan ? "?type=vplan" : "", "title" => "Vertretungsplan", "icon" => "dashboard", "classes" => $color[$vplan]));
    array_push($modules, array("id" => "events", "href" => $events ? "?type=events" : "", "title" => "Termine", "icon" => "today", "classes" => $color[$events]));
    array_push($modules, array("id" => "news", "href" => $news ? "?type=news" : "", "title" => "Newsletter", "icon" => "library_books", "classes" => $color[$news]));

    foreach ($modules as $module)
    {
        $id = $module['id'];
        $link = $module['href'];
        $title = $module['title'];
        $icon = $module['icon'];
        $inner = (isset($module['inner'])) ? $module['inner'] : $title;
        $classes = (isset($module['classes'])) ? $module['classes'] : "";

        ?>
        <li><a id="<?php echo $id ?>" href="<?php echo $link ?>" title="<?php echo $title ?>"
               class="<?php echo $classes ?>"><i
                        class="material-icons left"><?php echo $icon ?></i><?php echo $inner ?></a></li>
        <?php
    }

?>
