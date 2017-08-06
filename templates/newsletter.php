<?php

//$model = Model::getInstance();
$data = $this->getDataForView();
$user = $data['user'];
include("header.php");
$yr = date('Y');
$mt = date('m');

if(!isset($data['newsletters']) || $data['newsletters'] == null)
{
    $data['newsletters'] = array();
}

if ($mt>7) {
	$currentSchoolYear = $yr.'/'.($yr+1);
	}
else{
	$currentSchoolYear = ($yr-1).'/'.$yr;
	}
?>

<div class="container">

    <div class="card">
        <div class="card-content">
             <span class="card-title">
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
						 <i class="material-icons">chevron_left</i>
					</a>
                 Newsletter:
				</span>
            <?php /** @var \User $usr */
            $usr = $data['user']; ?>
				</div>
                <div class="row">
                    <div class="input-field col s12 l12 m12">
                        &nbsp;&nbsp;Wenn Sie den Newsletter automatisch per email erhalten wollen, stellen Sie dies in Ihrem Benutzeraccount ein!
                    </div>
                </div>
            		
		        <div>
				<ul class="collection">
				<?php /** @var Newsletter $news */foreach($data["newsletters"] as $news){
						if ($news->getSchoolYear() == $currentSchoolYear && $news->getSendDate() != 0) { ?>
							<li class="collection-item">
								<div>
								<span class="teal-text">
								<?php 
								$date = $news->getNewsDate();
								echo  'Newsletter vom: <b>'.$date.'</b>'; ?>
								</span>
								<span class="teal-text">
								<a class="secondary-content action " href="?type=view&nl=<?php echo $news->getId(); ?>">lesen</a>
								<a class="secondary-content action" style="color: #ff0000;">versendet am: <?php echo $news->getSendDate(); ?> </a>
								</span>
							</li>
						<?php }?>
				<?php } ?>
					
				</ul>   
				</div>
    </div>
    <div class="card-action center">
        &copy; <?php echo date("Y"); ?> Heinrich-Suso-Gymnasium Konstanz
    </div>
</div>

</div>

<?php include("js.php"); ?>



</body>
</html>
