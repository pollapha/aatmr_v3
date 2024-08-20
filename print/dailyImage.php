<?php
$date1 = $_REQUEST['date1'];
$output = shell_exec('node C:\node\headlesscore\index.js '.$date1);
// echo $output;
echo '<img src="'.$output.'"/>';
?>