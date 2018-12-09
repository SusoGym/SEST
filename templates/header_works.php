<!DOCTYPE html>
<?php $dsgvo = ($this->getDataForView()['dsgvo'] == null) ? 'undefined' : $this->getDataForView()['dsgvo']; ?>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    <meta name="google-site-verification" content="afR-m_0mxdzKpJL4S5AM5JnImHvvDpxGw5WxU6S1zDk"/>
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
		
		#dsgvo_accept {
			position: fixed; z-index: 999;left: 10%; width: 80%; 
			bottom: 20%; height: 60%; background-color: rgba(0,80,80,0.8); display:none;
			padding: 10px;
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
	
	
	<?php include("dsgvo.php"); ?>
	<div id="dsgvo_accept">
	<span style="font: Arial,Helvetica; font-size: 18px; font-weight: bold; color: #ffffff">Informationen zum Datenschutz</span><br><br>
	<span style="font: Arial,Helvetica; font-size: 14px; color: #ffffff">In dieser Anwendung werden persönliche Daten automatisiert verarbeitet. Ihr Name wird je nach Anwendungsfunktion anderen Nutzern angezeigt. 
	Ihre Emailadresse wird verwendet, um vergessene Passwörter zurückzusetzen oder weiter Hilfsinformationen an die hinterlegte Emailadresse zu senden. Ihre Daten werden in keinem Falle außerhalb dieser Anwendung genutzt oder an Dritte weitergegeben.</span>
	<table width = "20%" align="right">
	<tr>
	<td width="50%"><a class="btn red right" onClick="decline();">Ablehnen und verlassen</a></td>
	<td width="50%"><a class="btn green right" onClick="accept();">Akzeptieren und Meldung schließen</a></td>
	</tr>
	</table>
	</div>
	
	
	
	<script type="text/javascript">
	var xhttp = new XMLHttpRequest();
	
	var dsgvo = <?php echo $dsgvo; ?>;
	if (typeof dsgvo === 'undefined' || dsgvo === 'null') {
		document.getElementById('dsgvo_accept').style.display = 'inline';
	}
	
	
	xhttp.addEventListener('load', function(event) {
	content = "";
	
	if (this.responseText) {
		console.log(this.responseText);
		data = $.parseJSON(this.responseText);
		
		if (data['status'] === "declined"){
				location.replace("?type=logout");
			} else if (data['status'] === "accepted") {
			document.getElementById('dsgvo_accept').style.display = 'none';	
			}
		} 
	
	} );
	
	function decline(){
	xhttp.open("POST", "?type=handledsgvo&console&decline", true);
	xhttp.send();
	}
	
	function accept(){
	xhttp.open("POST", "?type=handledsgvo&console&accept", true);
	xhttp.send();
	}
	</script>