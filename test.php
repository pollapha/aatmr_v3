<?php

$mystring = 'GBL9A-W1';

if(!(strpos($mystring,'-') === false))
{
    echo 'OK';
}
else
{
    echo 'NO';
    
}

/* $pos = strpos($mystring, $findme);
var_dump($pos); */
?>