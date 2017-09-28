<?php namespace administrator; ?>
<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

<script>
    $(document).ready(function () {
        $('ul.forms').tabs();
        $('select').material_select();
        $(".button-collapse").sideNav();
    });
</script>

<script type="application/javascript">
    
    <?php
    
    $data = \View::getInstance()->getDataForView();
    
    if (isset($data['notifications']))
        foreach ($data['notifications'] as $not) {
            echo "Materialize.toast('" . $not['msg'] . "', " . $not['time'] . ");";
        }
    
    ?>

    function openType(target) {

        console.info(target);
        $('#insert').html('<form action="" name="openLink" method="post" style="display:none;"><input type="text" name="type" value="' + target + '" /> </form>');
        document.forms['openLink'].submit();
    }

    function chooseForm(f) {
        this.document.location.href = "?type=setclasses&form=" + f;
    }


    /*$('a').click(function (e) {

     if(!e.currentTarget.href.includes("type="))
     return;

     e.preventDefault();
     var target = e.currentTarget.href.split("?")[1].split("=")[1];

     openType(target);


     });*/


</script>
