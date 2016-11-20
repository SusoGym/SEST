<?php namespace administrator;

$tchrsids = $this->dataForView['allteachers'];
foreach ($tchrsids as $tId) {
  $teachers[] = new Teacher($tId);
  }

?>


<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <title>ESPT</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="icon" type="image/ico" href="favicon.ico">
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link type="text/css" rel="stylesheet" href="http://materializecss.com/bin/materialize.css"  media="screen,projection"/>
    <style>
      .action { margin-left: 10px; }
      .info { margin-left: 10px; font-style: italic; }
    </style>
  </head>
  <body class="grey lighten-2">

  <form id="logoutform" action="" method="post"><!-- logout form -->
      <input type="hidden" name="type" value="logout">
  </form>

    <nav>
      <div class="nav-wrapper teal">
        <a href="index.php" style="margin-left: 20px" class="brand-logo">Adminpanel</a>
        <a href="#" data-activates="mobile-nav" class="button-collapse" style="padding-left:20px;padding-right:20px;"><i class="material-icons">menu</i></a>
        <ul id="nav-mobile" class="right hide-on-med-and-down">
          <li><a  href="<?php echo $this->dataForView['backButton']; ?>"><i class="material-icons">arrow_back</i></a></li>
        </ul>
      </div>
    </nav>

    <div class="container">

      <div class="card ">
        <div class="card-content">
          <div class="row">
            <div class="col l3 hide-on-med-and-down">
              <form>
			  <ul class="forms collection">
			    <?php foreach ($this->dataForView['allForms'] as $f) { ?>
                  <li class="tab"><a class="collection-item" onClick="chooseForm('<?php echo $f; ?>')" href="#"><?php echo $f; ?></a></li>
                <?php } ?>
              </ul>
			  </form>
            </div>
            <div class="col l9 m12 s12">
              <?php if(isset($this->dataForView['currentForm'])) { ?>
				 <div id="form<?php echo $f; ?>" class="col s12">
                  <ul class="collection with-header">
                    <li class="collection-header"><h4>Lehrer für <font class="teal-text"><?php echo $this->dataForView['currentForm']; ?></font> festlegen</h4></li>
				  </ul>
				  
				  <div class="input-field col s12">
				  <form method="POST" action="index.php?type=setclasses">
                        <input type="hidden" name="update" value="<?php echo $this->dataForView['currentForm']; ?>">
						<select multiple  name="teacher[]"  >
						<option >Bitte wählen</option>
						<?php foreach ($teachers as $t){
								
								$status="";							
								
								if( isset($this->dataForView['teachersOfForm'][ $this->dataForView['currentForm'] ]) ) {
									in_array( $t->getId(),$this->dataForView['teachersOfForm'][ $this->dataForView['currentForm'] ] ) ? $status = "selected" : $status = "";
									}
									
								?>
								<option <?php echo $status; ?> value="<?php echo $t->getId(); ?>"><?php echo $t->getName()['name'].", ".$t->getName()['surname']; ?></option>	
						<?php } ?>
						</select>
                   <button class="btn-flat right waves-effect waves-teal" id="btn_login" type="submit">Submit<i class="material-icons right">send</i></button>
				   </form>
				   </div>
				 
                </div> 
				  
			 <?php  }
			  else { 
				  //tell user to choose form
				  ?>
				  <ul class="collection with-header">
                    <li class="collection-header"><h4>Bitte Klasse wählen</h4></li>
				  </ul>
			  <?php }
			   
              ?>
            </div>
          </div>
        </div>
        
      </div>

    </div>
    <ul id="mobile-nav" class="side-nav">
      <li>
        <div class="userView">
          <img class="background grey" src="http://materializecss.com/images/office.jpg">
          <img class="circle" src="http://www.motormasters.info/wp-content/uploads/2015/02/dummy-profile-pic-male1.jpg">
          <span class="white-text name">Name</span>
          <span class="white-text email">Email</span>
        </div>
      </li>
      <li><a class="waves-effect teal-text" href="<?php echo $this->dataForView['backButton']; ?>"><i class="material-icons">arrow_back</i>Zurück</a></li>
      <li><div class="divider"></div></li>
      <li><a class="subheader">Klassen</a></li>
      <?php foreach ($this->dataForView['allForms'] as $f) { ?>
        <li class="tab"><a class="waves-effect" onclick="$('.button-collapse').sideNav('hide');chooseForm('<?php echo $f; ?>');"><?php echo $f; ?></a></li>
      <?php } ?>
    </ul>
   
	<!-- Include Javascript -->
     <?php include("js.php") ?>
    
  </body>
</html>
