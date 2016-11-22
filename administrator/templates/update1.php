<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
?>


<div class="container">

    <div class="card ">
        <div class="card-content">

            <form action="?type=<?php echo $data['action']; ?>" method="POST">
                <div class="row">
                    <b><?php echo $data['title']; ?></b>
                    <br><b>Wählen Sie eine Zuordnung der Quelldaten zu den Zieldatenfeldern in der Datenbank</b>
                </div>
                <div class="row">
                    <table width="50%" align="center">
                        <tbody>

                        <?php foreach ($data['fileData'][0] as $d) { ?>
                        <tr>
                            <td>


                                <?php echo $d ?>
                            </td>
                            <td>
                                <select class="browser-default right" name="post_dbfield[]" title="Select a file"
                                        required>
                                    <option selected></option>
                                    <?php foreach ($data['fileData'][1] as $f) { ?>
                                        <option><?php echo $f; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <?php } ?>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <div class="row">
                    <input type="hidden" name="file" value="<?php echo $data['fileName'] ?>">


                    <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i
                            class="material-icons right">send</i></button>

                </div>

            </form>
        </div>

    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>
