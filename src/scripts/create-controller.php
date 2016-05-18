#!/usr/bin/php
<?php
include_once __DIR__.DIRECTORY_SEPARATOR."cli-script-helper-functions.php";

if( php_sapi_name() !== 'cli' ) {

    exit('Error: This script should only be run via the command line!!');
}

try {
    createController($argc, $argv);

} catch(\Exception $e) {
    
    $msg = 'Exception was thrown in ' . $e->getFile() . ' on line ' . $e->getLine()
           . PHP_EOL . $e->getMessage()
           . PHP_EOL . 'Exception Trace:' . PHP_EOL . $e->getTraceAsString();
    echo $msg;
}
