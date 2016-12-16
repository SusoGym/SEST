<?php
    $data = $this->getDataForView();
    /** @var Guardian $user */
    $user = $data['user'];

    $today = date("Ymd");
    include("header.php");

?>

<div class="container col s4 m4 l4">
    <div class="card ">
        <div class="card-content">
            <div class="row">
                <div class="col l12 m12 s12">
                    <?php
                        if ($today > $data['book_end'])
                        {
                            include("show_bookings.php");
                        } else
                        {
                            $teachers = $data['teachers'];
                            $appointments = $data['appointments'];
                            $maxAppointments = $data['maxAppointments'];
                            $maxedOutAppointments = count($appointments) >= $maxAppointments;
                            include("do_bookings.php");
                        }
                    ?>


                </div>
            </div>
        </div>
        <div class="card-action center">
            &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
        </div>
    </div>

</div>


<?php include("js.php"); ?>

</body>
</html>
