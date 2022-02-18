<?php
session_start();

use core\System;

require_once( "../constants.php" );
require_once( "../functions.php" );

try {
	ob_start();
	$system = System::getInstance();
	$system->start();
	$response = $system->getResponse();
	echo $response;
	ob_end_flush();
} catch( \Exception $e ) {

}