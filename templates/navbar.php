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

if ($est && (isset($data['est_date']) && $data['est_date'] < $today) || (isset($data['book_start']) && $data['book_start'] > $today))
{
  ChromePhp::info("No children selected by guardian or booking time expired");
  $est = false;
}


$color = array(true => '', false => 'teal-text text-lighten-3');

if (isset($data['modules'])) {
  $modules = $data['modules'];
  if (!$modules['vplan'] || !$selectionActive) {
    $vplan = false;
  }
  if (!$modules['events'] || !$selectionActive) {
    $events = false;
  }
  if (!$modules['news'] || !$selectionActive) {
    $news = false;
  }
}


$modules = array();

array_push($modules, array("id" => "home", "href" => ".", "title" => "Home", "icon" => "home", "inner" => "<font style='font-size: 24px;'>Suso-Intern</font>"));

if ($userObj instanceof Guardian) {
  array_push($modules, array("id" => "childsel", "href" => "?type=childsel", "title" => "Kinder verwalten", "icon" => "face"));
  if ($est) {
    array_push($modules, array("id" => "est", "href" => "?type=eest", "title" => "Elternsprechtag", "icon" => "supervisor_account"));
  }

} else {
  if ($est) {
    array_push($modules, array("id" => "est", "href" => "?type=lest", "title" => "Elternsprechtag", "icon" => "supervisor_account"));
  }
}

if ($vplan) {
  array_push($modules, array("id" => "vplan", "href" => "?type=vplan", "title" => "Vertretungsplan", "icon" => "dashboard"));
}
if ($events) {
  array_push($modules, array("id" => "events", "href" => "?type=events", "title" => "Termine", "icon" => "today"));
}
if ($news) {
  array_push($modules, array("id" => "news", "href" => "?type=news", "title" => "Newsletter", "icon" => "library_books"));
}

foreach ($modules as $module)
{
  $id = $module['id'];
  $link = $module['href'];
  $title = $module['title'];
  $icon = $module['icon'];
  $inner = (isset($module['inner'])) ? $module['inner'] : $title;

  ?>
  <li>
    <a id="<?php echo $id ?>" <?php if ($link != "") echo "href='$link'" ?> title="<?php echo $title ?>" class="waves-effect">
      <i class="material-icons left">
        <?php echo $icon ?>
      </i>
      <?php echo $inner ?>
    </a>
  </li>
  <?php
}

?>
