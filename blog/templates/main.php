<html>
<head>
 <link rel="icon" type="image/ico" href="favicon.ico">
 <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
</head>
<body class="grey lighten-2" id="body" style="height: 100vh;">

 <div id="navBar" class="navbar-fixed"></div>

 <script src="https://code.jquery.com/jquery-2.2.4.min.js"
         integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
 <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
 <script type="application/javascript">
     $(function () {
         $('#navBar').load('templates/navbar.php');
     })
 </script>

</body>
</html>