<?php namespace administrator;
include("header.php");
$data = \View::getInstance()->getDataForView();
$user = $data['user'];


?>

<div class="container">
    
    <div class="card">
        <div class="card-content">
			<span class="card-title">
					<a id="backButton" class="mdl-navigation__link waves-effect waves-light teal-text"
                       href="?type=news">
						 <i class="material-icons">chevron_left</i>
					</a>
                <?php echo \View::getInstance()->getTitle(); ?>
			</span>
            <div class="row"><?php echo $data["newsletter"]->makeViewText($user); ?></div>
        </div>
    
    
    </div>

</div>


<!-- Include Javascript -->
<?php include("js.php") ?>


</body>
</html>
