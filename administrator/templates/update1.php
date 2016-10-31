	<?php include("header.php"); ?>
    

    <div class="container">

      <div class="card ">
        <div class="card-content">
						
          <form action="index.php?type=usstart" method="POST">
		  <div class="row">
		  <b><?php echo $this->action ?></b>
		  <br><b>Wählen Sie eine Zuordnung der Quelldaten zu den Zieldatenfeldern in der Datenbank</b>
		  </div>
		  <div class="row">
				<table width="50%" aligh="center">
				<tbody>
			
				<?php foreach($this->data[0] as $d) { ?>
				<tr>
				<td>
				
					
					<?php echo $d ?> 
				</td>
				<td>
					<select class="browser-default right" name="post_dbfield[]" required>
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
    

   
	 <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script> 
	<script type="text/javascript" src="http://materializecss.com/bin/materialize.js"></script>
    <script>
        $(document).ready(function(){
          $(".button-collapse").sideNav();
        });
		
		
    </script>
	
	
  </body>
</html>
