<?php
session_start();

use core\System;

require_once( "../constants.php" );
require_once( "../functions.php" );

try {
	ob_start();
	$system = System::getInstance();
	$system->start();
	echo $system->getOutput();
	ob_end_flush();
} catch( \Exception $e ) {
	echo $e->getMessage()."<br />";
}