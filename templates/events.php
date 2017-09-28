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
$modeLink = '<li><a href="?type=events&all" class="btn-floating teal tooltipped" data-position="left" data-tooltip="Alle anzeigen"><i class="material-icons">select_all</i></a></li>';
//Für Anzeige des kompletten Jahres wird todayTimestamp auf das Datum des ersten Termins gesetzt.
if (isset($data['showAllEvents'])) {
    $first = $data['events'][0]->sday;
    $todayTimestamp = strtotime($first);
    $todayMonth = $data['events'][0]->jahr . $data['events'][0]->monatNum;
    $modeLink = '<li><a href="?type=events" class="btn-floating teal tooltipped" data-position="left" data-tooltip="Aktuelle anzeigen"><i class="material-icons">filter_list</i></a></li>';
}
?>


<div class="container">
    
    <div class="fixed-action-btn">
        <a class="btn-floating btn-large teal">
            <i class="large material-icons">more_vert</i>
        </a>
        <ul>
            <?php echo $modeLink; ?>
            <li><a href="<?php echo $data['icsPath']; ?>" class="btn-floating teal tooltipped" data-position="left"
                   data-tooltip="Termine herunterladen"><i class="material-icons">file_download</i></a></li>
        </ul>
    </div>
    <?php foreach ($data['months'] as $month) {
        $yearmonth = $month["jahr"] . $month["mnum"];
        if ($todayMonth <= $yearmonth) { ?>
            <div class="card ">
                <div class="card-content">
              <span class="card-title"><i
                          class="material-icons left">today</i><?php echo $month['mstring'] . " " . $month['jahr']; ?></span>
                    <ul class="collection">
                        <?php foreach ($data['events'] as $t) {
                            if ($t->monatNum == $month["mnum"]) {
                                ?>
                                <li class="collection-item">
                                    <span class="title"><b><?php echo $t->typ ?></b></span>
                                    <p class="grey-text">
                                        <?php echo " " . $t->sweekday . " " . $t->sday . " "; ?>
                                        <?php if (isset($t->stime)) { ?><?php echo ' (' . $t->stime . ')';
                                        } ?>
                                        <?php if (isset($t->eday)) { ?><?php echo "-"; ?>
                                            <?php echo $t->eweekday ?>
                                            <?php echo $t->eday;
                                            if (isset($t->etime)) {
                                                echo ' (' . $t->etime . ')';
                                            } ?>
                                        <?php } ?>
                                    </p>
                                </li>
                                <?php
                            }
                        } ?>
                    </ul>
                </div>
            </div>
            <?php
        }
    } ?>

</div>

<?php include("js.php"); ?>

</body>
</html>
