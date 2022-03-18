<?php

namespace system;

use Exception;
use RuntimeException;
use system\classes\tree\RoleTree;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\manager\ConnectionManager;
use system\classes\Configuration;
use system\classes\Logger;
use system\classes\Request;
use system\classes\Router;
use system\classes\tree\Menu;
use models\Session;


/**
 * The System Class Type singleton
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
	 * @throws RuntimeException - if no valid controller and its method was found
	 */
	public function start(): void {
		try {
            // Initiate session cookie settings
            $cookie = Core::$_configuration->getSection("cookie");
            ini_set('session.cookie_domain', $cookie["domain"]);
            session_start();

            // Initiate database connections
            $connections = Core::$_configuration->getSection("connections");
            foreach( $connections as $name => $conn ) {
                Core::$_connection_manager->addConnection($name, $conn["dns"], $conn["user"], $conn["password"]);
            }

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

				// Get the Response obj from the controller
				$this->_response = $controller->$method(...$params);
				$this->_response->setHeaders();
			} else {
			    // No valid controller found
			    throw new RuntimeException("Controller for request ".Core::$_request->getRequestUri()." cant be found!");
            }
		} catch( Exception $e ) {
			// Pass all exceptions to the index.php
			throw new RuntimeException($e->getMessage());
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