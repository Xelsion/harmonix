<?php

namespace core\manager;

use core\abstracts\AController;

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
					if( $class instanceof AController ) {
						$route = $controller->getRoute();
						$this->_controllers[$route] = $class;
					}
				}
			} else if( $value !== "." && $value !== ".." && is_dir($directory.DIRECTORY_SEPARATOR.$value) ) {
				$this->initController($directory.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR);
			}
		}
	}

	public static function getInstance(): ControllerManager {
		if( static::$_manager === null ) {
			static::$_manager = new ControllerManager();
		}
		return static::$_manager;
	}

	public function getController( string $route ): ?AController {
		return $this->_controllers[$route] ?? null;
	}


	public function __toString() {
		return "core.ControllerManager";
	}
}