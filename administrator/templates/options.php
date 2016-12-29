<?php namespace administrator;
    include("header.php");

    $data = \View::getInstance()->getDataForView();
	$isText = false;
	foreach ($data["options"] as $o){
		if($o['field']){
			$isText = true;
			break;
			}
		}
                  
?>


<div class="container">
    
    <div class="card">
	<form autocomplete="off" action="?type=options&sbm" class="row"  method="POST" style="margin: 20px;">
        <div class="card-content">
          <span class="card-title">
            <?php if (isset($data["backButton"]))
            { ?>
                <a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                   href="<?php echo $data["backButton"]; ?>"><i
                            class="material-icons">chevron_left</i></a>
            <?php } ?>
            <?php echo \View::getInstance()->getTitle(); ?>
          </span>
          <p  style="margin-top: 20px;">
	   <?php
                foreach ($data["options"] as $o){ 
			if(!$o['field']) {?>
                    		<div class="input-field col s12 l4 m6">
                            	<input name="<?php echo $o["type"]; ?>" type="text" class="" value="<?php echo $o['value']; ?>"required>
                            	<label for=?php echo $o["type"]; ?>  <?php echo $o['kommentar']; ?> </label>
                        	</div>
                  <?php
                  		}
			} ?>

            </p>
		<?php if($isText){
			foreach ($data["options"] as $o){ 
                    		if($o['field']){?>
					<div class="row"></div>
					<div class="row" >
					<p><span class="teal-text"><?php echo $o['kommentar']; ?></span></p> 
                            	<textarea wrap="soft" name="<?php echo $o["type"]; ?>" row="5">
                            	<?php echo $o['value']; ?>
					</textarea>
                        		</div>
               <?php			}
                  		} 

		}?>

		<div class="row" style="margin-bottom: 0;">
                 <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i class="material-icons right">send</i></button>
              </div>
    		</form>	
        </div>
	
    </div>
</div>

<?php include "js.php"; ?>
</body>
</html>
