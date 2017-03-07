<?php
$data = $this->getDataForView();
/** @var Guardian $user */
$user = $data['user'];
$today = date("Ymd");
include("header.php");
$today = date('d.m.Y');
$todayMonth = date('Ym');
//$today="12.10.2016";//Nur zum Debugging
$todayTimestamp = strtotime($today);

$showDetails = false;
$much = $data['VP_showAll'];
$showClass = false;

if ($user instanceOf Teacher) {
    if ($much) {
        $modeLink = '<li><a href="?type=vplan&all=0" class="btn-floating teal tooltipped" data-position="left" data-tooltip="eigene anzeigen"><i class="material-icons left">filter_list</i></a></li>';
    } else {
        $modeLink = '<li><a href="?type=vplan&all=1" class="btn-floating teal tooltipped" data-position="left" data-tooltip="alle anzeigen"><i class="material-icons left">select_all</i></a></li>';
    }
    
    $showDetails = true;
    $showClass = true;
} elseif ($user instanceOf StudentUser) {
    $showDetails = $user->getClass() == "11" || $user->getClass() == "12";
} elseif ($user instanceof Guardian) {
    $showClass = true;
}

if ($data['VP_showAll']) {
    $showDetails = true;
    $showClass = true;
}

?>
<div class="container">
    
    <?php if (isset($modeLink)) {
        ?>
        <div class="fixed-action-btn">
            <a class="btn-floating btn-large teal">
                <i class="large material-icons">more_vert</i>
            </a>
            <ul>
                <?php echo $modeLink; ?>
            </ul>
        </div>
        <?php
    } ?>
    
    <?php
    $dayNr = 0;
    foreach ($data["VP_allDays"] as $day) {
        $events = $data['VP_termine'];
        $timestamp = $day['timestamp'];
        if ($much) {
            $absentTeachers = $data['VP_absentTeachers'][$timestamp];
            $blockedRooms = $data['VP_blockedRooms'][$timestamp];
        }
        $coverLessons = isset($data['VP_coverLessons'][$timestamp]) ? $data['VP_coverLessons'][$timestamp] : null;
        
        include("vplanDay.php");
        $dayNr++;
    } ?>

    <div class="card">
        <div class="card-content">
            <div class="center grey-text">
                <?php echo "Stand: " . $data["VP_lastUpdate"]; ?>
            </div>
        </div>
    </div>
</div>

<?php include("js.php"); ?>

</body>
</html>
