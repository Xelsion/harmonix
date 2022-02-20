<?php

namespace core;

use RuntimeException;
use core\abstracts\AController;
use core\classes\Request;
use core\classes\Router;
use core\abstracts\AResponse;

class System {

	private static ?System $_system = null;
	private Request $_request;
	private Router $_router;
	private AResponse $_response;

	private function __construct() {
		$this->_request = Request::getInstance();
		$this->_router = Router::getInstance();
	}

	public static function getInstance(): System {
		if( static::$_system === null ) {
			static::$_system = new System();
		}
		return static::$_system;
	}

	public function start(): void {
		$route = $this->_router->getRoute($this->_request);
		$controller = new $route["controller"]();
		if( $controller instanceof AController ) {
			$action = $route["action"];
			$param = $route["params"];
			$this->_response = $controller->$action(...$param);
		} else {
			throw new RuntimeException("Controller for request ".$this->_request->getRequestUri()." cant be found!");
		}
	}

	public function getOutput(): string {
		return $this->_response->getOutput();
	}
}