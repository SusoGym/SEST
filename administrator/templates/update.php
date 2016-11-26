<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
?>


<div class="container">

    <div class="card ">
        <div class="card-content">
            <div class="row">
                <b><?php echo $data['title']; ?></b>
                <br>Bitte wählen Sie eine Quelldatei
            </div>
            <form enctype="multipart/form-data"  target="myTarget" method="post"
                  action="?console&type=<?php echo $data['action']; ?>">
                <div class="row">
                    <input type="file" class="btn-flat left waves-effect waves-teal" name="file" id="file" required>
                    <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i
                            class="material-icons right">send</i></button>

                </div>

                <iframe id="myTarget" style="display: none;" name="myTarget"></iframe>
            </form>
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

        if(!success)
        {
            Materialize.toast("Fehler beim Hochladen der Datei: " + error, 4000);
        }
        else
        {
            var student = <?php echo (\View::getInstance()->getDataForView()['action'] == "uschoose") ? "true" : "false"; ?>;

            var type = student ? "dispsupdate1" : "disptupdate1";

            window.location = "?type=" + type;
        }

    }

</script>
</body>
</html>
