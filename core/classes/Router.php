<?php

namespace core\classes;

use core\abstracts\AController;
use Exception;
use RuntimeException;
use ReflectionMethod;

/**
 * The Router Type singleton
 * Collect all the Controllers and returns the proper controller for the curren request
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Router {

	// the instance of this class
	private static ?Router $_router = null;
	// the collection of the collected controllers
	private array $_routes = array();

	/**
	 * The class constructor
	 * will be called once by the static method getInstance()
	 * calls the method initController()
	 */
	private function __construct() {
		$this->initController(PATH_CONTROLLER);
	}

	/**
	 * The initializer for this class
	 * @return Router
	 */
	public static function getInstance(): Router {
		if( static::$_router === null ) {
			static::$_router = new Router();
		}
		return static::$_router;
	}

	/**
	 * Sets a route for a specific call.
	 * The call looks like "ControllerName->methodName"
	 *
	 * @param string $route
	 * @param string $call
	 * @throws RuntimeException
	 */
	public function addRoute( string $route, string $call ): void {
		if( !isset($this->_routes[$route]) ) {
			$this->_routes[$route] = $call;
		} else {
			throw new RuntimeException("Router: The route [".$route."] was already taken");
		}
	}

	/**
	 * Checks if the given $route is in our controller collection
	 *
	 * @param string $route
	 * @return bool
	 */
	public function hasRoute( string $route ): bool {
		if( isset($this->_routes[$route]) ) {
			return true;
		}
		return false;
	}

	/**
	 * The getRoute method will check the given $request
	 * for a valid Controller and the requested method
	 * the returned array will be like:
	 * ["controller" => {Controller instance}, "method" => {Methode name}, "params" => {Array of formatted args}]
	 *
	 * @param Request $request
	 * @return array|null
	 * @throws RuntimeException
	 */
	public function getRoute( Request $request ): ?array {
		$request_parts = $request->getRequestParts();

		$route = $this->getValidRoute($request_parts);
		if( $this->hasRoute($route) ) {
			$call = $this->_routes[$route];
			if( preg_match("/^(.*)->(.*)/", $call, $matches) ) {
				$controller = new $matches[1]();
				$method = $matches[2];
				try {
					$params = $this->getValidParameters($controller, $method, $request_parts);
					return array( "controller" => $controller, "method" => $method, "params" => $params );
				} catch( RuntimeException $e ) {
					throw new RuntimeException($e->getMessage());
				}
			}
		} else {
			throw new RuntimeException("Router: There is no route for [".$route."]!");
		}
		return null;
	}

	/**
	 * Parses the $request array and returns the route to the
	 * matching controller
	 *
	 * @param array $request_parts
	 * @return string
	 */
	private function getValidRoute( array &$request_parts ): string {
		$temp = "";
		$route = "";

		if( empty($request_parts) ) {
			// the request is empty so return the default Controller route
			return "/";
		}

		// get the route for the controller
		while( !empty($request_parts) ) {
			$part = array_shift($request_parts);
			$temp .= "/".$part;
			if( $this->hasRoute($temp) ) {
				// we find a route lets continue
				$route = $temp;
				continue;
			}

			// last part didn't match, but it could be an argument
			$result = preg_grep("/^".preg_quote($route, "/")."\/{.*}$/", array_keys($this->_routes));
			if( count($result) === 1 ) {
				$route = array_pop($result);
			}
			// last part wasn't a route parameter sp put it back into our request parts
			array_unshift($request_parts, $part);
			break;
		}
		return $route;
	}

	/**
	 * Checks if the needed parameters of the controller method
	 * matches the given parameters in the request.
	 * Also checks the type of the parameters and tries to convert
	 * the request parameter into the required method parameter type
	 * Returns an array with formatted values
	 *
	 * @param AController $controller
	 * @param string $method
	 * @param array $params
	 * @return array|null
	 * @throws RuntimeException
	 */
	private function getValidParameters( AController $controller, string $method, array $params ): ?array {
		$num_params = count($params);
		$result = array();
		try {
			$reflection = new ReflectionMethod($controller, $method);
			$args = $reflection->getParameters();
			$max_args = count($args);
			$min_args = 0;
			for( $i = 0; $i < $max_args; $i++ ) {
				$arg_name = $args[$i]->getName();
				$arg_type = (string)$args[$i]->getType();
				$arg_optional = $args[$i]->isOptional();
				if( !$arg_optional ) {
					$min_args++;
				}
				if( isset($params[$i]) ) {
					print_debug($arg_type);
					switch( $arg_type ) {
						case "bool":
							if( $params[$i] === "0" || $params[$i] === "1" ) {
								$result[$arg_name] = (bool)$params[$i];
							} elseif( strtolower($params[$i]) === "true" || strtolower($params[$i]) === "false" ) {
								$result[$arg_name] = (bool)$params[$i];
							} else {
								throw new RuntimeException("Router: Param type mismatch for method[".$controller."->".$method."]");
							}
							break;
						case "int":
							if( is_numeric($params[$i]) ) {
								$result[$arg_name] = (int)$params[$i];
							} else {
								throw new RuntimeException("Router: Param type mismatch for method[".$controller."->".$method."]");
							}
							break;
						case "string":
							$result[$arg_name] = $params[$i];
							break;
						default:
							try {
								$result[$arg_name] = new $arg_type($params[$i]);
							} catch( Exception $e ) {
								throw new RuntimeException("Router: Param type mismatch for method[".$controller."->".$method."]");
							}
					}
				}
			}
			print_debug($result);
			if( $num_params >= $min_args && $num_params <= $max_args ) {
				return $result;
			}
			throw new RuntimeException("Router: Param count mismatch for method[".$controller."->".$method."]");
		} catch( \ReflectionException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * Walks through the given $directory and collects all
	 * Classes that are an instance of core\abstracts\AController
	 *
	 * @param string $directory
	 */
	private function initController( string $directory ): void {
		$files = scandir($directory);
		foreach( $files as $file ) {
			if( !is_dir($directory.$file) ) {
				$class_file = $directory.$file;
				if( file_exists($class_file) ) {
					$class = Path2Namespace($class_file);
					$controller = new $class();
					if( $controller instanceof AController ) {
						$controller->initRoutes($this);
					}
				}
			} else if( $file !== "." && $file !== ".." && is_dir($directory.DIRECTORY_SEPARATOR.$file) ) {
				$this->initController($directory.DIRECTORY_SEPARATOR.$file.DIRECTORY_SEPARATOR);
			}
		}
	}
}