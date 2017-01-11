<?php
include("header.php");
?>
<div class="container">
  <div class="card white">
    <div class="card-content">
      <span class="card-title">Startseite</span>
      <p><?php echo $this->getDataForView()['welcomeText']; ?></p>
    </div>
  </div>
</div>


<?php include("js.php"); ?>

</body>
</html>
