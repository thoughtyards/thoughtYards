<?php
/**
 * Thoughtyards command line script file.
 *
 * This script is meant to be run on command line to execute
 * one of the pre-defined console commands.
 *
 * @author Vipul Dadhich <vipul.dadhich@gmail.com>
 * @link http://www.thoughtyards.info/, www.thoughtyards.com
 * @copyright Vipul Dadhich
 * @license GNU
 */

ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__.'/vendor/autoload.php';
require dirname(__FILE__).'/web-boot/Gateway.php';

define('TY_DS','\\');

$bootrecord='load';
//YAML boot strap configurations
Gateway::boot($bootrecord);
$config=dirname(__FILE__).'/web-boot/config/AppTerminalConsoleConfig.php';

defined('STDIN') or define('STDIN', fopen('php://stdin', 'r'));

if(isset($config))
{
	$app=Gateway::CosoleKitInit($config);	
}
else
	$app=Gateway::CosoleKitInit(array('basePath'=>dirname(__FILE__).'/cli'));

$app->run();