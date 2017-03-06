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
            <form action="?type=<?php echo $data['action']; ?>" method="POST">
                <p>
                    Wählen Sie eine Zuordnung der Quelldaten zu den Zieldatenfeldern in der Datenbank
                </p>
                <div class="row">
                    <table width="50%" align="center">
                        <tbody>
                        
                        <?php foreach ($data['fileData'][0] as $d)
                        { ?>
                        <tr>
                            <td>
                                
                                
                                <?php echo $d ?>
                            </td>
                            <td class="input-field">
                                <select class="browser-default right" name="post_dbfield[]" title="Select a file"
                                        required>
                                    <option selected></option>
                                    <?php foreach ($data['fileData'][1] as $f) { ?>
                                        <option value="<?php echo $f; ?>"><?php echo $f; ?></option>
                                    <?php } ?>
                                </select>
                            </td>
                            <?php } ?>
                        </tr>
                        </tbody>
                    </table>

                </div>
                <div class="row">


                    <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i
                                class="material-icons right">send</i></button>

                </div>

                <input type="hidden" name="file" value="<?php echo $data['fileName'] ?>"></input>
            </form>
        </div>

    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>
