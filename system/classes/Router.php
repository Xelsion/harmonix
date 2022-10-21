<?php

namespace system\classes;

use Exception;
use ReflectionException;
use ReflectionMethod;

use system\abstracts\AController;
use system\exceptions\SystemException;

/**
 * The Router Type singleton
 * Collect all the Controllers and returns the proper controller for the curren request
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Router {

	// the instance of this class
	private static ?Router $_instance = null;
	// the collection of the collected controllers
	private array $_routes = array();

    /**
     * The class constructor
     * will be called once by the static method getInstance()
     * calls the method initController()
     *
     * @throws SystemException
     */
	private function __construct() {
		$this->initController(PATH_CONTROLLER);
	}

	/**
	 * The initializer for this class
	 *
	 * @return Router
	 */
	public static function getInstance(): Router {
		if( static::$_instance === null ) {
			static::$_instance = new Router();
		}
		return static::$_instance;
	}

	/**
	 * Returns an array of all collected routes
	 *
	 * @return array
	 */
	public function getRoutes(): array {
		return $this->_routes;
	}

    /**
     * Sets a route for a specific call.
     * The call looks like "ControllerName->methodName"
     *
     * @param string $route
     * @param string $class
     * @param string|null $method
     *
     * @throws SystemException
     */
	public function addRoute( string $route, string $class, ?string $method = null ): void {
		if( !isset($this->_routes[$route]) ) {
			if( is_null($method) ) {
				$method = "index";
			}
            $regex = str_replace("/" , "\/", $route);
            $regex = preg_replace("/{.*}/", "([\w|0-9]+)", $regex);
			$this->_routes[$regex] = array( "controller" => $class, 'method' => $method, 'regex' => $regex );
		} else {
			throw new SystemException(__FILE__, __LINE__,"Router: The route [".$route."] is already taken");
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
		$matches = preg_grep("/^".preg_quote($route, '/')."/i", array_keys($this->_routes));
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
	public function getRoute( Request $request ): ?array {
        return $this->getRouteArray($request->getRequestUri());
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
    public function getRouteFor( string $url ): ?array {
        return $this->getRouteArray($url);
    }

    /**
     * Parses the request parts to get the controller and the wanted method
     *
     * @param string $request
     * @return array
     *
     * @throws ReflectionException
     * @throws SystemException
     */
    private function getRouteArray( string $request ): array {
        // first check for static urls
        if( array_key_exists( addcslashes($request, "/"), $this->_routes ) ) {
            $entry = $this->_routes[addcslashes($request, "/")];
            $controller = new $entry["controller"]();
            $method = $entry["method"];
            return array( "controller" => $controller, "method" => $method, "params" => array() );
        }

        // then check for dynamic urls
        foreach( $this->_routes as $regex => $entry ) {
            $matches = array();
            if( preg_match("/^".$regex."$/i", $request, $matches) ) {
                array_shift($matches);
                $controller = new $entry["controller"]();
                $method = $entry["method"];
                $params = $this->getFormattedParameters($controller, $method, $matches);
                return array( "controller" => $controller, "method" => $method, "params" => $params );
            }
        }
        return array();
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
	private function getFormattedParameters( AController $controller, string $method, array $params ): ?array {
		$num_params = count($params);
		$result = array();

        $reflection = new ReflectionMethod($controller, $method);
        $args = $reflection->getParameters();
        $max_args = count($args);
        $min_args = 0;
        // Go through all parameters of the requested method
        for( $i = 0; $i < $max_args; $i++ ) {
            $arg_name = $args[$i]->getName();
            $arg_type = (string)$args[$i]->getType();
            $arg_optional = $args[$i]->isOptional();
            // If the parameter is not optional increase the number of required parameters
            if( !$arg_optional ) {
                $min_args++;
            }
            if( isset($params[$i]) ) {
                // Check if the parameters match the expected type and converts it
                switch( $arg_type ) {
                    case "bool":
                        $valid_boolean = array( "0", "1", "true", "false" );
                        if( in_array(strtolower($params[$i]), $valid_boolean, true) ) {
                            $result[$arg_name] = (bool)$params[$i];
                        } else {
                            throw new SystemException( __FILE__,__LINE__,"Router: Param type mismatch for method[".$controller."->".$method."]");
                        }
                        break;
                    case "int":
                        if( preg_match("/^\d+$/", $params[$i]) ) {
                            $result[$arg_name] = (int)$params[$i];
                        } else {
                            throw new SystemException( __FILE__,__LINE__,"Router: Param type mismatch for method[".$controller."->".$method."]");
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
                                throw new SystemException( __FILE__,__LINE__,"Router: Param type mismatch for method[".$controller."->".$method."]");
                            }
                            if( $constructor_arg_type==="string" && !is_string($params[$i]) ) {
                                throw new SystemException( __FILE__,__LINE__,"Router: Param type mismatch for method[".$controller."->".$method."]");
                            }
                            $result[$arg_name] = new $arg_type($params[$i]);
                        } catch( Exception $e ) {
                            throw new SystemException( __FILE__,__LINE__,"Router: Param type mismatch for method[".$controller."->".$method."]");
                        }
                }
            }
        }


        // Check if we have a valid number of parameters.
        // If so return them
        if( $num_params >= $min_args && $num_params <= $max_args ) {
            return $result;
        }
        throw new SystemException( __FILE__,__LINE__,"Router: Param count mismatch for method[".$controller."->".$method."]");
	}

    /**
     * Walks through the given $directory and collects all
     * Classes that are an instance of system\abstracts\AController
     *
     * @param string $directory
     *
     * @throws SystemException
     */
	private function initController( string $directory ): void {
		$files = scandir($directory);
		// Go through all files/directories in this directory
		foreach( $files as $file ) {
			// do we have a file?
			if( !is_dir($directory.$file) ) {
				$class_file = $directory.$file;
				// Get the namespace of this path
				$class = Path2Namespace($class_file);
				// get an instance of this class
				$controller = new $class();
				if( $controller instanceof AController ) {
					// It's a valid Controller so initialize its routes
					$controller->init($this);
				}
			} else if( $file !== "." && $file !== ".." && is_dir($directory.DIRECTORY_SEPARATOR.$file) ) {
				// Let's go through this subdirectory
				$this->initController($directory.$file.DIRECTORY_SEPARATOR);
			}
		}
	}

	/**
	 * Collect all Controllers from all "subdomains" and returns them
	 * in an array
	 *
	 * @param string $directory
	 * @param array $results
	 */
	public function getAllRoutes( string $directory, array &$results ): void {
		$files = scandir($directory);
		// Go through all files/directories in this directory
		foreach( $files as $file ) {
			// do we have a file?
			if( !is_dir($directory.$file) ) {
				$class_file = $directory.$file;

                // Get the namespace of this path
				$class = Path2Namespace($class_file);

                // get the controller path
                $pattern = "/^controller\\".DIRECTORY_SEPARATOR."(.*)\\".DIRECTORY_SEPARATOR."/";
                preg_match($pattern, $class, $matches);
                $domain = $matches[1];

				// get an instance of this class
				$controller = new $class();
				if( $controller instanceof AController ) {
                    $routes = $controller->getRoutes();
                    foreach( $routes as $url => $route ) {
                        $results[$domain][$route["controller"]][$route["method"]] = $url;
                    }
				}
			} else if( $file !== "." && $file !== ".." && is_dir($directory.DIRECTORY_SEPARATOR.$file) ) {
				// Let's go through this subdirectory
				$this->getAllRoutes($directory.$file.DIRECTORY_SEPARATOR, $results);
			}
		}
	}
}
