<?php

namespace core;

use Exception;
use RuntimeException;
use core\abstracts\AController;
use core\abstracts\AResponse;
use core\classes\Configuration;
use core\classes\Logger;
use core\manager\ConnectionManager;
use core\classes\Request;
use core\classes\Router;
use models\Session;
use models\Actor;
use core\classes\tree\Menu;

/**
 * The System Class Type singleton
 * This class handles the main procedure from getting a request
 * to return the response output
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class System {

	// The instance of this class
	private static ?System $_instance = null;

	// The router
	private AResponse $_response;

	/**
	 * The class constructor
	 * initializes the core\classes\Request
	 * initializes the core\classes\Router
	 */
	private function __construct() {
		Core::$_configuration = Configuration::getInstance();
		Core::$_debugger = new Logger("debug");
		Core::$_connection_manager = new ConnectionManager();
		Core::$_request = Request::getInstance();
		Core::$_menu = new Menu();
		Core::$_router = Router::getInstance();
	}

	/**
	 * The initializer for this class
	 *
	 * @return System
	 */
	public static function getInstance(): System {
		if( static::$_instance === null ) {
			static::$_instance = new System();
		}
		return static::$_instance;
	}

	/**
	 * Gets the required controller for the current request
	 * and performs the requested method.
	 * Sets the core\abstracts\AResponse this method will return.
	 *
	 * @throws RuntimeException - if no valid controller and its method was found
	 */
	public function start(): string {
		try {
			// Initiate database connections
			$connections = Core::$_configuration->getSection("connections");
			foreach( $connections as $name => $conn ) {
				Core::$_connection_manager->addConnection($name, $conn["dns"], $conn["user"], $conn["password"]);
			}
			$session = new Session();
			Core::$_actor = $session->start();

			// Try to get the responsible route for this requested uri
			$route = Core::$_router->getRoute(Core::$_request);
			// Get the controller
			$controller = $route["controller"];
			// Is it a compatible controller?
			if( $controller instanceof AController ) {
				// Get the method and its parameters
				$method = $route["method"];
				$params = $route["params"];
				// Get the Response obj from the controller
				$this->_response = $controller->$method(...$params);
				// Return the response output
				return $this->_response->getOutput();
			}
			// No valid controller found
			throw new RuntimeException("Controller for request ".$this->_request->getRequestUri()." cant be found!");
		} catch( Exception $e ) {
			// Pass all exceptions to the index.php
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * Returns the output from the current AResponse object
	 *
	 * @return string
	 */
	public function getOutput(): string {
		if( $this->_response instanceof AResponse ) {
			return $this->_response->getOutput();
		}
		return "";
	}
}