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
	// The application configuration
	private Configuration $_configuration;
	// The database connection Manager
	private ConnectionManager $_connection_manager;
	// The request obj
	private Request $_request;
	// The router
	private Router $_router;
	// The response
	private AResponse $_response;
	// The debug logger
	private Logger $_debug_logger;

	private Actor $_actor;

	/**
	 * The class constructor
	 * initializes the core\classes\Request
	 * initializes the core\classes\Router
	 */
	private function __construct() {
		$this->_configuration = Configuration::getInstance();
		$this->_debug_logger = new Logger("debug");
		$this->_connection_manager = new ConnectionManager();

		$this->_request = Request::getInstance();
		$this->_router = Router::getInstance();
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
			$connections = $this->_configuration->getSection("connections");
			foreach( $connections as $name => $conn ) {
				$this->_connection_manager->addConnection($name, $conn["dns"], $conn["user"], $conn["password"]);
			}

			$session = new Session();
			$this->_actor = $session->start();

			// Try to get the responsible route for this requested uri
			$route = $this->_router->getRoute($this->_request);
			// Get the controller
			$controller = $route["controller"];
			// Is it a compatible controller?
			if( $controller instanceof AController ) {
				$controller->init();
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
	 * @return Actor
	 */
	public function getActor(): Actor {
		return $this->_actor;
	}

	/**
	 * Returns a standard Logger for debugging
	 *
	 * @return Logger
	 */
	public function getDebugLogger(): Logger {
		return $this->_debug_logger;
	}

	/**
	 * Returns the connection manager
	 *
	 * @return ConnectionManager
	 */
	public function getConnectionManager(): ConnectionManager {
		return $this->_connection_manager;
	}

	/**
	 * Returns the current request object
	 *
	 * @return Request
	 */
	public function getRequest(): Request {
		return $this->_request;
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