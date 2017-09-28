<?php

include("header.php");

?>

<div class="container">
    
    <div class="card ">
        <div class="card-content">
            <span class="card-title">Hier können Sie Ihre Kinder auswählen: </span>
            <div class="container">
                <form action="?type=addstudent" class="row" method="post">
                    <span id="student_placeholder"></span>
                    <a onclick="addStudent();" class="btn-flat btn-large waves-effect waves-light teal-text col s12">Feld
                                                                                                                     hinzufügen
                        <i
                                class="material-icons right large">add</i></a>
                    <a onclick="form.submit();"
                       class="col s12  btn-large center modal-action waves-effect waves-teal btn-flat">Eingetragene
                                                                                                       Schüler
                                                                                                       hinzufügen <i
                                class="material-icons right large">send</i></a>
                </form>
            </div>
        </div>
        <div class="card-action center">
            <div class="divider"></div>
            <br/>
            &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
        </div>
    </div>

</div>

<div id="student_blueprint" style="display:none;">
    <div class="input-field col s6">
        <input id="name" name="name" type="text" class="validate">
        <label for="name">Name des Schülers</label>
    </div>
    <div class="input-field col s6">
        <input type="date" name="bday" class="datepicker">
        <label for="date">Geburtstag</label>
    </div>
</div>

<?php include("js.php"); ?>

</body>
</html>
