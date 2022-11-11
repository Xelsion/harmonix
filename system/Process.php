<?php
namespace system;

use Exception;
use JsonException;
use ReflectionException;
use system\abstracts\AController;
use system\abstracts\ADBConnection;
use system\abstracts\AResponse;
use system\classes\Auth;
use system\classes\cache\ResponseCache;
use system\classes\Configuration;
use system\classes\connections\MsSqlConnection;
use system\classes\connections\MySqlConnection;
use system\classes\connections\PostgresConnection;
use system\classes\GarbageCollector;
use system\classes\Language;
use system\classes\Logger;
use system\classes\Login;
use system\classes\Request;
use system\classes\Router;
use system\classes\TimeAnalyser;
use system\classes\tree\Menu;
use system\classes\tree\RoleTree;
use system\exceptions\SystemException;
use system\helper\StringHelper;
use system\manager\ConnectionManager;

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

	// The response
	private ?AResponse $_response = null;

	/**
	 * The class constructor.
     * Initiates all important core objects
	 */
	private function __construct() {
        // init system
        System::$Core = Core::getInstance();
        System::$Storage = new Storage();

        // init system core classes
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
        // Initiate general settings
        $environment = System::$Core->configuration->getSectionValue("system", "environment");
        $debug = System::$Core->configuration->getSectionValue($environment, "debug");
        System::$Storage::set("debug_mode", (bool)$debug);

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

        // clear the garbage
        $gc = new GarbageCollector();
        $gc->clean();

        // initiate actor roles tree
        System::$Core->role_tree = RoleTree::getInstance();

        //$this->generateTestData();
        //$this->deleteTestData();

        // initiate the session
        $session = new Login();
        System::$Core->actor = $session->start();

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
            System::$Core->actor_role = System::$Core->actor->getRole(get_class($controller), $method);

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
                System::$Core->response_cache->addFileCheck(PATH_SYSTEM."helper/HtmlHelper.php");
                System::$Core->response_cache->addFileCheck(PATH_SYSTEM."helper/RequestHelper.php");
                System::$Core->response_cache->addFileCheck(PATH_SYSTEM."helper/StringHelper.php");

                // Always check on these tables
                System::$Core->response_cache->addDBCheck("mvc", "actors");
                System::$Core->response_cache->addDBCheck("mvc", "actor_roles");
                System::$Core->response_cache->addDBCheck("mvc", "access_permissions");
                System::$Core->response_cache->addDBCheck("mvc", "access_restrictions");

                // Get the Response obj from the controller
                $this->_response = $controller->$method(...$params);
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
