<?php

include('vendor/autoload.php');

use OpenSpout\Common\Entity\Cell;
use OpenSpout\Common\Entity\Row;

$writer = new \OpenSpout\Writer\XLSX\Writer();

$writer->openToFile($filePath); // write data to a file or to a PHP stream
//$writer->openToBrowser($fileName); // stream data directly to the browser

$cells = [
    Cell::fromValue('Carl'),
    Cell::fromValue('is'),
    Cell::fromValue('great!'),
];

/** add a row at a time */
$singleRow = new Row($cells);
$writer->addRow($singleRow);

/** add multiple rows at a time */
$multipleRows = [
    new Row($cells),
    new Row($cells),
];
$writer->addRows($multipleRows); 

/** Shortcut: add a row from an array of values */
$values = ['Carl', 'is', 'great!'];
$rowFromValues = Row::fromValues($values);
$writer->addRow($rowFromValues);

$writer->close();

/**
 * in case of streaming data directly to the browser with $writer->openToBrowser() ensure
 * to not send any further data after the $writer->close() call as that would be appended
 * to the generated file and that makes Excel complain about it being corrupted.
 * For example, you could place an `exit;` here or terminate the output in any other way.
 */