<?php
session_start();

use core\classes\Logger;
use core\System;

require_once( "../constants.php" );
require_once( "../functions.php" );

$runtime_logger = new Logger("runtime");
try {
	ob_start();
	$system = System::getInstance();
	$system->start();
	echo $system->getOutput();
	ob_end_flush();
} catch( Exception $e ) {
	echo "Fatal Error: Please check the Log Files for mor information";
	$runtime_logger->log($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace());
}