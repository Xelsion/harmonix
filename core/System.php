<?php

namespace core;

use RuntimeException;
use core\abstracts\AController;
use core\classes\Request;
use core\classes\Router;
use core\abstracts\AResponse;

/**
 * The System Class Type singleton
 * This class handles the main procedure from getting a request
 * to return the response output
 */
class System {

	// the instance of this class
	private static ?System $_system = null;

	private Request $_request;

	private Router $_router;

	private AResponse $_response;

	/**
	 * The class constructor
	 * initializes the core\classes\Request
	 * initializes the core\classes\Router
	 */
	private function __construct() {
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
	public function start(): void {
		$route = $this->_router->getRoute($this->_request);
		$controller = $route["controller"];
		$method = $route["method"];
		$args = $param = $route["params"];
		if( $controller instanceof AController ) {
			$this->_response = $controller->$method(...$args);
		} else {
			throw new RuntimeException("Controller for request ".$this->_request->getRequestUri()." cant be found!");
		}
	}

	public function getOutput(): string {
		return $this->_response->getOutput();
	}
}