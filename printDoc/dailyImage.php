<?php
$date1 = $_REQUEST['date1'];
$workType = $_REQUEST['workType'];
$subDate = explode(' ',$date1);
if(count($subDate) >1)
{
    $date1 = $subDate[0];
}
$output = shell_exec('node C:\node\headlesscore\index.js '.$date1.' '.$workType);
// echo $output;
echo '<img src="'.$output.'"/>';
?>