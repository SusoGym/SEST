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
include("header.php");
?>
<div class="container">
  <div class="card white">
    <div class="card-content">
      <span class="card-title">Startseite</span>

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
