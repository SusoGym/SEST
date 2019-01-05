<?php
session_start();
if (!isset($_SESSION['user']) )
	die("What are you doing here?");
$header = '<img src="../assets/logo.png" width="90" height="50">';
$title = "Abwesenheitsliste";
$today = "Stand: ".date('d.m.Y H:i');;
$subject = "Abwesenheitsliste";
$pdfAuthor = "Heinrich-Suso-Gymnasium";
$pdfName = "Abwesenheitsliste";


$html = '
<table cellpadding="5" cellspacing="0" style="width: 100%; ">
	<tr>
	   <td>'.nl2br(trim($header)).'</td>
	   <td style="text-align: right;font-size: 20px"><b>'.$title.'</b></td>
	   <td style="text-align: right;font-size: 14px">'.$today.'</td>
	</tr>

	
</table>';
$filename = "absentees.txt";
if (file_exists ( $filename ) ) {
	$fh = fopen($filename,"r");
	while (!feof($fh)) {
	$line = fgets($fh);
	$html .= $line;
	}
} else {
$html .= "Keine Daten!";	
}







//////////////////////////// Erzeugung eures PDF Dokuments \\\\\\\\\\\\\\\\\\\\\\\\\\\\\\\

// TCPDF Library laden
require_once('../../tcpdf/tcpdf.php');

// Erstellung des PDF Dokuments
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Dokumenteninformationen
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($pdfAuthor);
$pdf->SetTitle($title);
$pdf->SetSubject($subject);


// Header und Footer Informationen
$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));


// Auswahl des Font
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

// Auswahl der MArgins
//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->setPrintHeader(false);
// Automatisches Autobreak der Seiten
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Image Scale 
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Schriftart
$pdf->SetFont('helvetica', '', 10);

// Neue Seite
$pdf->AddPage();

// Fügt den HTML Code in das PDF Dokument ein

$pdf->writeHTML($html, true, false, true, false, '');

//Ausgabe der PDF

//Variante 1: PDF direkt an den Benutzer senden:
$pdf->Output($pdfName, 'I');

//Variante 2: PDF im Verzeichnis abspeichern:
//$pdf->Output(dirname(__FILE__).'/'.$pdfName, 'F');
//echo 'PDF herunterladen: <a href="'.$pdfName.'">'.$pdfName.'</a>';

?>
