<?php namespace administrator;
      include("header.php"); ?>


    <div class="container">

      <div class="card ">
        <div class="card-content">


		  <div class="row">
		  <b><?php echo $this->action ?></b>
		  <br><b>Daten wurden gelesen</b><br>
		  überprüfte Datensätze: <?php echo $this->data[0] ?><br>
		  eingefügte Datensätze:   <?php echo $this->data[1] ?><br>
		  gelöschte Datensätze: <?php echo $this->data[2] ?>

		  </div>



        </div>

      </div>

    </div>



    <!-- Include Javascript -->
    <?php include("js.php") ?>

	
	
  </body>
</html>
