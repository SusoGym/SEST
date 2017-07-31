<?php

//$model = Model::getInstance();
$data = $this->getDataForView();

/** @var Teacher $usr */
$usr = $data['user'];

include("header.php");


$vpmail = $data['vpmail'];
$newsmail = $data['newsmail'];
$newshtml = $data['newshtml'];
$vpview = $data['vpview'];
$namestatus = "disabled";
if ($vpmail) {
    $vpmailStatus1 = "checked";
    $vpmailStatus2 = null;
} else {
    $vpmailStatus1 = null;
    $vpmailStatus2 = "checked";
}
if ($newsmail) {
    $newsmailStatus1 = "checked";
    $newsmailStatus2 = null;
	$htmlButton = null;
} else {
    $newsmailStatus1 = null;
    $newsmailStatus2 = "checked";
	$htmlButton = "disabled";
}
if ($vpview) {
    $vpviewStatus1 = "checked";
    $vpviewStatus2 = null;
	
} else {
    $vpviewStatus1 = null;
    $vpviewStatus2 = "checked";
}	
if($newshtml) {
	$newshtmlStatus1 = "checked";
	$newshtmlStatus2 = null;
	
	}
else {
	$newshtmlStatus1 = null;
	$newshtmlStatus2= "checked";
	
	}



?>

<div class="container">

    <div class="card">
        <div class="card-content">
             <span class="card-title">
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
						 <i class="material-icons">chevron_left</i>
					</a>
                 Nutzereinstellungen aktualisieren:
				</span>
            <form onsubmit="submitForm()" action="javascript:void(0);" autocomplete="off">
                <div class="row">
                    <div class=" col s12 l6 m6">
                        <label for="f_vpmail">Erhalte Email bei Änderungen im Vertretungsplan:<br></label>
                        <input class="with-gap" name="f_vpmail" type="radio" id="radio1"
                               value="true"<?php echo $vpmailStatus1; ?> >
                        <label for="radio1">ja</label>
                        <input class="with-gap" name="f_vpmail" type="radio" id="radio2"
                               value="false"<?php echo $vpmailStatus2; ?> >
                        <label for="radio2">nein</label>
                    </div>
                    <div class=" col s12 l6 m6">
                        <label for="f_vpview">Standardansicht Vertretungsplan:<br></label>
                        <input class="with-gap" name="f_vpview" type="radio" id="radio3"
                               value="false" <?php echo $vpviewStatus2; ?> >
                        <label for="radio3">nur eigene</label>
                        <input class="with-gap" name="f_vpview" type="radio" id="radio4"
                               value="true"<?php echo $vpviewStatus1; ?> >
                        <label for="radio4">alle</label>
                    </div>
                    <div class=" col s12 l6 m6">
                        <label for="f_newsmail">Erhalte Newsletter per Email:<br></label>
                        <input class="with-gap" name="f_newsmail" type="radio" id="radio5"
                               value="true"<?php echo $newsmailStatus1; ?> >
                        <label for="radio5">ja</label>
                        <input class="with-gap" name="f_newsmail" type="radio" id="radio6"
                               value="false"<?php echo $newsmailStatus2; ?> >
                        <label for="radio6">nein</label>
                    </div>
					<div class=" col s12 l6 m6">
                        <label for="f_newshtml">Newsletter im HTML-Format:<br></label>
                        <input class="with-gap" name="f_newshtml" type="radio" id="radio7"
                               value="true"<?php echo $newshtmlStatus1; ?> <?php if (isset($htmlButton)) {echo $htmlButton;} ?> >
                        <label for="radio7">ja</label>
                        <input class="with-gap" name="f_newshtml" type="radio" id="radio8"
                               value="false"<?php echo $newshtmlStatus2; ?> <?php if (isset($htmlButton)) {echo $htmlButton;} ?> >
                        <label for="radio8">nein</label>
                    </div>
                </div>
                <!-- TODO: check with password? -->
                <div class="row">
                    <div class="input-field col s6 l6 m6 offset-s6 offset-l6 offset-m6">
                        <button class="btn waves-effect waves-light" type="submit">Update
                            <i class="material-icons right">send</i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card-action center">
        &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
    </div>
</div>

<?php include("js.php"); ?>

<script type="application/javascript">
    function submitForm() {
        var vpmail = $("input:radio[name ='f_vpmail']:checked").val();
        var vpview = $("input:radio[name ='f_vpview']:checked").val();
        var newsmail = $("input:radio[name ='f_newsmail']:checked").val();
		var newshtml = $("input:radio[name ='f_newshtml']:checked").val();

        $.post("", {
            'type': 'teacher_editdata',
            'console': '',
            'data[vpmail]': vpmail,
            'data[vpview]': vpview,
            'data[newsmail]': newsmail,
			'data[newshtml]': newshtml


        }, function (data) {

            try {
                if (data.success) {
                    location.reload();
                }
                else { // oh no! ;-;
                    var notifications = data['notifications'];
                    notifications.forEach(function (data) {
                        Materialize.toast(data, 4000);
                    });

                }
            } catch (e) {
                Materialize.toast('Interner Server Fehler!');
                console.error(e);
                console.info('Request: ' + url_param);
                console.info('Response: ' + data);
            }

        });

    }
</script>

</body>
</html>
