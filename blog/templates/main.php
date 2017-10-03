<html>
<head>
    <link rel="icon" type="image/ico" href="favicon.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="./templates/style.css">
</head>
<body class="grey lighten-2" id="body" style="height: 100.06vh;">
    
    <nav>
        <div class="nav-wrapper teal">
            <span class="brand-logo center">Suso-Blog</span>
            <a href="#" data-activates="slide-out" class="button-collapse"> <i class="material-icons">menu</i> </a>
            <ul class="right hide-on-med-and-down">
                <li class="loginbtn hidden">
                    <a href="#login"><span>Login</span><i class="material-icons right">person</i></a>
                </li>
                <li class="logoutbtn hidden">
                    <a href="javascript:void(0);" class="suso-replace" id="logout"><span>Logout</span><i
                                class="material-icons right">power_settings_new</i></a>
                </li>
            </ul>
            <ul class="left hide-on-med-and-down">
                <li>
                    <a id="home" href=".." title="Home" class="waves-effect"><i class="material-icons left">home</i>
                        <font
                                style="font-size: 24px">Suso-Intern</font></a>
                </li>
            </ul>
        </div>
        <ul id="slide-out" class="side-nav" style="z-index:999;">
            <li>
                <img class="logo-mobile" src="../assets/logo.png" alt="Logo">
            </li>
            <li>
                <a id="home" href=".." title="Home" class="waves-effect"><i
                            class="material-icons left">arrow_back</i> <font
                            style="font-size: 24px">Suso-Intern</font></a>
            </li>
            <li class="loginbtn hidden">
                <a href="#login"><span>Login</span><i class="material-icons left">person</i></a>
            </li>
            <li class="logoutbtn hidden">
                <a href="javascript:void(0);" onclick="logout();"><span>Logout</span><i class="material-icons left">power_settings_new</i></a>
            </li>
        </ul>
    
    </nav>
    
    <div id="login" class="modal">
        <div class="modal-content">
            <h4>Anmelden</h4>
            <form class="row suso-replace" action="javascript:void(0);" id="login_form">
                <div class="input-field col l6 m6 s12">
                    <i class="material-icons left prefix">person</i> <input id="usr_login" type="text">
                    <label for="usr_login">Email-Adresse / Schul-Nutzername</label>
                </div>
                <div class="input-field col l6 m6 s12">
                    <i class="material-icons left prefix">vpn_key</i> <input id="pwd_login" type="password">
                    <label for="pwd_login">Passwort</label>
                </div>
                <input type="submit" style="display: none;">
            </form>
        </div>
        <div class="modal-footer">
            <a href="javascript:void(0);" id="login_btn"
               class="modal-action waves-effect waves-light btn-flat suso-replace">Anmelden<i
                        class="material-icons right">send</i></a>
        </div>
    </div>
    
    
    <div class="container" style="padding-top: 10px;">
        
        <ul id="createPost" class="collapsible" data-collapsible="accordion" permission="PERMISSION_ADD_POST PERMISSION_VIEW_EDITOR_PANEL">
            <li>
                <div class="collapsible-header"><i class="material-icons">create</i>Beitrag verfassen</div>
                <div class="collapsible-body white">
                    <form id="createForm" action="javascript:void(0);" class="row suso-replace"
                          style="margin:0px;">
                        <div class="input-field col s12">
                            <i class="material-icons left prefix">title</i>
                            <input id="createTitle" type="text" required> <label for="createTitle">Titel</label>
                        </div>
                        <div class="input-field col s12">
                            <textarea id="createText" placeholder="Text" class="materialize-textarea"></textarea>
                        </div>
                        <button type="submit" class="btn teal right" style="margin:10px;"><i
                                    class="material-icons right">send</i>Veröffentlichen
                        </button>
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
                <a id="delete" href="javascript:void(0);" class="suso-replace"
                   style="position:absolute;top:60px;right:20px;"
                   permission="PERMISSION_DELETE_POST"><i class="material-icons red-text">delete</i></a>
                <a id="edit" href="javascript:void(0);" class="suso-replace"
                   style="position:absolute;top:20px;right:20px;"
                   permission="PERMISSION_EDIT_POST"><i class="material-icons amber-text">edit</i></a>
            
            </div>
        </div>
        
        <div id="newdate" class="hidden ">
            <div class="" style="padding:8px;">
        <span class="card-title grey-text text-darken-2"><i class="material-icons left">today</i><span id="date"
                                                                                                       style="font-size:18px;"></span></span>
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
                <a id="deletebtn" href="javascript:void(0);"
                   class="modal-action modal-close waves-effect waves-light btn red">Löschen<i
                            class="material-icons right">delete</i></a>
                <a href="javascript:void(0);" class="modal-action modal-close waves-effect btn-flat">Abbrechen</a>
            </div>
        </div>
        
        <div id="editPost" class="modal">
            <div class="modal-content">
                <h4>Post bearbeiten</h4>
                <div class="row">
                    <div class="input-field col s12">
                        
                        <i class="material-icons left prefix">title</i> <input id="editTitle" type="text">
                        <label for="title">Titel</label>
                    </div>
                </div>
                <div class="row">
                    <div class="col s12">
                        <textarea id="editText"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a id="editBtn" href="javascript:void(0);"
                   class="modal-action modal-close waves-effect waves-light btn amber">Bearbeiten<i
                            class="material-icons right">edit</i></a>
                <a href="javascript:void(0);" class="modal-action modal-close waves-effect btn-flat">Abbrechen</a>
            </div>
        </div>

</body>
<script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/js/materialize.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/js-cookie/2.1.4/js.cookie.min.js"></script>
<script src="//cdn.ckeditor.com/4.7.3/full/ckeditor.js"></script>
<script src="./templates/suso.blogApiClient-1.0.js"></script>
<script src="./templates/susoblog.js"></script>
<script type="text/javascript">
    Suso.initialize();
    Suso.loadPage();
</script>
</html>
