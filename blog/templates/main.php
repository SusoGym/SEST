<html>
<head>
  <link rel="icon" type="image/ico" href="favicon.ico">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
</head>
<body class="grey lighten-2" id="body" style="height: 100vh;">

  <div id="navBar" class="navbar-fixed"></div>

  <div id="blog-placeholder" class="container" style="padding-top: 10px;">

  </div>

  <div id="entry" class="hoverable card white">
    <div class="card-content">
      <span id="title" class="card-title"></span>
      <p class="grey-text" style="margin-bottom: 4px;"><span id="author"></span>, <span id="date"></span></p>
      <p id="body"></p>
    </div>
  </div>

  <div id="newdate" class="">
    <div class="" style="padding:8px;">
      <span class="card-title grey-text text-darken-2"><i class="material-icons left">today</i><span id="date" style="font-size:18px;"></span></span>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-2.2.4.min.js"
  integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
  <script type="application/javascript">
  $(function () {
    $('#navBar').load('templates/navbar.php');

    $('#entry').hide();
    $('#newdate').hide();

    $.get('', {'console': true, 'action': 'fetchPosts'}, function(data){
      if (data.code !== 200) {
        Materialize.toast(data.message, 2000);
      } else {

        data.payload.forEach(function(element){

            if (!lastDate) {
              var lastDate = new Date(0);
            }

            date = new Date(element.releaseDate);
            if (lastDate.getFullYear()+'-'+lastDate.getMonth()+'-'+lastDate.getDate() !== date.getFullYear()+'-'+date.getMonth()+'-'+date.getDate()) {

              newdate = $('#newdate').clone();
              newdate.attr('id', 'newdate'+element.id);
              $('#blog-placeholder').append(newdate);
              $('#newdate'+element.id+' #date').text(date.getDate()+'. '+(date.getMonth()+1)+'. '+date.getFullYear()+':');

              $('#newdate'+element.id).show();

            }

            card = $('#entry').clone();
            card.attr('id', 'entry'+element.id);
            $('#blog-placeholder').append(card);

            $('#entry'+element.id+' #author').text(element.authorObject.displayName);
            if (date.getHours()<10) {hours='0'+date.getHours();}else{hours=date.getHours();}
            if (date.getMinutes()<10) {mins='0'+date.getMinutes();}else{mins=date.getMinutes();}
            datestring = hours+':'+mins+' Uhr';
            $('#entry'+element.id+' #date').text(datestring);
            $('#entry'+element.id+' #title').text(element.subject);
            $('#entry'+element.id+' #body').html(element.body);
            $('#entry'+element.id).show();

            var lastDate = date;

        });
      }
    });

  });




  </script>
</body>
</html>
