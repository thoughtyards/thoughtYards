<?php
ini_set('display_errors',0);
error_reporting(E_ALL);

define('TY_DS','\\');

require_once __DIR__.'/vendor/autoload.php';
require dirname(__FILE__).'/web-boot/Gateway.php';

//@TODO Define directory separator and add in the TYCONFIG.php; 
$bootrecord='load';
//YAML configurations
Gateway::boot($bootrecord);
$config=dirname(__FILE__).'/web-boot/config/AppTerminalWebConfig.php';
Gateway::WebKitInit($config)->run();
