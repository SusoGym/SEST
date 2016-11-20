<?php namespace administrator;
       include("header.php")
 ?>

    <div class="container">

      <div class="card">
        <div class="card-content">
          <div class="row">
			<?php isset($this->dataForView['title']) ? $title = $this->dataForView['title'] : $title =""; ?>
			<b><?php echo $title ?></b>
          </div>

        </div>

        </div>


    </div>


     <!-- Include Javascript -->
     <?php include("js.php") ?>


  </body>
</html>
