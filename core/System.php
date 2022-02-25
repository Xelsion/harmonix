<?php

namespace core;

use Exception;
use RuntimeException;
use core\abstracts\AController;
use core\abstracts\AResponse;
use core\classes\Logger;
use core\classes\Request;
use core\classes\Router;


/**
 * The System Class Type singleton
 * This class handles the main procedure from getting a request
 * to return the response output
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class System {

	// the instance of this class
	private static ?System $_system = null;
	private Request $_request;
	private Router $_router;
	private AResponse $_response;
	private Logger $_debug_logger;

	/**
	 * The class constructor
	 * initializes the core\classes\Request
	 * initializes the core\classes\Router
	 */
	private function __construct() {
		$this->_debug_logger = new Logger("debug");
		$this->_request = Request::getInstance();
		$this->_router = Router::getInstance();
	}

	/**
	 * The initializer for this class
	 *
	 * @return System
	 */
	public static function getInstance(): System {
		if( static::$_system === null ) {
			echo 'construct system';
			static::$_system = new System();
		}
		return static::$_system;
	}

	/**
	 * Gets the required controller for the current request
	 * and performs the requested method.
	 * Sets the core\abstracts\AResponse this method will return.
	 *
	 * @throws RuntimeException - if no valid controller and its method was found
	 */
	public function start(): string {
		$route = $this->_router->getRoute($this->_request);
		$controller = $route["controller"];
		$controller->init();
		$method = $route["method"];
		$params = $route["params"];
		if( $controller instanceof AController ) {
			$this->_response = $controller->$method(...$params);
			return $this->_response->getOutput();
		} else {
			throw new RuntimeException("Controller for request ".$this->_request->getRequestUri()." cant be found!");
		}
	}

	public function getDebugLogger(): Logger {
		return $this->_debug_logger;
	}

	public function getRequest(): Request {
		return $this->_request;
	}

	public function getOutput(): string {
		if( $this->_response instanceof AResponse ) {
			return $this->_response->getOutput();
		}
		return "";
	}
}