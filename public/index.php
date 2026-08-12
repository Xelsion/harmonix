<?php
/**
 * The entrypoint of the application
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
declare(strict_types=1);

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use lib\App;
use lib\core\abstracts\ALoggableException;
use lib\core\classes\Configuration;
use lib\core\classes\Logger;
use lib\core\classes\Template;
use lib\core\classes\TemplateData;
use lib\helper\StringHelper;
use lib\middleware\SessionAuth;

const PATH_ROOT = ".." . DIRECTORY_SEPARATOR;
require_once("../functions.php");

define("SUB_DOMAIN", get_subdomain_from_host($_SERVER["HTTP_HOST"]));
require_once("../constants.php");


// Initiate session cookie settings
ini_set('session.cookie_domain', StringHelper::getDomain());
session_start();

mb_detect_order(["UTF-8", "ISO-8859-1", "ASCII"]);

$runtime_logger = new Logger("runtime");
$config = new Configuration(PATH_ROOT . "application.ini");

try {
	// start output buffering and prevent all direct output
	ob_start();

	// start the process
	$app = new App();
	$app->addMiddleware(SessionAuth::class);
	$app->run();

	// write the process results to the output buffer
	echo $app->getResponseOutput();

	// print the output buffer and empty it
	ob_end_flush();
} catch( Exception $e ) {
	try {
		$environment = $config->getSectionValue("system", "environment");
		if( $config->getSectionValue($environment, "show_errors") ) {
			$view = new Template(PATH_VIEWS_ROOT . "exception.html");
			TemplateData::set("error", $e);
			echo $view->parse();
		} else {
			echo "An error occur: Please check the Log Files for more information";
		}

		// if it's a loggable exception then call its log function
		if( $e instanceof ALoggableException ) {
			$e->log();
		} else { // else we have to use our logger to log the exception
			$runtime_logger->log($e->getFile(), $e->getLine(), $e->getMessage(), $e->getTrace());
		}
	} catch( Exception $e ) {
		die($e->getMessage());
	}
} finally {
	exit(0);
}

