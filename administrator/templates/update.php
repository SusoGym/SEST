<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
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
            <form enctype="multipart/form-data" class="row" target="myTarget" method="post"
                  action="?console&type=<?php echo $data['action']; ?>">
                <div class="file-field input-field col l12">
                    <div class="btn">
                        <span>Datei</span>
                        <input type="file" name="file" id="file" required>
                    </div>
                    <div class="file-path-wrapper">
                        <input class="file-path validate" type="text" placeholder="Bitte wählen Sie eine Quelldatei">
                    </div>
                </div>
                <button class="btn-flat btn-large waves-effect waves-teal col l12" type="submit">
                    Submit
                    <i class="material-icons right">send</i>
                </button>
            </form>
            <iframe id="myTarget" style="display: none;" name="myTarget"></iframe>
        </div>

    </div>

</div>

<script>
    window.top.window.uploadComplete("");
</script>

<!-- Include Javascript -->
<?php include("js.php") ?>

<script>

    function submitFile(actionType) {
        // file has started loading
        alert("file");
    }

    function uploadComplete(success, error) {
        //file completed uploading

        if (!success) {
            Materialize.toast("Fehler beim Hochladen der Datei: " + error, 4000);
        }
        else {
            var student = <?php echo (\View::getInstance()->getDataForView()['action'] == "uschoose") ? "true" : "false"; ?>;
            var teacher = <?php echo (\View::getInstance()->getDataForView()['action'] == "utchoose") ? "true" : "false"; ?>;
			var lessons = <?php echo (\View::getInstance()->getDataForView()['action'] == "lessonchoose") ? "true" : "false"; ?>;

            //var type = student ? "dispsupdate1" : "disptupdate1";
            var type;
            if (student) {
                type = "dispsupdate1";
            } else if (teacher) {
                type = "disptupdate1";
            } else if (lessons) {
				type = "dispupdatelessons";
			} else {
                type = "dispupdateevents";
            }

            window.location = "?type=" + type;
        }

    }

</script>
</body>
</html>
