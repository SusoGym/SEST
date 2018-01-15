<?php 
$url = (!isset($url) ) ?  "#" : "https://www.".$url; 


?>
<html>
<head>
</head>
<body>
<div style="font-family: sans-serif; text-align: left;">
    <p>Bitte folgen Sie dem Link <a href="<?php echo $url; ?>"><?php echo $url; ?></a>, um Ihr Passwort zurückzusetzen. Er verliert seine
       Gültigkeit in 24 Stunden, sollten Sie bis dahin Ihr Passwort nicht zurückgesetzt haben, müssen Sie einen neuen
       Link anfordern.</p>
    <p><br/>Sollte obiger Link nicht funktionieren, kopieren Sie bitte folgende Zeile in die Adresszeile Ihres Browsers, um das Passwort zurückzusetzen:<br/><br/>
  	<?php echo $url; ?><br/></p>
    <p>Mit freundlichen Grüßen,<br>Ihr Suso-Intern-Team</p>
</div>
</body>
</html>
