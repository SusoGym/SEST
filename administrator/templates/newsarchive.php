<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
?>

<div class="container">

    <div class="card ">
        <div class="card-content">
		<span class="card-title">
			<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text" href=".">
						 <i class="material-icons">chevron_left</i>
			</a>
            <?php echo \View::getInstance()->getTitle(); ?>
		</span>
		<ul class="collapsible white" data-collapsible="accordion">
		<?php 
		foreach ($data["schoolyears"] as $year) { ?>
		<li>
                <div class="collapsible-header">
					<span id="year" class="left teal-text" style="font-size: 14px; font-weight:bold;"><?php echo $year; ?></span>			
				</div>
				<div class="collapsible-body">
				<ul class="collection">
				<?php foreach($data["newsletters"] as $news){ ?>
						<?php 
						if ($news->getSchoolYear() == $year) { ?>
							<li class="collection-item">
								<div>
								<span class="teal-text">
								<?php 
								$date = $news->getNewsDate();
								echo  'Newsletter vom: <b>'.$date.'</b>'; ?>
								</span>
								<span class="teal-text">
								<?php 
								if ($news->getSendDate() != 0 ) { ?>
									<a class="secondary-content action " href="?type=view&nl=<?php echo $news->getId(); ?>">lesen</a>
									<a class="secondary-content action" style="color: #ff0000;">versendet am: <?php echo $news->getSendDate(); ?> </a>
									
									<?php }
									else { ?>
									<a class="secondary-content action " href="?type=sendnews&nl=<?php echo $news->getId(); ?>">senden</a>
									<a class="secondary-content action " href="?type=enternews&nl=<?php echo $news->getId(); ?>">bearbeiten</a>
									
										
									
									<?php }
									?>
								</span>
							</li>
						<?php }?>
				<?php } ?>
				</ul>
				</div>
		</li>
		<?php } ?>
		</ul>
        </div>

    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>

</body>
</html>
