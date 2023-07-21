<?php
/**
 * The entrypoint of the application
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', 'on');

use lib\App;
use lib\core\blueprints\ALoggableException;
use lib\core\classes\Logger;
use lib\helper\StringHelper;
use lib\middleware\SessionAuth;

define("SUB_DOMAIN", explode(".", $_SERVER["HTTP_HOST"])[0]);
const PATH_ROOT = ".." . DIRECTORY_SEPARATOR;

require_once("../constants.php");
require_once("../functions.php");

// Initiate session cookie settings
ini_set('session.cookie_domain', StringHelper::getDomain());
session_start();

mb_detect_order(["UTF-8", "ISO-8859-1", "ASCII"]);

$runtime_logger = new Logger("runtime");

try {
	// getActor output buffering and prevent all direct output
	ob_start();

	// getActor the process
	$app = new App();
	$app->addMiddleware(SessionAuth::class);
	$app->run();

	// write the process results to the output buffer
	echo $app->getResponseOutput();

	// print the output buffer and empty it
	ob_end_flush();
} catch( Exception $e ) {
	try {
		echo "An error occur: Please check the Log Files for more information";

		// if it's a loggable exception then call its log function
		if( $e instanceof ALoggableException ) {
			$e->log();
		} else { // else we have to use our logger to log the exception
			$runtime_logger->log($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace());
		}
	} catch( Exception $e ) {
		die($e->getMessage());
	}
}
