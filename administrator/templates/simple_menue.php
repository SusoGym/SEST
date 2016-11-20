 

    <?php include("header.php"); ?>
	
	
	
	
	
    <div class="container">

      <div class="card">
        <div class="card-content ">
          <div class="row">
          <?php isset($this->dataForView['title']) ? $title = $this->dataForView['title'] : $title =""; ?>
			<b><?php echo $title ?></b>
		  <?php if(isset($this->dataForView["backButton"])) { ?>
		          <a id="backButton" class="mdl-navigation__link right teal-text" href="<?php echo $this->dataForView["backButton"]; ?>"><i class="material-icons">arrow_back</i></a>
		<?php } ?>
         </div>
		  <?php 
		  if (isset($this->dataForView["menueItems"])) {
			foreach ($this->dataForView["menueItems"] as $m) { ?>
			<div class="row">
             <ul ><a  class="mdl-navigation__link teal-text" id="menueItem" href="<?php echo $m['link']; ?>"><?php echo $m['entry']; ?></a></ul>
			</div>
		  
		  <?php 
			}
		  } ?>
		  
		
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
