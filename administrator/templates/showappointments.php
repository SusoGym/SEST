<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
?>


<div class="container">
    <div class="card white">
        <div class="card-content">
            <span class="card-title"><?php echo \View::getInstance()->getTitle(); ?></span>
            <?php foreach ($data['teachers'] as $tchr) { ?>
            <div class="row">
                <div class="col s12 l12 m12 teal-text">
                    <?php 
                    echo $tchr->getFullName().'<br/>'; 
                    $appointments = $tchr->getAppointmentsOfTeacher();
                    //var_dump($appointments);
                    if (count($appointments) >0 ) {
                            foreach ($appointments as $appointment){
                            echo $appointment['parent']->getFullName(). " -- ". $appointment['parent']->getEmail().'<br/>';
                            }
                    } 
                     
                    
                    ?>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>


<!-- blueprint for collapsible list -->

<li id="row_blueprint" style="display: none;">
      <div class="collapsible-header" name="listheader"></div>
      <div class="collapsible-body" name="listbody"></div>
    </li>

<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>