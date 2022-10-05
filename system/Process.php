<?php

namespace system;

use JsonException;
use ReflectionException;
use system\classes\Language;
use system\exceptions\SystemException;

use system\abstracts\AController;
use system\abstracts\AResponse;
use system\manager\ConnectionManager;
use system\classes\Auth;
use system\classes\GarbageCollector;
use system\classes\tree\RoleTree;
use system\classes\tree\Menu;
use system\classes\Configuration;
use system\classes\Logger;
use system\classes\Request;
use system\classes\Router;
use models\Session;

/**
 * The Process class of type singleton
 * This class handles the main procedure from getting a request
 * to return the response output
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Process {

	// The instance of this class
	private static ?Process $_instance = null;

	// The router
	private AResponse $_response;

	/**
	 * The class constructor.
     * Initiates all important core objects
	 */
	private function __construct() {
		Core::$_configuration = Configuration::getInstance();
		Core::$_debugger = new Logger("debug");
		Core::$_connection_manager = new ConnectionManager();
		Core::$_request = Request::getInstance();
		Core::$_menu = new Menu();
		Core::$_router = Router::getInstance();
        Core::$_lang = Language::getInstance("de");
	}

	/**
	 * The initializer for this class
	 *
	 * @return Process
	 */
	public static function getInstance(): Process {
		if( static::$_instance === null ) {
			static::$_instance = new Process();
		}
		return static::$_instance;
	}

    /**
     * Gets the required controller for the current request
     * and performs the requested method.
     * Sets the system\abstracts\AResponse this method will return.
     *
     * @throws SystemException|ReflectionException|JsonException - if no valid controller and its method was found
     */
	public function start(): void {

        // Initiate session cookie settings
        $cookie = Core::$_configuration->getSection("cookie");
        ini_set('session.cookie_domain', $cookie["domain"]);
        session_start();

        // Initiate database connections
        $connections = Core::$_configuration->getSection("connections");
        foreach( $connections as $name => $conn ) {
            Core::$_connection_manager->addConnection($name, $conn["dns"], $conn["user"], $conn["password"]);
        }

        // clear the garbage
        $gc = new GarbageCollector();
        $gc->clean();

        // initiate actor roles tree
        Core::$_role_tree = RoleTree::getInstance();

        // initiate the session
        $session = new Session();
        Core::$_actor = $session->start();

        // Try to get the responsible route for this requested uri
        $route = Core::$_router->getRoute(Core::$_request);

        // Get the controller
        $controller = $route["controller"];

        // Is it a compatible controller?
        if( $controller instanceof AController ) {
            // Get the method and its parameters
            $method = $route["method"];
            $params = $route["params"];

            // Set the actor role for the current request
            Core::$_actor_role = Core::$_actor->getRole(get_class($controller), $method);

            // Set the Authentication class
            Core::$_auth = new Auth();

            // Has the current actor access to this request?
            if( Core::$_auth->hasAccess() ) {
                // Get the Response obj from the controller
                $this->_response = $controller->$method(...$params);
                $this->_response->setHeaders();
            } else {
                redirect("/error/403");
            }

        } else {
            // No valid controller found
            throw new SystemException(__FILE__, __LINE__, "Controller for request ".Core::$_request->getRequestUri()." cant be found!");
        }
	}

	/**
	 * Returns the output from the current AResponse object
	 *
	 * @return string
	 */
	public function getResult(): string {
		return $this->_response->getOutput();
	}
}
