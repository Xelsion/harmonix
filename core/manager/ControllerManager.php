<?php

namespace core\manager;

use core\abstracts\AController;
use core\classes\Request;

class ControllerManager {

	private static ?ControllerManager $_manager = null;
	private array $_controllers = array();

	private function __construct() {
		$this->initController();
	}

	private function initController( string $directory = PATH_CONTROLLER ): void {
		$files = scandir($directory);
		foreach( $files as $key => $value ) {
			if( !is_dir($directory.$value) ) {
				$class_file = $directory.$value;
				if( file_exists($class_file) ) {
					$class = Path2Namespace($class_file);
					$controller = new $class();
					if( $controller instanceof AController ) {
						$route = $controller->getRoute();
						$this->_controllers[$route] = $class;
					}
				}
			} else if( $value !== "." && $value !== ".." && is_dir($directory.DIRECTORY_SEPARATOR.$value) ) {
				$this->initController($directory.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR);
			}
		}
		printDebug($this->_controllers);
	}

	public static function getInstance(): ControllerManager {
		if( static::$_manager === null ) {
			static::$_manager = new ControllerManager();
		}
		return static::$_manager;
	}

	public function getController( Request $request ): ?AController {
		$request_parts = $request->getRequestParts();
		$temp = "";
		$route = "";

		// get the route for the controller
		while( !empty($request_parts) ) {
			$temp .= "/".array_shift($request_parts);
			if( isset($this->_controllers[$temp]) ) {
				$route = $temp;
				continue;
			}
			break;
		}
		$controller_class = $this->_controllers[$route];
		$controller = new $controller_class();

		// get the action for the controller
		$action_name = "indexAction";
		if( count($request_parts) > 0 ) {
			$part = array_shift($request_parts);
			if( $controller->hasActionFunction($part) ) {
				$action_name = $controller->getActionFunction($action_name);
			} else {
				$controller->addParam($part);
			}
		}
		$controller->setAction($action_name);

		// get the parameters for the controller
		while( !empty($request_parts) ) {
			$controller->addParam(array_shift($request_parts));
		}

		return $controller;
	}

	public function __toString() {
		return "core.ControllerManager";
	}
}