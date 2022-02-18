<?php

namespace core;

use core\abstracts\AController;
use core\classes\Request;
use core\abstracts\AResponse;
use core\manager\ControllerManager;

class System {

	private static ?System $_system = null;
	private ControllerManager $_controllerManager;
	private Request $_request;
	private AResponse $_response;

	private function __construct() {
		$this->_request = Request::getInstance();
		$this->_controllerManager = ControllerManager::getInstance();
	}

	public static function getInstance(): System {
		if( static::$_system === null ) {
			static::$_system = new System();
		}
		return static::$_system;
	}

	public function start(): void {
		$controller = $this->_controllerManager->getController($this->_request);
		if( $controller instanceof AController ) {
			$action = $controller->getAction();
			$param = $controller->getParam();
			$this->_response = $controller->$action(...$param);
		} else {
			throw new \RuntimeException("Controller for request ".$this->_request->getRequestUri()." cant be found!");
		}
	}

	public function getResponse(): string {
		return $this->_response->getOutput();
	}
}