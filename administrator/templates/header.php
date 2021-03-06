<?php namespace administrator; ?>
<!DOCTYPE html>

<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />
    <meta name="google-site-verification" content="afR-m_0mxdzKpJL4S5AM5JnImHvvDpxGw5WxU6S1zDk"/>
    <title>Suso-Gymnasium</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=0"/>
    <!--suppress HtmlUnknownTarget -->
    <link rel="icon" type="image/ico" href="favicon.ico">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/materialize/0.98.0/css/materialize.min.css">
    <script src="https://code.jquery.com/jquery-2.2.4.min.js"
        integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>

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
	
<script type="text/javascript">
$(document).ready(function () {
        
        $(".button-collapse").sideNav();
        $('ul.collapsible').collapsible();
        
    });
</script>
</head>

<body class="grey lighten-2" style="height: 100vh;">

<div class="navbar-fixed">
<nav>
    <div class="nav-wrapper teal">
        <span class="hide-on-large-only brand-logo">Suso-Intern:Admin</span>
        <a href="#" data-activates="slide-out" class="button-collapse">
                    <i class="material-icons">menu</i>
                </a>
        <ul class="left hide-on-med-and-down">
            <li><a href="." title="Startseite" class="waves-effect"><i class="material-icons left">home</i><font
                            style="font-size: 24px;">Suso-Intern: Admin</font></a></li>
            <li><a href="?type=updmgt" title="Datenabgleich" class="waves-effect"><i
                            class="material-icons left">input</i>Datenabgleich</a></li>
            <li><a href="?type=settings" title="Einstellungen" class="waves-effect"><i class="material-icons left">settings</i>Einstellungen</a>
            </li>
            <li><a href="?type=usrmgt" title="Benutzerverwaltung" class="waves-effect"><i class="material-icons left">people</i>Verwaltung</a>
            </li>
            <li><a href="?type=news" title="Newslettereintrag" class="waves-effect"><i class="material-icons left">comment</i>Newsletter</a>
            </li>
        </ul>
        <ul class="right hide-on-med-and-down">
            <li><a href="?type=logout" title="Logout"><i class="material-icons right">power_settings_new</i>Logout</a>
            </li>
        </ul>
    </div>
</nav>
</div>
<ul id="slide-out" class="side-nav">
    <li>
        <img class="logo-mobile" src="assets/logo.png">
    </li>
    <li><a class="waves-effect" href="."><i class="material-icons">home</i><font style="font-size: 24px;">Suso-Intern:Admin</font></a></li>
    <li><a class="waves-effect" href="?type=updmgt"><i class="material-icons">input</i>Datenabgleich</a></li>
    <li><a class="waves-effect" href="?type=settings"><i class="material-icons">settings</i>Einstellungen</a></li>
    <li><a class="waves-effect" href="?type=usrmgt"><i class="material-icons">people</i>Verwaltung</a></li>
    <li><a class="waves-effect" href="?type=news"><i class="material-icons">comment</i>Newsletter</a></li>
    <li><a class="waves-effect" href="?type=home"><i class="material-icons">home</i>Home</a></li>
    <div class="divider"></div>
    <li><a class="waves-effect" href="?type=logout"><i class="material-icons">power_settings_new</i>Logout</a></li>

</ul>
