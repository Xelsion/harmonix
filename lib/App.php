<?php
namespace lib;

use Exception;
use JsonException;
use lib\abstracts\AController;
use lib\abstracts\ADBConnection;
use lib\abstracts\AMiddleware;
use lib\abstracts\AResponse;
use lib\classes\Auth;
use lib\classes\cache\ResponseCache;
use lib\classes\Configuration;
use lib\classes\connections\MsSqlConnection;
use lib\classes\connections\MySqlConnection;
use lib\classes\connections\PostgresConnection;
use lib\classes\GarbageCollector;
use lib\classes\Language;
use lib\classes\Logger;
use lib\classes\R;
use lib\classes\Test;
use lib\classes\TimeAnalyser;
use lib\classes\tree\Menu;
use lib\classes\tree\RoleTree;
use lib\core\ClassManager;
use lib\core\Core;
use lib\core\Request;
use lib\core\Router;
use lib\core\Storage;
use lib\core\System;
use lib\exceptions\SystemException;
use lib\helper\StringHelper;
use lib\manager\ConnectionManager;
use ReflectionException;

/**
 * The App class of type singleton
 * This class handles the main procedure from getting a request
 * to return the response output
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class App {

	// The instance of this class
	private static ?App $_instance = null;

	// The response
	private ?AResponse $_response = null;

    private array $middleware = array();

	/**
	 * The class constructor.
     * Initiates all important core objects
	 */
	private function __construct() {
        // init lib
        System::$Core = Core::getInstance();
        System::$Storage = new Storage();
        System::$ClassManager = new ClassManager();

        // init lib core classes
        System::$Core->configuration = Configuration::getInstance();
        System::$Core->debugger = new Logger("debug");
        System::$Core->connection_manager = new ConnectionManager();
        System::$Core->request = Request::getInstance();
        System::$Core->menu = new Menu();
        System::$Core->router = Router::getInstance();
        System::$Core->lang = Language::getInstance();
        System::$Core->analyser = new TimeAnalyser();
	}

	/**
	 * The initializer for this class
	 *
	 * @return App
	 */
	public static function getInstance(): App {
		if( static::$_instance === null ) {
			static::$_instance = new App();
		}
		return static::$_instance;
	}

    /**
     * Adds a class that's an instanceof AMiddleware to the middleware array
     *
     * @param string $middleware
     * @return void
     * @throws SystemException
     */
    public function addMiddleware( string $middleware ): void {
        if( get_parent_class($middleware) === AMiddleware::class ) {
            $this->middleware[] = $middleware;
        } else {
            throw new SystemException( __FILE__, __LINE__,$middleware . " is not a valid implementation of ".AMiddleware::class);
        }
    }

    /**
     * Gets the required controller for the current request
     * and performs the requested method.
     * Sets the lib\abstracts\AResponse this method will return.
     *
     * @throws SystemException|ReflectionException|JsonException - if no valid controller and its method was found
     */
	public function run(): void {
        // Initiate general settings
        $environment = System::$Core->configuration->getSectionValue("system", "environment");
        $debug = System::$Core->configuration->getSectionValue($environment, "debug");
        System::$Storage::set("debug_mode", (bool)$debug);
        System::$Storage::set("is_cached", false);

        if( System::$Storage::get("debug_mode") ) {
            System::$Core->analyser->addTimer("template-parsing");
            System::$Core->analyser->startTimer("template-parsing");
        }

        // Initiate session cookie settings
        $cookie = System::$Core->configuration->getSection("cookie");
        ini_set('session.cookie_domain', $cookie["domain"]);
        session_start();

        // Initiate database connections
        $connections =  System::$Core->configuration->getSection("connections");
        foreach( $connections as $conn ) {
            $connection = match ( $conn["type"] ) {
                "postgres" => new PostgresConnection(),
                "mssql" => new MsSqlConnection(),
                "mysql" => new MySqlConnection(),
                default => null
            };

            if( $connection instanceof ADBConnection ) {
                $connection->host = $conn["host"];
                $connection->port = (int) $conn["port"];
                $connection->dbname = $conn["dbname"];
                $connection->user = $conn["user"];
                $connection->pass = $conn["password"];
                System::$Core->connection_manager->addConnection($connection);
            }
        }

        // process all middlewares
        foreach( $this->middleware as $middleware ) {
            call_user_func_array( [$middleware, "proceed"], [] );
        }

        // initiate actor roles tree
        System::$Core->role_tree = RoleTree::getInstance();

        // clear the garbage
        $gc = new GarbageCollector();
        $gc->clean();

        //$this->generateTestData();
        //$this->deleteTestData();

        // Try to get the responsible route for this requested uri
        try {
            $route = System::$Core->router->getRoute(System::$Core->request);
            if( empty($route) ) { // no route found
                System::$Core->request->setRequestUri("/error/404");
                $route = System::$Core->router->getRoute(System::$Core->request);
            }
        } catch( Exception $e ) { // route was found but with mismatching arguments
            System::$Storage::set("message", $e->getMessage());
            System::$Core->request->setRequestUri("/error/400");
            $route = System::$Core->router->getRoute(System::$Core->request);
        }

        // Get the controller
        $controller = $route["controller"];

        // Is it a compatible controller?
        if( $controller instanceof AController ) {
            // Get the method and its parameters
            $method = $route["method"];
            $params = $route["params"];

            // Set the actor role for the current request
            System::$Core->actor_role = System::$Core->actor->getRole($controller::class, $method);

            // Set the Authentication class
            System::$Core->auth = new Auth();

            // Has the current actor access to this request?
            if( System::$Core->auth->hasAccess() ) {
                System::$Core->response_cache = ResponseCache::getInstance();

                // Always check on these files
                System::$Core->response_cache->addFileCheck(__FILE__);
                System::$Core->response_cache->addFileCheck(PATH_ROOT."lang-de.ini");
                System::$Core->response_cache->addFileCheck(PATH_ROOT."functions.php");
                System::$Core->response_cache->addFileCheck(PATH_ROOT."constants.php");
                System::$Core->response_cache->addFileCheck(PATH_LIB."helper/HtmlHelper.php");
                System::$Core->response_cache->addFileCheck(PATH_LIB."helper/RequestHelper.php");
                System::$Core->response_cache->addFileCheck(PATH_LIB."helper/StringHelper.php");

                // Always check on these tables
                System::$Core->response_cache->addDBCheck("mvc", "actors");
                System::$Core->response_cache->addDBCheck("mvc", "actor_roles");
                System::$Core->response_cache->addDBCheck("mvc", "access_permissions");
                System::$Core->response_cache->addDBCheck("mvc", "access_restrictions");

                // Get the Response obj from the controller
                $this->_response = call_user_func_array([$controller, $method], $params);
                $this->_response->setHeaders();
            } else {
                redirect("/error/403");
            }
        } else {
            // No valid controller found
            throw new SystemException(__FILE__, __LINE__, "Controller for request ".System::$Core->request->getRequestUri()." cant be found!");
        }

	}

	/**
	 * Returns the output from the current AResponse object
	 *
	 * @return string
	 */
    public function getResult(): string {
        $output = $this->_response->getOutput();
        System::$Core->analyser->stopTimer("template-parsing");
        $elapsed_time = System::$Core->analyser->getTimerElapsedTime("template-parsing");
        $elapsed_time = round($elapsed_time * 1000, 2);
        $is_cached = ( System::$Storage::get("is_cached") ) ? "true" : "false";
        $output = str_replace("{{is_cached}}", $is_cached, $output);
        return str_replace("{{build_time}}", $elapsed_time."ms",$output);
	}


    /**
     * @return void
     */
    public function generateTestData():void {
        for( $i=0; $i<10000; $i++) {
            try {
                $email = StringHelper::getRandomString();
                $first_name = "TEST_". StringHelper::getRandomString();
                $last_name = StringHelper::getRandomString();
                $password = StringHelper::getRandomString();

                $pdo = System::$Core->connection_manager->getConnection("mvc");
                $pdo->prepareQuery("INSERT INTO actors (email, first_name, last_name, password) VALUES (:email, :first_name, :last_name, :password)");
                $pdo->bindParam('email', $email);
                $pdo->bindParam('first_name', $first_name);
                $pdo->bindParam('last_name', $last_name);
                $pdo->bindParam('password', $password);
                $pdo->execute();
            } catch( Exception $e ) {
                die($e->getMessage());
            }
        }
    }

    private function deleteTestData(): void {
        try {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $pdo->run("DELETE FROM actors WHERE first_name LIKE 'TEST_%'");
            $pdo->run("ALTER TABLE actors AUTO_INCREMENT=4");
        } catch( Exception $e ) {
            die($e->getMessage());
        }
    }

}
