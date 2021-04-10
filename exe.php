#!/usr/bin/env php
<?php
require_once "vendor/autoload.php";

use LatinScript\Parser;

if(!isset($argv[1])){
    throw new \Exception("No file provided");
}

$Parser = new Parser($argv[1]);

$Parser->parse();

//$Parser->parseFile();

?>
