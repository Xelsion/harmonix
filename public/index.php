<?php

use system\abstracts\ALoggableException;
use system\classes\Logger;
use system\exceptions\SystemException;
use system\Process;

define("SUB_DOMAIN", explode(".", $_SERVER["HTTP_HOST"])[0]);
require_once( "../constants.php" );
require_once( "../lang-de.php" );
require_once( "../functions.php" );

$runtime_logger = new Logger("runtime");
try {
	ob_start();
	$process = Process::getInstance();
    $process->start();
	echo $process->getResult();
	ob_end_flush();
} catch( Exception $e ) {
	try {
		echo "Error: Please check the Log Files for mor information";
        if( $e instanceof ALoggableException ) {
            $e->log();
        } else {
		    $runtime_logger->log($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace());
        }
	} catch( JsonException|SystemException $e ) {
		die($e->getMessage());
	}
}