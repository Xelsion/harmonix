<?php

namespace lib\core;

use Exception;
use lib\App;
use lib\core\attributes\Route;
use lib\core\blueprints\AController;
use lib\core\exceptions\SystemException;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * The Router Type setAsSingleton
 * Collect all the Controllers and returns the proper controller for the curren request
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Router {

	// the instance of this class
	private static ?Router $instance = null;

	// the collection of the collected controllers
	private array $routes = array();

	/**
	 * The class constructor
	 * will be called once by the static method getInstanceOf()
	 * calls the method initController()
	 *
	 * @throws SystemException|ReflectionException
	 */
	private function __construct() {
		$this->registerController("www", PATH_CONTROLLER_ROOT);
		$this->registerController("admin", PATH_CONTROLLER_ROOT);
	}

	/**
	 * @param string $sub_domain
	 * @param string $directory
	 *
	 * @return void
	 *
	 * @throws ReflectionException
	 * @throws \lib\core\exceptions\SystemException
	 */
	private function registerController(string $sub_domain, string $directory): void {
		$path = $directory . $sub_domain . DIRECTORY_SEPARATOR;
		$files = scandir($path);
		foreach( $files as $file ) {
			if( !is_dir($path . $file) ) {
				$class_file = $path . $file;
				$class = Path2Namespace($class_file);
				$controller = App::getInstanceOf($class);
				if( $controller instanceof AController ) {
					$reflection = new ReflectionClass($controller::class);
					$class_attributes = $reflection->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
					$class_path = "";
					if( !empty($class_attributes) ) {
						foreach( $class_attributes as $attr ) {
							$class_route = $attr->newInstance();
							$class_path = $class_route->path;
						}
					}
					foreach( $reflection->getMethods() as $method ) {
						$method_attributes = $method->getAttributes(Route::class, ReflectionAttribute::IS_INSTANCEOF);
						if( !empty($method_attributes) ) {
							foreach( $method_attributes as $attr ) {
								$route = $attr->newInstance();
								$method_path = $route->path;
								$route->path = $this->getClearedRoutePath($class_path . "/" . $method_path);
								$this->addRoute($sub_domain, $route, $controller::class, $method->getName());
							}
						}
					}
				}
			} else if( $file !== "." && $file !== ".." && is_dir($path . DIRECTORY_SEPARATOR . $file) ) {
				// Let's go recursively
				$this->registerController($sub_domain, $directory . $file . DIRECTORY_SEPARATOR);
			}
		}
	}

	/**
	 * The initializer for this class
	 *
	 * @return Router
	 */
	public static function getInstance(): Router {
		if( static::$instance === null ) {
			static::$instance = new Router();
		}
		return static::$instance;
	}

	/**
	 * Returns an array of all collected routes
	 *
	 * @return array
	 */
	public function getRoutes(): array {
		return $this->routes;
	}

	/**
	 * Sets a route for a specific call.
	 * The call looks like "ControllerName->methodName"
	 *
	 * @param string $sub_domain
	 * @param Route $route
	 * @param string $class
	 * @param string|null $method
	 *
	 * @throws \lib\core\exceptions\SystemException
	 */
	public function addRoute(string $sub_domain, Route $route, string $class, ?string $method = null): void {
		if( !isset($this->routes[$sub_domain][$route->method][$route->path]) ) {
			if( is_null($method) ) {
				$method = "index";
			}
			$regex = str_replace("/", "\/", $route->path);
			$regex = preg_replace("/{id}/", "([0-9]+)", $regex);
			$regex = preg_replace("/{[a-zA-Z|_-]+_id}/", "([0-9]+)", $regex);
			$regex = preg_replace("/{[a-zA-Z|_-]+}/", "([\w]+)", $regex);
			if( $route->method === "ALL" ) {
				$this->routes[$sub_domain]["GET"][$route->path] = array("controller" => $class, 'method' => $method, 'regex' => $regex);
				$this->routes[$sub_domain]["POST"][$route->path] = array("controller" => $class, 'method' => $method, 'regex' => $regex);
			} else {
				$this->routes[$sub_domain][$route->method][$route->path] = array("controller" => $class, 'method' => $method, 'regex' => $regex);
			}
		} else {
			throw new SystemException(__FILE__, __LINE__, "Router: The route [" . $route->path . "] is already taken");
		}
	}

	/**
	 * Checks if the given $route is in our controller collection
	 *
	 * @param string $path
	 * @return bool
	 * @throws SystemException
	 */
	public function hasRoute(string $path): bool {
		$request_method = App::$request->getRequestMethod();
		if( isset($this->routes[SUB_DOMAIN][$request_method][$path]) ) {
			return true;
		}
		$matches = preg_grep("/^" . preg_quote($path, '/') . "/i", array_keys($this->routes[SUB_DOMAIN][$request_method]));
		return count($matches) > 0;
	}

	/**
	 * The getRoute method will check the given $request
	 * for a valid Controller and the requested method
	 * the returned array will be like:
	 * ["controller" => {Controller instance}, "method" => {Methode name}, "params" => {Array of formatted args}]
	 *
	 * @param Request $request
	 * @return array|null
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	public function getRoute(Request $request): ?array {
		return $this->getRouteArray($request->getRequestUri(), $request->getRequestMethod());
	}

	/**
	 * The getRoute method will check the given $url
	 * for a valid Controller and the requested method
	 * the returned array will be like:
	 * ["controller" => {Controller instance}, "method" => {Methode name}, "params" => {Array of formatted args}]
	 *
	 * @param string $url
	 * @return array|null
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	public function getRouteFor(string $url): ?array {
		return $this->getRouteArray($url);
	}

	/**
	 * Returns a sorted array of all registered routes
	 *
	 * @return array
	 */
	public function getSortedRoutes(): array {
		$sorted_routes = array();
		foreach( $this->routes as $domain => $request_methods ) {
			foreach( $request_methods as $request_method => $paths ) {
				foreach( $paths as $path => $settings ) {
					$controller = $settings["controller"];
					$controller_method = $settings["method"];
					if( isset($sorted_routes[$domain][$controller][$controller_method]) ) {
						$sorted_routes[$domain][$controller][$controller_method] = "[" . $request_method . "]" . $sorted_routes[$domain][$controller][$controller_method];
					} else {
						$sorted_routes[$domain][$controller][$controller_method] = "[" . $request_method . "] " . $path;
					}
				}
			}
		}
		return $sorted_routes;
	}

	/**
	 * Parses the request parts to getInstanceOf the controller and the wanted method
	 *
	 * @param string $request
	 * @return array
	 *
	 * @throws ReflectionException
	 * @throws SystemException
	 */
	private function getRouteArray(string $request, string $request_method = "GET"): array {
		if( !isset($this->routes[SUB_DOMAIN][$request_method]) ) {
			return array();
		}

		// first check for static urls
		if( array_key_exists(addcslashes($request, "/"), $this->routes[SUB_DOMAIN][$request_method]) ) {
			$entry = $this->routes[SUB_DOMAIN][$request_method][addcslashes($request, "/")];
			$controller = App::getInstanceOf($entry["controller"]);
			$controller_method = $entry["method"];
			return array("controller" => $controller, "method" => $controller_method, "params" => array());
		}

		// then check for dynamic urls
		foreach( $this->routes[SUB_DOMAIN][$request_method] as $path => $entry ) {
			$matches = array();
			if( preg_match("/^" . $entry["regex"] . "$/i", $request, $matches) ) {
				array_shift($matches);
				$controller = App::getInstanceOf($entry["controller"]);
				$controller_method = $entry["method"];
				$params = $this->getFormattedParameters($controller, $controller_method, $matches);
				return array("controller" => $controller, "method" => $controller_method, "params" => $params);
			}
		}
		return array();
	}

	/**
	 * formats the given path string to a valid route string and returns the cleared string
	 * Removes any unwanted characters like "//" or ending slashes or Add a starting slash if non exists
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	private function getClearedRoutePath(string $path): string {
		$route_path = preg_replace("/\/{2,}/", "/", $path);
		if( !str_starts_with($route_path, "/") ) {
			$route_path = "/" . $route_path;
		}
		if( $route_path !== "/" && str_ends_with($route_path, "/") ) {
			$route_path = substr($route_path, 0, -1);
		}
		return $route_path;
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
	 *
	 * @throws SystemException
	 * @throws ReflectionException
	 */
	private function getFormattedParameters(AController $controller, string $method, array $params): ?array {
		$num_params = count($params);
		$result = array();

		$reflection = new ReflectionMethod($controller, $method);
		$args = $reflection->getParameters();
		$max_args = count($args);
		$min_args = 0;
		for( $i = 0; $i < $max_args; $i++ ) {
			$arg_name = $args[$i]->getName();
			$arg_type = (string)$args[$i]->getType();
			// If the parameter is not optional increase the number of required parameters
			if( !$args[$i]->isOptional() ) {
				$min_args++;
			}
			if( isset($params[$i]) ) {
				// Check if the parameters match the expected type and converts it
				switch( $arg_type ) {
					case "bool":
						$valid_boolean = array("0", "1", "true", "false");
						if( in_array(strtolower($params[$i]), $valid_boolean, true) ) {
							$result[$arg_name] = (bool)$params[$i];
						} else {
							throw new SystemException(__FILE__, __LINE__, "Router: Param type mismatch for method[" . $controller . "->" . $method . "]");
						}
						break;
					case "int":
						if( preg_match("/^\d+$/", $params[$i]) ) {
							$result[$arg_name] = (int)$params[$i];
						} else {
							throw new SystemException(__FILE__, __LINE__, "Router: Param type mismatch for method[" . $controller . "->" . $method . "]");
						}
						break;
					case "string":
						$result[$arg_name] = $params[$i];
						break;
					default:
						try {
							$class_reflection = new ReflectionMethod($arg_type, "__construct");
							$constructor_args = $class_reflection->getParameters();
							$constructor_arg_type = (string)$constructor_args[0]->getType();
							if( $constructor_arg_type === "int" && !is_numeric($params[$i]) ) {
								throw new SystemException(__FILE__, __LINE__, "Router: Param type mismatch for method[" . $controller . "->" . $method . "]");
							}
							if( $constructor_arg_type === "string" && !is_string($params[$i]) ) {
								throw new SystemException(__FILE__, __LINE__, "Router: Param type mismatch for method[" . $controller . "->" . $method . "]");
							}
							$result[$arg_name] = App::getInstanceOf($arg_type, null, ["id" => $params[$i]]);
						} catch( Exception ) {
							throw new SystemException(__FILE__, __LINE__, "Router: Param type mismatch for method[" . $controller . "->" . $method . "]");
						}
				}
			}
		}

		// Check if we have a valid number of parameters.
		// If so return them
		if( $num_params >= $min_args && $num_params <= $max_args ) {
			return $result;
		}
		throw new SystemException(__FILE__, __LINE__, "Router: Param count mismatch for method[" . $controller . "->" . $method . "]");
	}

}
