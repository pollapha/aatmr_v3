<?php
include('../vendor/autoload.php');
$mpdf = new mPDF('th','A4-L','','','5','5','21','5');
$mpdf->autoScriptToLang = true;
$aatlogo = 'images/aatlogopng.png';
$ttvlogo = 'images/ttv-logo.gif';
$mpdf->SetHTMLHeader(
	'<table style="border-bottom:1px solid black;" border:1px; cellspacing="0">
		<tr>
			<td width="810"><b>TITAN-VNS AUTO LOGISTICS CO., LTD.</b><br>49/52 Moo 5 Thung Sukhla, Sriracha, Chonburi 20230</td>
			<td><img src="'.$aatlogo.'" width="130" height="40"/></td><td><img src="'.$ttvlogo.'" width="130"/></td>
		</tr>
	</table>
	');
$mpdf->setFooter('{PAGENO}/{nbpg} ');

$footerTable = 
'
<html><head>
<style>
table {
	font-family: sans-serif;
	border: 7mm solid aqua;
	border-collapse: collapse;
}
table.table2 {
	border: 2mm solid aqua;
	border-collapse: collapse;
}
table.layout {
	border: 0mm solid black;
	border-collapse: collapse;
}
td.layout {
	text-align: center;
	border: 0mm solid black;
}
td {
	padding: 3mm;
	border: 2mm solid blue;
	vertical-align: middle;
}
td.redcell {
	border: 3mm solid red;
}
td.redcell2 {
	border: 2mm solid red;
}
</style>
</head>
<body>
<h1>mPDF</h1>
<h2>Tables - Borders</h2>
<h4>mPDF</h4>
Border conflict resolution in tables with border-collapse set to "collapse". mPDF follows the rules set by CSS as well as possible, but as you can see, there is some difference in interpretation of the rules:
<table class="layout">
<tr>
    <td class="layout">mPDF</td>
    <td class="layout">Internet Explorer<br />IE 9</td>
    <td class="layout">Firefox<br />v 32.0.3</td>
</tr>
<tr>
	<td class="layout">
<table>
<tr>
    <td style="border:5mm solid green">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td rowspan="2" class="redcell" style="border:5mm solid teal">1</td>
    <td style="border:3mm solid pink">1</td>
    <td style="border:5mm solid purple">1</td>
</tr>
<tr>
    <td style="border:2mm solid gray">1</td>
    <td>1</td>
</tr>
<tr>
    <td class="redcell">1</td>
    <td>1</td>
    <td>1</td>
</tr>
</table>
	</td>
    <td class="layout" rowspan="3"><img src="bordersIE.jpg" /></td>
    <td class="layout" rowspan="3"><img src="bordersFF.jpg" /></td>
</tr>
<tr>
	<td class="layout" style="text-align: left">
<table style="border: 2.5mm solid aqua">
<tr>
    <td class="redcell">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td rowspan="2" class="redcell" style="border:5mm solid green">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td class="redcell">1</td>
    <td>1</td>
    <td>1</td>
</tr>
</table>
	</td>
</tr>
<tr>
	<td class="layout">
<table>
<tr>
    <td class="redcell">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td rowspan="2" >1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td style="border:5mm solid yellow">1</td>
    <td>1</td>
</tr>
<tr>
    <td class="redcell">1</td>
    <td>1</td>
    <td>1</td>
</tr>
</table>
	</td>
</tr>
</table>
<pagebreak />
<table class="layout">
<tr>
    <td class="layout">mPDF</td>
    <td class="layout">Internet Explorer<br />IE 9</td>
    <td class="layout">Firefox<br />v 32.0.3</td>
</tr>
<tr>
	<td class="layout">
<table class="table2">
<tr>
    <td style="border:2mm solid green">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td rowspan="2" class="redcell2" style="border:2mm solid teal">1</td>
    <td style="border:2mm solid pink">1</td>
    <td style="border:2mm solid purple">1</td>
</tr>
<tr>
    <td style="border:2mm solid gray">1</td>
    <td>1</td>
</tr>
<tr>
    <td class="redcell2">1</td>
    <td>1</td>
    <td>1</td>
</tr>
</table>
	</td>
    <td class="layout" rowspan="3"><img src="borders2IE.jpg" /></td>
    <td class="layout" rowspan="3"><img src="borders2FF.jpg" /></td>
</tr>
<tr>
	<td class="layout" style="text-align: left">
<table style="border: 2mm solid aqua" class="table2">
<tr>
    <td class="redcell2">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td rowspan="2" class="redcell2" style="border:2mm solid green">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td class="redcell2">1</td>
    <td>1</td>
    <td>1</td>
</tr>
</table>
	</td>
</tr>
<tr>
	<td class="layout">
<table class="table2">
<tr>
    <td class="redcell2">1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td rowspan="2" >1</td>
    <td>1</td>
    <td>1</td>
</tr>
<tr>
    <td style="border:2mm solid yellow">1</td>
    <td>1</td>
</tr>
<tr>
    <td class="redcell2">1</td>
    <td>1</td>
    <td>1</td>
</tr>
</table>
	</td>
</tr>
</table>
<pagebreak />
<h4>mPDF</h4>
<table style="border: 10px solid orange">
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px double red">double red</td>
<td style="border: 10px dashed yellow">dashed yellow</td>
<td style="border: 10px dotted green">dotted green</td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px hidden orange">hidden </td>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px none orange">none</td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px ridge blue">ridge blue</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px groove pink">groove pink</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px none orange">none </td>
<td style="border: 10px inset gray">inset gray</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px outset purple">outset purple</td>
<td style="border: 10px none orange">none </td>
</tr>
</table>
<h4>Firefox 32</h4>
<img src="borders3FF.jpg" />
<br />
<h4>IE 9</h4>
<img src="borders3IE.jpg" />
<pagebreak />
<div>mPDF</div>
<table style="border: 10px solid orange; border-collapse: separate;">
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px double red">double red</td>
<td style="border: 10px dashed yellow">dashed yellow</td>
<td style="border: 10px dotted green">dotted green</td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px hidden orange">hidden </td>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px none orange">none</td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px ridge blue">ridge blue</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px solid orange">Data</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px groove pink">groove pink</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px solid orange">Data</td>
</tr>
<tr>
<td style="border: 10px none orange">none </td>
<td style="border: 10px inset gray">inset gray</td>
<td style="border: 10px none orange">none </td>
<td style="border: 10px outset purple">outset purple</td>
<td style="border: 10px none orange">none </td>
</tr>
</table>
<div>Firefox 32</div>
<img style="margin:0;" src="borders4FF.jpg" />
<div>IE 9</div>
<img style="margin:0;" src="borders4IE.jpg" />
<pagebreak />
<table style="border: 5px inset teal">
<tr>
<td style="border: 5px solid orange">solid orange</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px double red">double red</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px inset gray">inset gray</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px outset purple">outset purple</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px groove pink">groove pink</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px ridge blue">ridge blue</td>
</tr>
</table>
<table style="border: 5px inset gray; border-collapse: separate;">
<tr>
<td style="border: 5px solid orange">solid orange</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px double red">double red</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px inset gray">inset gray</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px outset purple">outset purple</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px groove pink">groove pink</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px ridge blue">ridge blue</td>
</tr>
</table>
<table style="border: 5px outset purple; border-collapse: separate;">
<tr>
<td style="border: 5px solid orange">solid orange</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px double red">double red</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px inset gray">inset gray</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px outset purple">outset purple</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px groove pink">groove pink</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px ridge blue">ridge blue</td>
</tr>
</table>
<table style="border: 5px groove pink; border-collapse: separate;">
<tr>
<td style="border: 5px solid orange">solid orange</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px double red">double red</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px inset gray">inset gray</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px outset purple">outset purple</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px groove pink">groove pink</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px ridge blue">ridge blue</td>
</tr>
</table>
<table style="border: 5px ridge blue; border-collapse: separate;">
<tr>
<td style="border: 5px solid orange">solid orange</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px double red">double red</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px inset gray">inset gray</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px outset purple">outset purple</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px groove pink">groove pink</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px ridge blue">ridge blue</td>
</tr>
</table>
<table style="border: 5px double red; border-collapse: separate;">
<tr>
<td style="border: 5px solid orange">solid orange</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px double red">double red</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px inset gray">inset gray</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px outset purple">outset purple</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px groove pink">groove pink</td>
<td style="border: 0px none black">none</td>
<td style="border: 5px ridge blue">ridge blue</td>
</tr>
</table>
</body>
</html>
';

$mpdf->AddPage('L');
$mpdf->WriteHTML($footerTable);
$mpdf->Output();
?>