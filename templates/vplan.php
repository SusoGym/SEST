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
if ($user instanceOf Teacher) {
    if ($much) {
        $modeLink = '<a href="?type=vplan&all=0" class="teal-text right"><i class="material-icons left">filter_list</i><span style="font-size:10px;" >nur eigene anzeigen</span></a>';
    } else {
        $modeLink = '<a href="?type=vplan&all=1" class="teal-text right"><i class="material-icons left">select_all</i><span style="font-size:10px;" >alle anzeigen</span></a>';
    }
    
    $showDetails = true;
} elseif ($user instanceOf StudentUser) {
    $showDetails = $user->getClass() == "11" || $user->getClass() == "12";
}

if ($data['VP_showAll'])
    $showDetails = true;

?>
<div class="container">
    
    <?php if (isset($modeLink)) {
        ?>
        <div class="card">
            <div class="card-content">
                <div class="center">
                    <?php echo $modeLink; ?>
                    <a href="<?php echo $data['icsPath']; ?>" class="teal-text btn-flat">
                        <i class="material-icons left">file_download</i>
                        Herunterladen
                    </a>
                </div>
            </div>
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
