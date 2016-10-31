

  

	<?php include("header.php"); ?>
    

    <div class="container">

      <div class="card ">
        <div class="card-content">
						
          
		  <div class="row">
		  <b><?php echo $this->action ?></b>
		  <br><b>Daten wurden gelesen</b><br>
		  <?php echo $this->data[0] ?> Datensätze eingefügt <br>
		  <?php echo $this->data[1] ?> Datensätze gelöscht 
		  </div>
		  
		  
		  
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
