<?php namespace administrator;

$data = \View::getInstance()->getDataForView();

$teachers = $data['allteachers'];

include "header.php";
?>


<div class="container">
    <div class="card ">
        <div class="card-content">
          <span class="card-title">
            <?php if (isset($data["backButton"])) { ?>
                <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                   href="<?php echo $data["backButton"]; ?>"><i
                            class="material-icons">chevron_left</i></a>
            <?php } ?>
              <?php echo \View::getInstance()->getTitle(); ?>
          </span>
            <div class="row">
                <div class="col l3 hide-on-med-and-down">
                    <form>
                        <ul class="forms collection">
                            <?php foreach ($data['allForms'] as $f) {
                                $classes = "collection-item";
                                
                                if (isset($data['currentForm']) && $data['currentForm'] == $f)
                                    $classes .= " active";
                                
                                ?>

                                <li class="tab"><a class="<?php echo $classes; ?>"
                                                   onClick="chooseForm('<?php echo "$f"; ?>')"
                                                   href="#<?php echo "$f"; ?>"><?php echo "$f"; ?></a></li>
                            <?php } ?>
                        </ul>
                    </form>
                </div>
                <div class="col l9 m12 s12">
                    <?php
                    if (isset($data['currentForm'])) {
                        $f = $data['currentForm'];
                        ?>
                        <div id="form<?php echo $f; ?>" class="col s12">
                            <h4>Lehrer für
                                <font class="teal-text"><?php echo $f ?></font>
                                festlegen</h4>

                            <div class="input-field col s12">
                                <form method="POST" action="?type=setclasses">
                                    <input type="hidden" name="update"
                                           value="<?php echo $data['currentForm']; ?>">
                                    <select multiple name="teacher[]">
                                        <?php
                                        /** @var \Teacher $t */
                                        foreach ($teachers as $t) {
                                            
                                            $status = "";
                                            
                                            if (isset($data['teachersOfForm'][$data['currentForm']])) {
                                                in_array($t->getId(), $data['teachersOfForm'][$data['currentForm']]) ? $status = "selected" : $status = "";
                                            }
                                            
                                            ?>
                                            <option <?php echo $status; ?>
                                                    value="<?php echo $t->getId(); ?>"><?php echo $t->getFullname(); ?></option>
                                        <?php } ?>
                                    </select>
                                    <button class="btn-flat right waves-effect waves-teal" id="btn_login"
                                            type="submit">
                                        Submit<i class="material-icons right">send</i></button>
                                </form>
                            </div>

                        </div>
                    
                    <?php } else {
                        //tell user to choose form
                        ?>
                        <h4>Bitte Klasse wählen</h4>
                    <?php }
                    
                    ?>
                </div>
            </div>
        </div>

    </div>

</div>
<ul id="mobile-nav" class="side-nav">
    <li>
        <div class="userView">
            <img class="background grey" src="http://materializecss.com/images/office.jpg">
            <img class="circle"
                 src="http://www.motormasters.info/wp-content/uploads/2015/02/dummy-profile-pic-male1.jpg">
            <span class="white-text name">Name</span>
            <span class="white-text email">Email</span>
        </div>
    </li>
    <li><a class="waves-effect teal-text" href="<?php echo $data['backButton']; ?>"><i
                    class="material-icons">arrow_back</i>Zurück</a></li>
    <li>
        <div class="divider"></div>
    </li>
    <li><a class="subheader">Klassen</a></li>
    <?php foreach ($data['allForms'] as $f) { ?>
        <li class="tab"><a class="waves-effect"
                           onclick="$('.button-collapse').sideNav('hide');chooseForm('<?php echo $f; ?>');"><?php echo $f; ?></a>
        </li>
    <?php } ?>
</ul>

<!-- Include Javascript -->
<?php require("js.php") ?>

<script type="application/javascript">
    $(document).ready(function () {
        $('select').material_select();
        $('.select-dropdown').addClass('active');
        $('.dropdown-content').addClass('active').css({display: "block"});
    });

</script>

</body>
</html>
