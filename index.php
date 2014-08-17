<?php
include_once "serial.php";
include_once "roomba.php";

function cOut($data)
{
    if (!empty($data)) {
        echo $data;
        echo PHP_EOL;
    }
}

cOut('Begin');

$roomba = new roomba();
$roomba->run();

cOut('End');