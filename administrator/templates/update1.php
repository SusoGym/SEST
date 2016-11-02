<?php namespace administrator;
	  include("header.php"); ?>


    <div class="container">

      <div class="card ">
        <div class="card-content">

          <form action="?type=<?php echo $this->actionType ?>" method="POST">
		  <div class="row">
		  <b><?php echo $this->action ?></b>
		  <br><b>Wählen Sie eine Zuordnung der Quelldaten zu den Zieldatenfeldern in der Datenbank</b>
		  </div>
		  <div class="row">
				<table width="50%" align="center">
				<tbody>

				<?php foreach($this->data[0] as $d) { ?>
				<tr>
				<td>


					<?php echo $d ?>
				</td>
				<td>
					<select class="browser-default right" name="post_dbfield[]" title="Select a file" required>
						<option selected ></option>
						<?php foreach($this->data[1] as $f) {?>
						<option ><?php echo $f; ?></option>
						<?php } ?>
					</select>
				</td>
				<?php } ?>
				</tr>
				</tbody>
				</table>

		  </div>
		  <div class="row">
			<input type="hidden" name="file" value="<?php echo $this->file ?>">


			<button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i class="material-icons right">send</i></button>

          </div>

		  </form>
        </div>

      </div>

    </div>



	<!-- Include Javascript -->
	<?php include("js.php") ?>

	
	
  </body>
</html>
