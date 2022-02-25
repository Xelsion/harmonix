<?php

namespace core;

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
	private static ?System $system = null;

	private Request $request;

	private Router $router;

	private AResponse $response;

	private Logger $logger;
	/**
	 * The class constructor
	 * initializes the core\classes\Request
	 * initializes the core\classes\Router
	 */
	private function __construct() {
		$this->logger = new Logger("runtime.log");
		$this->request = Request::getInstance();
		$this->router = Router::getInstance();
	}

	/**
	 * The initializer for this class
	 *
	 * @return System
	 */
	public static function getInstance(): System {
		if( static::$system === null ) {
			echo 'construct system';
			static::$system = new System();
		}
		return static::$system;
	}

	public function __isset( $prop ) {
		return isset($this->$prop);
	}

	public function __set( string $prop, $value ) {
		$this->$prop = $value;
	}

	public function __get( string $prop ) {
		echo "__get => ".$prop;

		return $this->$prop;
	}

	/**
	 * Gets the required controller for the current request
	 * and performs the requested method.
	 * Sets the core\abstracts\AResponse this method will return.
	 *
	 * @throws RuntimeException - if no valid controller and its method was found
	 */
	public function start(): void {
		$route = $this->router->getRoute($this->request);
		$controller = $route["controller"];
		$controller->init();
		$method = $route["method"];
		$params = $route["params"];
		if( $controller instanceof AController ) {
			$this->response = $controller->$method(...$params);
		} else {
			throw new RuntimeException("Controller for request ".$this->request->getRequestUri()." cant be found!");
		}
	}

	public function getOutput(): string {
		return $this->response->getOutput();
	}
}