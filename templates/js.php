<script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
<script type="text/javascript" src="http://materializecss.com/bin/materialize.js"></script>
<script>
    $(document).ready(function () {
        $('ul.teachers').tabs();
        $('ul.students').tabs();
        $(".button-collapse").sideNav();
        $('.modal').modal();
        $('.datepicker').pickadate({
            selectMonths: true, // Creates a dropdown to control month
            selectYears: 20,
            max: new Date()
        });
    });

    var counter = 0;

    function addStudent() {
        counter++;
        if (counter <= 100) {
            var clonedNode = document.getElementById('student_blueprint').cloneNode(true);
            clonedNode.id = ''; // reset id name of clone
            clonedNode.style.display = 'block'; // remove display: none; from clone
            clonedNode.className = 'student_instance';
            var childNodes = clonedNode.childNodes;
            childNodes.forEach(function (childNode) {
                var nodeName = childNode.name;
                if (nodeName)
                    childNode.name = nodeName + "[" + counter + "]";
            });
            var insertHere = document.getElementById('student_placeholder');
            insertHere.parentNode.insertBefore(clonedNode, insertHere);
            $('.datepicker').pickadate({
                selectMonths: true, // Creates a dropdown to control month
                selectYears: 20,
                max: new Date()
            });
        }

        initDatepick();
    }

    $(document).ready(function () {
        addStudent(); // -> create one default student field

    });


    function openType(target) {

        console.info(target);

        var form = document.createElement('form'),
            node = document.createElement("input");
        form.method = "post";
        form.style.display = "none";
        form.name = "openLink";
        form.id = "openLink";

        node.type = "text";
        node.name = "type";
        node.value = target;

        form.appendChild(node);
        document.getElementById("body").appendChild(form);
        document.forms['openLink'].submit();
    }

    /*
     $('a').click(function (e) {

     if(!e.currentTarget.href.includes("type="))
     return;

     e.preventDefault();
     var target = e.currentTarget.href.split("?")[1].split("=")[1];

     openType(target);


     });
     */
</script>
