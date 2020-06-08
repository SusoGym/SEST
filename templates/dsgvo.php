<?php
$user = $this->getDataForView()['user'];
$dsgvoText = "Wenn Sie diese Website benutzen, werden verschiedene <b>personenbezogene Daten</b> erhoben. 
	Personenbezogene Daten sind Daten, mit denen Sie persönlich identifiziert werden können. 
	<br/>Die vorliegende aktualisierte <b>Datenschutzerklärung</b> erläutert, welche Daten wir erheben und wofür wir sie nutzen. 
	Sie erläutert auch, wie und zu welchem Zweck das geschieht.
	Wir weisen darauf hin, dass die Datenübertragung im Internet (z.B. bei der Kommunikation per E-Mail) Sicherheitslücken aufweisen kann. 
	Ein lückenloser Schutz der Daten vor dem Zugriff durch Dritte ist nicht möglich.<br/>
	<br><b>In dieser Anwendung werden persönliche Daten gespeichert und automatisiert verarbeitet.</b>"; 
	
if ($user instanceof Guardian) {
	$dsgvoText .= "<ul>
	<li>Ihr Name wird je nach Anwendungsfunktion anderen Nutzern angezeigt. </li>
	<li>Ihre Emailadresse wird für interne Prozesse verwendet und Sie erhalten je nach Einstellung Emails aus dem System.</li> 
	<li>Name, Geburtstag und Klasse Ihrer Kinder werden gespeichert und innerhalb des Systems verwendet.</li>
	<li>Abwesenheitsdaten Ihrer Kinder werden gespeichert.</li>
	<li>Daten über gemietet Schließfächer (Nr und Ort, sowie Zeitpunkt der Miete) werden gespeichert.</li>
	<li>Die Software greift auf Daten des Ausleihsystems der Schülerbibliothek zurück und ermittelt ausgeliehene Bücher und das fällige Rückgabedatum.</li>
	</ul>
	<b>Keine der verarbeiteten Daten werden jemals außerhalb dieser Anwendung genutzt oder an Dritte weitergegeben.</b>
	<br/>
	Unter Umständen werden vom Server temporäre lokale Dateien auf Ihrem Gerät gespeichert, die zu einer besseren Nutzung
	führen sollen (sogenannte Cookies)." ;
} elseif ($user instanceof Teacher) {
	$dsgvoText .= "<ul>
	<li>Ihr Name wird je nach Anwendungsfunktion anderen Nutzern angezeigt. </li>
	<li>Ihre Emailadresse wird für interne Prozesse verwendet und Sie erhalten je nach Einstellung Emails aus dem System.</li> 
	</ul>
	<b>Keine der verarbeiteten Daten werden jemals außerhalb dieser Anwendung genutzt oder an Dritte weitergegeben.</b>
	<br/>
	Unter Umständen werden vom Server temporäre lokale Dateien auf Ihrem Gerät gespeichert, die zu einer besseren Nutzung
	führen sollen. (sogenannte Cookies)" ;
} elseif ($user instanceof StudentUser) {
	$dsgvoText .= "<ul>
	<li>Ihr Name wird je nach Anwendungsfunktion anderen Nutzern angezeigt. </li>
	<li>Name, Geburtstag und Klasse werden gespeichert und innerhalb des Systems verwendet</li>
	<li>Abwesenheitsdaten werden gespeichert und weiterverarbeitet</li>
	<li>Daten über gemietet Schließfächer (Nr und Ort, sowie Zeitpunkt der Miete) werden gespeichert.</li>
	<li>Die Software greift auf Daten des Ausleihsystems der Schülerbibliothek zurück und ermittelt ausgeliehene Bücher und das fällige Rückgabedatum.</li>
	</ul>
	<b>Keine der verarbeiteten Daten werden jemals außerhalb dieser Anwendung genutzt oder an Dritte weitergegeben.</b>
	<br/>
	Unter Umständen werden vom Server temporäre lokale Dateien auf Ihrem Gerät gespeichert, die zu einer besseren Nutzung
	führen sollen. (sogenannte Cookies)" ;
}

$dsgvoText .= "<br/><br/>Sie haben im Rahmen der geltenden gesetzlichen Bestimmungen jederzeit das Recht auf unentgeltliche Auskunft über Ihre gespeicherten personenbezogenen Daten, 
deren Herkunft und Empfänger und den Zweck der Datenverarbeitung und ggf. ein Recht auf Berichtigung, Sperrung oder Löschung dieser Daten.";
?>
<style>
#dsgvo_accept {
	position: fixed; z-index: 999;left: 1%; width: 98%; 
	bottom: 1%; height: 98%; background-color: rgba(0,80,80,0.8); overflow:auto; display:none;
	padding: 10px;
}
</style>
<div id="dsgvo_accept">
	<span style="font: Arial,Helvetica; font-size: 18px; font-weight: bold; color: #ffffff">Informationen zum Datenschutz</span><br><br>
	<span style="font: Arial,Helvetica; font-size: 14px; color: #ffffff"><?php echo $dsgvoText; ?></span>
	<table width = "20%" align="right">
	<tr>
	<td width="50%"><a class="btn red right" onClick="decline();">Ablehnen und verlassen</a></td>
	<td width="50%"><a class="btn green right" onClick="accept();">Akzeptieren und Meldung schließen</a></td>
	</tr>
	</table>
</div>
<?php 
if (isset($this->getDataForView()['dsgvo'])) {
	$dsgvo = ($this->getDataForView()['dsgvo'] == null) ? 'undefined' : $this->getDataForView()['dsgvo'];
	} else {
	$dsgvo = 'undefined';
	}	?>


	
	
	
	<script type="text/javascript">
	var xhttp = new XMLHttpRequest();
	
	var dsgvo = "<?php echo $dsgvo; ?>";
	if (dsgvo === 'undefined' || dsgvo === 'null') {
		document.getElementById('dsgvo_accept').style.display = 'inline';
	}
	
	
	xhttp.addEventListener('load', function(event) {
	content = "";
	
	if (this.responseText) {
		console.log("unexpected respnse from server!");
		} 
	
	} );
	
	function decline(){
	xhttp.open("POST", "?type=handledsgvo&console&decline", true);
	xhttp.send();
	document.getElementById('dsgvo_accept').style.display = 'none';	
	location.replace("?type=logout");
	}
	
	function accept(){
	xhttp.open("POST", "?type=handledsgvo&console&accept", true);
	xhttp.send();
	document.getElementById('dsgvo_accept').style.display = 'none';	
	}
	</script>