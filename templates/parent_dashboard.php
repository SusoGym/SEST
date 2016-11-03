<?php

// ALL OF THIS IS DUMMY DATA
// TODO: give real data

$model = Model::getInstance();
$tchrs = array();
//$tchrs = $model->classGetTeachers($model->studentGetClass(/*$_SESSION['user']['id']*/1));
$tchrsids = $model->getTeachers();
foreach ($tchrsids as $tchr) {
    $tchrs[$tchr] = $model->teacherGetName($tchr);
}

include("header.php");

?>

<div class="container">

    <div class="card ">
        <div class="card-content">
            <div class="row">
                <div class="col l3 hide-on-med-and-down">
                    <ul class="teachers collection">
                        <?php foreach ($tchrs as $id => $name) { ?>
                            <li class="tab"><a class="collection-item"
                                               onclick="$('html, body').animate({ scrollTop: 0 }, 200);"
                                               href="#tchr<?php echo $id; ?>"><?php echo $name['name'] . ', ' . $name['surname']; ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <div class="col l9 m12 s12">
                    <?php foreach ($tchrs as $id => $name) { ?>
                        <div id="tchr<?php echo $id; ?>" class="col s12">
                            <ul class="collection with-header">
                                <li class="collection-header"><h4>Termin bei <span
                                            class="teal-text"><?php echo $name['surname'] . " " . $name['name']; ?></span>
                                        buchen</h4></li>

                                <li class="collection-item">
                                    <div>
                                        slot
                                        <a href class="secondary-content action">
                                            <i class="material-icons green-text">forward</i>
                                        </a>
                                        <span class="secondary-content info grey-text">
                          jetzt buchen
                        </span>
                                    </div>
                                </li>

                                <li class="collection-item">
                                    <div>
                                        slot
                                        <span class="secondary-content action">
                          <i class="material-icons grey-text">check</i>
                        </span>
                                        <span class="secondary-content info grey-text">
                          gebucht
                        </span>
                                    </div>
                                </li>

                                <li class="collection-item">
                                    <div>
                                        slot
                                        <span class="secondary-content action">
                          <i class="material-icons red-text">clear</i>
                        </span>
                                        <span class="secondary-content info grey-text">
                          nicht verfügbar
                        </span>
                                    </div>
                                </li>

                            </ul>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
        <div class="card-action center">
            <div class="divider"></div>
            <br/>
            &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
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
        <?php $mobile = true; include("navbar.php"); ?>
    <li>
        <div class="divider"></div>
    </li>
    <li><a class="subheader">Teachers</a></li>
    <?php foreach ($tchrs as $id => $name) { ?>
        <li class="tab"><a class="waves-effect"
                           onclick="$('ul.teachers').tabs('select_tab', 'tchr<?php echo $id; ?>');$('.button-collapse').sideNav('hide');"><?php echo $name['name'] . ', ' . $name['surname']; ?></a>
        </li>
    <?php } ?>
</ul>

<?php include("js.php"); ?>

</body>
</html>
