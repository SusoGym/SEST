<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="google-site-verification" content="afR-m_0mxdzKpJL4S5AM5JnImHvvDpxGw5WxU6S1zDk" />
    <title>Suso-Gymnasium</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!--suppress HtmlUnknownTarget -->
    <link rel="icon" type="image/ico" href="favicon.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">

    <style>
        .action {
            margin-left: 10px;
        }

        .info {
            margin-left: 10px;
            font-style: italic;
        }

        .logo-mobile {
            width: 80%;
            margin: 20px;
            display: block;
            margin-left: auto;
            margin-right: auto
        }

        .name {
            margin: 20px;
        }

        #mobilevptable th {
            text-align: left !important;
        }

        #mobilevptable tr {
            max-width: 80%;
            text-align: left !important;
        }
    </style>
</head>
<body class="grey lighten-2" id="body" style="height: 100vh;">

<form id="logoutform" action="" method="post"><!-- logout form -->
    <input type="hidden" name="type" value="logout">
</form>
<div class="navbar-fixed">

    <nav>
        <div class="nav-wrapper teal">
            <span class="hide-on-large-only brand-logo">Suso-Intern</span>
            <a href="#" data-activates="slide-out" class="button-collapse">
                <i class="material-icons">menu</i>
            </a>
            <ul class="left hide-on-med-and-down">
                <?php include("navbar.php"); ?>
            </ul>
            <?php if (Controller::getUser() != null): //if logged in ?>
                <ul class="right hide-on-med-and-down">
                    <li>
                        <a id="logout" href="?type=logout" title="Logout">
                            <i class="material-icons right">power_settings_new</i>
                            Log Out
                        </a>
                    </li>
                </ul>
            
            <?php endif; ?>
        </div>
    </nav>
</div>

<ul id="slide-out" class="side-nav">
    <li>
        <img class="logo-mobile" src="assets/logo.png">
    </li>
    <?php include("navbar.php"); ?>
    <div class="divider"></div>
    <li>
        <a id="logout" href="?type=logout" title="Logout">
            <i class="material-icons left">power_settings_new</i>
            Log Out
        </a>
    </li>
</ul>
