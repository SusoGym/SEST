<?php $day = date_format(date_create($data['bookingDetails'][0]["anfang"]), 'd.m.Y'); ?>
<ul class="collection with-header">
    <li class="collection-header">
        <span class="teal-text" style="font-size:24px"><?php echo "Ihre Elternsprechtermine am " . $day; ?></span>
    </li>
    <?php

        foreach ($data['bookingDetails'] as $appointment)
        {
            $anfang = date_format(date_create($appointment['anfang']), 'H:i');
            $ende = date_format(date_create($appointment['ende']), 'H:i');


            ?>


            <li class="collection-header">
                <span class="teal-text" style="font-size:14px"><?php echo $anfang . " - " . $ende; ?></span>
                <span class="teal-text right"
                      style="font-size:14px"><?php echo " bei " . $appointment['teacher']->getFullName(); ?></span>
            </li>


        <?php }


    ?>
</ul>