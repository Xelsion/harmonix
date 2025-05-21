<?php

namespace lib;

use Exception;
use lib\core\blueprints\AController;
use lib\core\blueprints\AMiddleware;
use lib\core\blueprints\AResponse;
use lib\core\cache\ObjectCache;
use lib\core\classes\Analyser;
use lib\core\classes\Auth;
use lib\core\classes\Configuration;
use lib\core\classes\GarbageCollector;
use lib\core\classes\KeyValuePairs;
use lib\core\classes\Language;
use lib\core\classes\Logger;
use lib\core\classes\TemplateData;
use lib\core\ClassManager;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use lib\core\Request;
use lib\core\Router;
use lib\core\tree\RoleTree;
use models\ActorModel;
use models\ActorRoleModel;

/**
 * The App class of type setAsSingleton
 * This class handles the main procedure from getting a request
 * to return the response_types output
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class App {

	private static ObjectCache $ob_cache;

	public static Configuration $config;

	// The class manager with dependency injection
	private static ClassManager $class_manager;

	// The global debug logger
	public static Logger $debugger;

	// The current request
	public static Request $request;

	// The current response_types
	public static ?AResponse $response = null;

	// The current actor
	public static ActorModel $curr_actor;

	// The current actor role
	public static ActorRoleModel $curr_actor_role;

	// The Auth depending on the current actor role and the current request restrictions
	public static Auth $auth;

	// The analyser contains entries witch time measured aspects of the framework
	public static Analyser $analyser;

	// The global accessible key=>value storage for the application
	public static KeyValuePairs $storage;

	// An array of instances running as middleware
	private array $middleware = array();

	/**
	 * The class constructor.
	 * Initiates all important core objects
	 *
	 * @throws SystemException
	 */
	public function __construct() {

		self::$ob_cache = new ObjectCache();
		self::$class_manager = new ClassManager();
		self::$debugger = new Logger("debug");
		self::$config = new Configuration(PATH_ROOT . "application.ini");
		self::$analyser = self::getInstanceOf(Analyser::class);
		self::$storage = self::getInstanceOf(KeyValuePairs::class);

		self::setAsSingleton(Configuration::class, self::$config);
		self::setAsSingleton(ConnectionManager::class, self::getInstanceOf(ConnectionManager::class));
		self::setAsSingleton(Request::class, self::getInstanceOf(Request::class));
		self::setAsSingleton(Router::class, Router::getInstance());
		self::setAsSingleton(Language::class, Language::getInstance());

		self::$request = self::getInstanceOf(Request::class);
		self::$curr_actor = self::getInstanceOf(ActorModel::class);
		self::$curr_actor_role = self::getInstanceOf(ActorRoleModel::class);
	}

	/**
	 * Adds a class that's an instanceof AMiddleware to the middleware array
	 *
	 * @param string $middleware
	 * @return void
	 * @throws SystemException
	 */
	public function addMiddleware(string $middleware): void {
		if( get_parent_class($middleware) === AMiddleware::class ) {
			$this->middleware[] = $middleware;
		} else {
			throw new SystemException(__FILE__, __LINE__, $middleware . " is not a valid implementation of " . AMiddleware::class, 1001);
		}
	}

	/**
	 * Gets the required controller for the current request
	 * and performs the requested method.
	 * Sets the lib\core\blueprints\AResponse this method will return.
	 *
	 * @throws SystemException - if no valid controller and its method was found
	 */
	public function run(): void {
		// Initiate general settings
		$environment = self::$config->getSectionValue("system", "environment");
		$debug = self::$config->getSectionValue($environment, "debug");
		self::$storage->set("debug_mode", (bool)$debug);
		self::$storage->set("is_cached", false);

		self::$analyser->start();

		// some classes need a db connections to be initialized, we do them now
		self::setAsSingleton(RoleTree::class, RoleTree::getInstance());

		// process all middlewares
		foreach( $this->middleware as $middleware ) {
			(self::getInstanceOf($middleware))->invoke();
		}

		// clear the garbage
		$gc = self::getInstanceOf(GarbageCollector::class);
		$gc->clean();

		// Try to getInstanceOf the responsible route for this requested uri
		$router = self::getInstanceOf(Router::class);
		try {
			$route = $router->getRoute(self::$request);
			if( empty($route) ) { // no route found
				self::$request->setRequestUri("/error/404");
				$route = $router->getRoute(self::$request);
			}
		} catch( Exception $e ) { // route was found but with mismatching arguments
			self::$storage->set("message", $e->getMessage());
			self::$request->setRequestUri("/error/400");
			$route = $router->getRoute(self::$request);
		}

		// Get the controller
		$controller = $route["controller"];

		// Is it a compatible controller?
		if( $controller instanceof AController ) {

			// Get the method and its parameters
			$method = $route["method"];
			$params = $route["params"];

			// Set the actor role for the current request
			self::$curr_actor_role = self::$curr_actor->getRole($controller::class, $method);

			// Set the Authentication class
			self::$auth = self::getInstanceOf(Auth::class);

			// Has the current actor access to this request?
			if( self::$auth->hasAccess() ) {
				// Get the Response obj from the controller
				$this::$response = call_user_func_array([$controller, $method], $params);
			} else {
				redirect("/error/403");
			}
		} else {
			// No valid controller found
			throw new SystemException(__FILE__, __LINE__, "Controller for request " . self::$request->getRequestUri() . " cant be found!");
		}
	}

	/**
	 * Returns the output from the current AResponse object
	 *
	 * @return string
	 */
	public function getResponseOutput(): string {
		$this::$response->setHeaders();
		$output = $this::$response->getOutput();
		$elapsed_time = self::$analyser->getElapsedTime()->format("ms");
		$is_cached = (self::$storage->get("is_cached")) ? "true" : "false";
		return str_replace(array(
			"{{system_message}}",
			"{{build_time}}",
			"{{is_cached}}"
		), array(TemplateData::getSystemMessage(), $elapsed_time, $is_cached), $output);
	}

	/**
	 * Adds the given classname or callable function to the class manager under the given namespace
	 *
	 * @param string $namespace
	 * @param callable|string $concrete
	 *
	 * @return void
	 */
	public static function set(string $namespace, callable|string $concrete): void {
		self::$class_manager->set($namespace, $concrete);
	}

	/**
	 * Stores the given instance of a class into the class manager under the given namespace
	 * and will the class manager will return instances of this class as if it has the singleton architecture
	 *
	 * @param string $namespace
	 * @param object $instance
	 *
	 * @return void
	 */
	public static function setAsSingleton(string $namespace, object $instance): void {
		self::$class_manager->singleton($namespace, $instance);
	}

	/**
	 * Returns an instance of the given classname if it exists via the class manager
	 *
	 * @param string $namespace
	 * @param string|null $method
	 * @param array $args
	 *
	 * @return mixed
	 *
	 * @throws SystemException
	 */
	public static function getInstanceOf(string $namespace, ?string $method = null, array $args = []): mixed {
		if( isset($args["id"]) && is_numeric($args["id"]) && count($args) === 1 ) {
			$id = (int)$args["id"];
			$obj = self::$ob_cache->get($namespace, $id);
			if( is_null($obj) ) {
				$obj = self::$class_manager->get($namespace, $method, $args);
				self::$ob_cache->set($namespace, $id, $obj);
			}
			return $obj;
		}
		return self::$class_manager->get($namespace, $method, $args);
	}

}
