<?php
	require_once('qrcode.class.php');
	$err = 'L';
	if(!isset($_GET['text']))
	{
		echo "Error Please input param";exit();
	}
	$text = trim($_GET['text']);
	$qrcode = new QRcode($text, $err);
	$qrcode->disableBorder();
	$qrcode->displayPNG(100);
?>