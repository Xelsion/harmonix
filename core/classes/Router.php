<?php

namespace core\classes;

use core\abstracts\AController;

class Router {

	private static ?Router $_router = null;
	public array $_routes = array();

	private function __construct() {
		$this->initController(PATH_CONTROLLER);
	}

	public static function getInstance(): Router {
		if( static::$_router === null ) {
			static::$_router = new Router();
		}
		return static::$_router;
	}

	public function addRoute( string $route, string $call ): void {
		if( !isset($this->_routes[$route]) ) {
			$this->_routes[$route] = $call;
		} else {
			throw new \RuntimeException("Router: The route [".$route."] was already taken");
		}
	}

	public function hasRoute( string $route ): bool {
		if( isset($this->_routes[$route]) ) {
			return true;
		}
		return false;
	}

	public function getRoute( Request $request ): ?array {
		$request_parts = $request->getRequestParts();
		$temp = "";
		$route = "";
		// get the route for the controller
		while( !empty($request_parts) ) {
			$part = array_shift($request_parts);
			$temp .= "/".$part;
			if( $this->hasRoute($temp) ) {
				$route = $temp;
				continue;
			}
			if( empty($request_parts) && $route === "" ) {
				// no more parts and still no controller?
				// let's assume the home controller was requested
				$route = "/";
			}
			// put the last part back into out request parts
			array_unshift($request_parts, $part);
			break;
		}
		if( $this->hasRoute($route) ) {
			$call = $this->_routes[$route];
			if( preg_match("/^(.*)->(.*)/", $call, $matches) ) {
				return array( "controller" => $matches[1], "action" => $matches[2], "params" => $request_parts );
			}
		} else {
			throw new \RuntimeException("Router: There is no route for [".$route."]!");
		}
		return null;
	}

	private function initController( string $directory ): void {
		$files = scandir($directory);
		foreach( $files as $key => $value ) {
			if( !is_dir($directory.$value) ) {
				$class_file = $directory.$value;
				if( file_exists($class_file) ) {
					$class = Path2Namespace($class_file);
					$controller = new $class();
					if( $controller instanceof AController ) {
						$controller->initRoutes($this);
					}
				}
			} else if( $value !== "." && $value !== ".." && is_dir($directory.DIRECTORY_SEPARATOR.$value) ) {
				$this->initController($directory.DIRECTORY_SEPARATOR.$value.DIRECTORY_SEPARATOR);
			}
		}
	}
}