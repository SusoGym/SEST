<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
if (isset($data["editingnewsletter"])) {
	$date = $data["editingnewsletter"]->getNewsDate();
	$text = trim($data["editingnewsletter"]->getNewsText());
	}
else {
	$date = $text = null;
	}


?>

<div class="container">

    <div class="card ">
        <div class="card-content">
            <span class="card-title"><?php echo $data["title"] ; ?></span> 
		<div>
		<!-- Eingabefelder für Newsletter -->
		<form action="?type=<?php echo $data["link"]; ?>" method="POST">
		<?php if (isset($data["newsid"]) ) { ?>
			<input type="hidden" name="nl" value="<?php echo $data["newsid"]; ?> ">
		<?php } ?>
		<div class="input-field col s12 l4 m6">
		<input type="text" size="50" id="nldate" name="nldate" value="<?php echo $date; ?>" required> 
		<label for="nldate">Datum(dd.mm.yyyy)</label>
		</div>
		<a class="teal-text">Newsletter:</a><textarea rows="30" cols="200" name="nltext" > <?php echo $text; ?></textarea>
		
		</div>
		<button class="btn-flat btn-large waves-effect waves-teal col l12" type="submit">
                    <?php echo $data["button"]; ?>
                    <i class="material-icons right">send</i>
                </button>
        </form>
        </div>

    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>
<?php include("././templates/js.php") ?>

</body>
</html>
