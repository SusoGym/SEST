  

	<?php include("header.php"); ?>
    

    <div class="container">

      <div class="card ">
        <div class="card-content">
          <form enctype="multipart/form-data" onsubmit="submitFile($this->actionType)" action="index.php?type=<?php echo $this->actionType; ?>" method="POST">
		  <div class="row">
		  <b><?php echo $this->action ?></b>
		  <br>Bitte wählen Sie eine Quelldatei
		  </div>
		  <div class="row">
			<input type="hidden" name="MAX_FILE_SIZE" value="200000">
			
			<input type="file" class="btn-flat left waves-effect waves-teal" name="Datei">
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
		
		 function submitFile(actionType)
      {
          var file = $('#Datei').val();
          var url = "?console&type=uschoose;
		  var actionType=actionType;
          console.info(url);

          $.get( "index.php?console&type="+actionType, function (data) {

              if(data === "true")
              {
                  location.reload();
              } else {
                  Materialize.toast("file upload failed", 4000);
                  
              }
          });

          return false;
      }
    </script>
  </body>
</html>
