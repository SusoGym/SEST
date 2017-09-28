<html>
<head>
  <link rel="icon" type="image/ico" href="favicon.ico">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <style media="screen">
  #sidenav-overlay {
    z-index: 998;
  }
  .logo-mobile {
    width: 80%;
    margin: 20px;
    display: block;
    margin-left: auto;
    margin-right: auto;
  }
  [permission] {
    display:none;
  }
  .hidden {
    display:none;
  }
  .embed-container {
    position: relative;
    padding-bottom: 56.25%; /* ratio 16x9 */
    height: 0;
    overflow: hidden;
    width: 100%;
    height: auto;
   }
  .embed-container iframe {

   position: absolute;
   top: 0;
   left: 0;
   width: 100%;
   height: 100%;
  }
  img
  {
   max-width:100%;
   max-height:100%;
  }
  </style>
</head>
<body class="grey lighten-2" id="body" style="height: 100.06vh;">

 <div class="navbar"></div>

  <div id="login" class="modal">
    <div class="modal-content">
      <h4>Anmelden</h4>
      <form class="row" action="javascript:void(0);" onsubmit="login();" method="">
        <div class="input-field col l6 m6 s12">
          <i class="material-icons left prefix">person</i>
          <input id="usr_login" type="text">
          <label for="usr_login">Email-Adresse / Schul-Nutzername</label>
        </div>
        <div class="input-field col l6 m6 s12">
          <i class="material-icons left prefix">vpn_key</i>
          <input id="pwd_login" type="password">
          <label for="pwd_login">Passwort</label>
        </div>
        <input type="submit" style="display: none;">
      </form>
    </div>
    <div class="modal-footer">
      <a href="javascript:void(0);" onclick="login();" class="modal-action waves-effect waves-light btn-flat">Anmelden<i class="material-icons right">send</i></a>
    </div>
  </div>


  <div class="container" style="padding-top: 10px;">

    <ul id="createPost" class="collapsible" data-collapsible="accordion" permission="PERMISSION_ADD_POST">
      <li>
        <div class="collapsible-header"><i class="material-icons">create</i>Beitrag verfassen</div>
        <div class="collapsible-body white">
          <form id="createform" action="javascript:void(0);" onsubmit="createPost();" class="row" style="margin:0px;">
            <div class="input-field col s12">
              <i class="material-icons left prefix">title</i>
              <input id="createtitle" type="text" required>
              <label for="createtitle">Titel</label>
            </div>
            <div class="switch col s12">
              <label>
                Nur Text
                <input id="switchHTML" type="checkbox">
                <span class="lever"></span>
                HTML-Text
              </label>
            </div>
            <div class="input-field col s12">
              <textarea id="createtext" placeholder="Text" class="materialize-textarea"></textarea>
            </div>
            <button type="submit" class="btn teal right" style="margin:10px;"><i class="material-icons right">send</i>Veröffentlichen</button>
          </form>
        </div>
      </li>
    </ul>

    <div id="blog-placeholder">

    </div>

    <div id="entry" class="hidden hoverable card white">
      <div class="card-content">
        <span id="title" class="card-title"></span>
        <p class="grey-text" style="margin-bottom: 4px;"><span id="author"></span>, <span id="date"></span></p>
        <p id="body" style="display= inline-block;"></p>
        <a id="delete" href="javascript:void(0);" onclick="deletePost();" style="position:absolute;top:60px;right:20px;" permission="PERMISSION_DELETE_POST"><i class="material-icons red-text">delete</i></a>
        <a id="edit" href="javascript:void(0);" onclick="editPost();" style="position:absolute;top:20px;right:20px;" permission="PERMISSION_EDIT_POST"><i class="material-icons amber-text">edit</i></a>

      </div>
    </div>

    <div id="newdate" class="hidden ">
      <div class="" style="padding:8px;">
        <span class="card-title grey-text text-darken-2"><i class="material-icons left">today</i><span id="date" style="font-size:18px;"></span></span>
      </div>
    </div>

    <div id="hiddensnippet" style="display:none;">

    </div>

    <div id="confirmdelete" class="modal">
      <div class="modal-content">
        <h4 class="red-text">Beitrag löschen?</h4>
        <p>Diese Aktion kann nicht rückgängig gemacht werden!</p>
      </div>
      <div class="modal-footer">
        <a id="deletebtn" href="javascript:void(0);" class="modal-action modal-close waves-effect waves-light btn red">Löschen<i class="material-icons right">delete</i></a>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect btn-flat">Abbrechen</a>
      </div>
    </div>

    <div id="editPost" class="modal">
      <div class="modal-content">
        <h4>Post bearbeiten</h4>
        <div class="row">
          <div class="input-field col s12">

              <i class="material-icons left prefix">title</i>
            <input id="edittitle" type="text">
            <label for="title">Titel</label>
          </div>
        </div>
        <div class="row">
          <div class="col s12">
            <textarea id="edittext"></textarea>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <a id="editbtn" href="javascript:void(0);" class="modal-action modal-close waves-effect waves-light btn amber">Bearbeiten<i class="material-icons right">edit</i></a>
        <a href="javascript:void(0);" class="modal-action modal-close waves-effect btn-flat">Abbrechen</a>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-2.2.4.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
    <script src="https://cdn.ckeditor.com/4.6.1/standard/ckeditor.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.4/js.cookie.min.js"></script>
    <script type="application/javascript">

        String.prototype.replaceAll = function(target, replacement) {
            return this.split(target).join(replacement);
        };

    <?php if (isset($_GET['destroy'])) : ?>
      Cookies.remove('auth', {path:''});
      window.location = "./";
    <?php endif; ?>

    loadPage();
    CKEDITOR.replace( 'edittext' );

    function loadPage() {
        loadNavbar();
        authenticate();
        fetchPosts();
        $('#entry').hide();
        $('#newdate').hide();
        $('[permission]').hide();
        $('.modal').modal();
        $('#switchHTML').change(switchHTML);
        $('.collapsible').collapsible();
    }

    function fetchPosts() {
      $.get('', {'console': true, 'action': 'fetchPosts'}, function(data){
        if (data.code !== 200) {
          Materialize.toast('['+data.code+'] '+data.message, 2000);
        } else {
          $('#blog-placeholder').empty();
          if(data.payload == undefined)
          {
              Materialize.toast('Es wurden keine Einträge gefunden.');
              return;
          }
          data.payload.forEach(function(element, index, array){
            if (index == 0) {
              var lastDate = new Date(0);

            } else {
              if (array[index-1].releaseDate) {
                var lastDate = new Date(array[index-1].releaseDate);
              }
               else {
                var lastDate = new Date(0);
              }
            }

            date = new Date(element.releaseDate);
            if (lastDate.getUTCFullYear()+'-'+lastDate.getUTCMonth()+'-'+lastDate.getUTCDate() !== date.getUTCFullYear()+'-'+date.getUTCMonth()+'-'+date.getUTCDate()) {

              newdate = $('#newdate').clone();
              newdate.attr('id', 'newdate'+element.id);
              $('#blog-placeholder').append(newdate);
              $('#newdate'+element.id+' #date').text(date.getDate()+'. '+(date.getMonth()+1)+'. '+date.getFullYear()+':');

              $('#newdate'+element.id).show();

            }

            card = $('#entry').clone();
            card.attr('id', 'entry'+element.id);
            $('#blog-placeholder').append(card);

            body = putDivAroundIframe(element.body);

            if(element.authorObject.displayName == null || element.authorObject.displayName == "")
            {
                $('#entry'+element.id+' #author').text("Unknown");
            } else {
                $('#entry'+element.id+' #author').text(element.authorObject.displayName);
            }

            if (date.getHours()<10) {hours='0'+date.getHours();}else{hours=date.getHours();}
            if (date.getMinutes()<10) {mins='0'+date.getMinutes();}else{mins=date.getMinutes();}
            datestring = hours+':'+mins+' Uhr';
            $('#entry'+element.id+' #date').text(datestring);
            $('#entry'+element.id+' #title').text(element.subject);
            $('#entry'+element.id+' #body').html(body);
            $('#entry'+element.id+' #delete').attr('onclick', 'confirmDelete('+element.id+')');
            $('#entry'+element.id+' #edit').attr('onclick', 'editModal('+element.id+')');

            $('#entry'+element.id).show();

            var lastDate = date;

          });
        }
      });
    }

    var ishtml = false;
    function switchHTML() {
      if (ishtml) {
        var html = CKEDITOR.instances.createtext.getSnapshot();
        $('#hiddensnippet').html(html);
        var text = $('#hiddensnippet').text();
        $('#hiddensnippet').empty();
        CKEDITOR.instances.createtext.destroy(true);
        $('textarea#createtext').css({'visibility': 'initial', 'display': 'initial'});
        $('textarea#createtext').val(text);
      } else if (!ishtml) {
        CKEDITOR.replace('createtext');
      }
      ishtml = (ishtml) ? false : true;
    }

    function createPost() {
      var title = $('#createtitle').val();
      var text = (ishtml) ? CKEDITOR.instances.createtext.getSnapshot() : $('textarea#createtext').val();
      $.post('', {'console': '', 'action': 'addPost', 'auth_token': Cookies.getJSON('auth').token, 'subject': title, 'body': text, 'releaseDate': toMYSQLDate(new Date())}, function (data){
        if (data.code == 200) {
          Materialize.toast('Post hinzugefügt', 2000);
          $('#createform').find("input[type=text], textarea").val("");
          if (CKEDITOR.instances.createtext) {

            CKEDITOR.instances.createtext.setData('');
          }
          $('.collapsible').collapsible('close', 0);
          fetchPosts();
        } else {
          Materialize.toast('['+data.code+'] '+data.message, 2000);
        }
      });
    }

    function editModal(id) {
      $('#editPost #editbtn').attr('onclick', 'editPost('+id+')');
      $('#editPost #edittitle').val($('#entry'+id+' #title').text());
      Materialize.updateTextFields();
      CKEDITOR.instances.edittext.setData($('#entry'+id+' #body').html());
      $('#editPost').modal('open');
    }

    function editPost(id) {
      title = $('#editPost #edittitle').val();
      text = CKEDITOR.instances.edittext.getSnapshot();
      $.post('', {'console': '', 'action': 'editPost', 'auth_token': Cookies.getJSON('auth').token, 'postId': id, 'subject': title, 'body': text}, function (data){
        if (data.code == 200) {
          Materialize.toast('Post erfolgreich bearbeitet', 2000);
          fetchPosts();
        } else {
          Materialize.toast('['+data.code+'] '+data.message, 2000);
        }
      });
    }

    function confirmDelete(id) {
      $('#confirmdelete #deletebtn').attr('onclick', 'deletePost('+id+')');
      $('#confirmdelete').modal('open');
    }

    function deletePost(id) {
      $.post('', {'console': '', 'action': 'deletePost', 'auth_token': Cookies.getJSON('auth').token, 'postId': id}, function(data){
        if (data.code == 200) {
          Materialize.toast('Post erfolgreich gelöscht', 2000);
          fetchPosts();
        } else {
          Materialize.toast('['+data.code+'] '+data.message, 2000);
        }
      });
    }

    function putDivAroundIframe(txt) {

        var arr = txt.match(/(<iframe.*?<\/iframe>)/g);

        var replaced = [];

        if(typeof arr !== 'undefined' && arr != null)
        { // we have an iFrame! yay
            arr.forEach(function (element, index, array) {

                if($.inArray(element, replaced) !== -1)
                    return;

                replaced.push(element);

               txt = txt.replaceAll(element, "<div class='embed-container'>" + element +"</div>");
            });
        }

        return txt;
    }

    function authenticate() {

      var loggedin = false;
      var auth = {token: '', expire: new Date()};

        var callback = function()
        {
            manageElements();
        };

      if (Cookies.getJSON('auth')) {
        auth = Cookies.getJSON('auth');
        loggedin = true;
        callback();

      } else {
          $.post('', {'console': '', 'action': 'createTokenFromSession'}, function(data){
              if (data.code === 200) {
                  auth.token = data.payload.authToken;
                  auth.expire = new Date(data.payload.expire);
                  //var expire = (auth.expire.getTime() - Date.now()) / (1000*60*60*24);
                  Cookies.set('auth', auth);
                  loggedin = true;
              }
              callback();
          });
      }
    }

    function login() {
      var pwd = $('#pwd_login').val();
      var usr = $('#usr_login').val().replace(/ /g, '');

      $.post("../", {'type': 'login', 'console': '', 'login[password]': pwd, 'login[mail]': usr}, function (data) {
        if (data == true) {
          Materialize.toast('Erfolgreich angemeldet!', 4000);
          $('.modal').modal('close');

        } else if (data == false) {
          Materialize.toast("Email-Addresse oder Passwort falsch", 4000);
          $('#pwd_login').val("");
          $('label[for="pwd_login"]').removeClass("active");
        } else {
          Materialize.toast("Unexpected response: " + data, 4000);
          console.info("Unexcpected response: " + data);
          $('#pwd_login').val("");

          $('label[for="pwd_login"]').removeClass("active");
        }

        loadPage();
      });
    }

    function logout() {
      Cookies.remove('auth');
      $.post("../", {'type': 'logout', 'console': ''}, function (data) {
        if (data.code == 200) {
          Materialize.toast('Erfolgreich abgemeldet!', 2000);
        } else {
          console.error(data);
          Materialize.toast('['+data.code+'] '+data.message, 2000);
        }

        authenticate();
      });
    }

    function manageElements() {
      $('[permission]').hide();
      var permissionsobj;
      var permissions = [];
      var userperm;
      var permarray = [];
      if (Cookies.getJSON('auth')) {
        token = Cookies.getJSON('auth').token;
      } else {
        token = '';
      }

      $.post('', {'console': '', 'action': 'getPermissions'}, function(data){
        if (data.code === 200) {
          permissionsobj = data.payload;
          Object.keys(permissionsobj).forEach(function(val){
            permissions[permissionsobj[val]] = val;
          });
          $.post('', {'console': '', 'action': 'getUserInfo', 'auth_token': token}, function(data){
            if (data.code === 200) {
              $('.loginbtn').hide();
              $('.logoutbtn').show().text(data.username);
              userperm = data.payload.permission;
              var string = userperm.toString(2);
              while (string.length < permissions.filter(Boolean).length) {
                string = "0"+string;
              }
              var i = 0;
              permissions.forEach(function(val, key){
                if (string.charAt((permissions.filter(Boolean).length-1)-Math.log2(key)) == 1) {
                  permarray[i] = permissions[key];
                  i++;
                }
              });
              permarray.forEach(function(val){
                if (val=="PERMISSION_EVERYTHING") {
                  $('[permission]').show();
                }
                $('[permission="'+val+'"]').show();
                $('.modal').modal();
              });
            } else {
              $('.loginbtn').show();
              $('.logoutbtn').hide();
            }
          });
        }
      });
      return permarray;
    }

    function toMYSQLDate(d) {

      a = d.getFullYear();
      b = d.getMonth();
      if(b < 10){b = '0'+b;}
      c = d.getDate();
      if(c < 10){c = '0'+c;}
      e = d.getHours();
      if(e < 10){e = '0'+e;}
      f = d.getMinutes();
      if(f < 10){f = '0'+f;}
      g = d.getSeconds();
      if(g < 10){ g = '0'+g;}
      return a+'-'+b+'-'+c+' '+e+':'+f+':'+g;
    }

    function loadNavbar() {
        $(".navbar").load("templates/navbar.php", function () {
            $(".button-collapse").sideNav();
        });
    }
    </script>
  </body>
  </html>
