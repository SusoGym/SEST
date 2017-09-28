<?php
$data = $this->getDataForView();
/** @var Guardian $user */
$user = $data['user'];

$today = date("Ymd");
include("header.php");
$day = date_format(date_create($data['estdate']), 'd.m.Y');

?>

<div class="container col s4 m4 l4">
    <div class="card ">
        <div class="card-content">
            
            
            <?php
            if ($today > $data['book_end']) {
                ?>
                <span class="card-title">Ihre Termine für den Elternsprechtag am <?php echo $day; ?></span>
                <?php include("show_bookings.php");
                
            } else {
                ?>
                <span class="card-title">Buchung für den Elternsprechtag am <?php echo $day; ?></span>
                <?php
                $teachers = $data['teachers'];
                $appointments = $data['appointments'];
                $maxAppointments = $data['maxAppointments'];
                $maxedOutAppointments = count($appointments) >= $maxAppointments;
                include("do_bookings.php");
                
            }
            ?>
            
            <div class="row">
                <div class="col l12 m12 s12">
                
                </div>
            </div>
        </div>
        <div class="card-action center">
            &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
        </div>
    </div>

</div>


<?php include("js.php"); ?>
<script>
    <?php
    if (isset($data['notifications']))
        foreach ($data['notifications'] as $not) {
            echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
        }
    
    ?>
</script>
</body>
</html>
