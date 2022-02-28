<?php
session_start();

use core\classes\Logger;
use core\System;

if( file_exists("../constants_".explode(".", $_SERVER["HTTP_HOST"])[0].".php") ) {
	$constants_file = "../constants_".explode(".", $_SERVER["HTTP_HOST"])[0].".php";
} else {
	$constants_file = "../constants_www.php";
}
require_once( $constants_file );
require_once( "../functions.php" );

$runtime_logger = new Logger("runtime");
try {
	ob_start();
	$system = System::getInstance();
	$system->start();
	echo $system->getOutput();
	ob_end_flush();
} catch( Exception $e ) {
	echo "Error: Please check the Log Files for mor information";
	$runtime_logger->log($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace());
}