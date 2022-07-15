<?php
/**
 * The entrypoint of the application
  *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
use system\abstracts\ALoggableException;
use system\classes\Logger;
use system\Process;

define("SUB_DOMAIN", explode(".", $_SERVER["HTTP_HOST"])[0]);
require_once( "../constants.php" );
require_once( "../lang-de.php" );
require_once( "../functions.php" );

$runtime_logger = new Logger("runtime");
try {
    // start output buffering and prevent all direct output
	ob_start();

    // start the process
	$process = Process::getInstance();
    $process->start();

    // write the process results to the output buffer
	echo $process->getResult();

    // print the output buffer and empty it
	ob_end_flush();
} catch( Exception $e ) {
	try {
		echo "Error: Please check the Log Files for mor information";
        if( $e instanceof ALoggableException ) {
            $e->log();
        } else {
		    $runtime_logger->log($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace());
        }
	} catch( Exception $e ) {
		die($e->getMessage());
	}
}
