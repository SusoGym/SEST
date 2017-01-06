
<ul class="collection with-header">
    
    <?php

        foreach ($data['bookingDetails'] as $appointment)
        {
            $anfang = date_format(date_create($appointment['anfang']), 'H:i');
            $ende = date_format(date_create($appointment['ende']), 'H:i');


            ?>


            <li class="collection-header">
                <span class="teal-text" style="font-size:14px"><?php echo $anfang . " - " . $ende; ?></span>
                <span class="teal-text right"
                      style="font-size:14px"><?php echo $appointment['teacher']->getFullName(); ?></span>
            </li>


        <?php }


    ?>
</ul>