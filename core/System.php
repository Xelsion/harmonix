<?php

namespace core;

use core\classes\Request;
use core\manager\ControllerManager;

class System {

	private static ?System $_system = null;
	private ControllerManager $_controllerManager;
	private Request $_request;

	private function __construct() {
		$this->_request = Request::getInstance();
		$this->_controllerManager = ControllerManager::getInstance();
	}

	public static function start(): System {
		if( static::$_system === null ) {
			static::$_system = new System();
		}
		return static::$_system;
	}

	public function getResponse(): string {
		return "Hallo World";
	}
}