<?php

namespace system;

use Exception;
use JsonException;
use ReflectionException;

use system\abstracts\ADBConnection;
use system\abstracts\AController;
use system\abstracts\AResponse;
use system\classes\cache\ResponseCache;
use system\classes\connections\MsSqlConnection;
use system\classes\connections\MySqlConnection;
use system\classes\connections\PostgresConnection;
use system\classes\Language;
use system\classes\Storage;
use system\classes\TimeAnalyser;
use system\classes\Auth;
use system\classes\GarbageCollector;
use system\classes\tree\RoleTree;
use system\classes\tree\Menu;
use system\classes\Configuration;
use system\classes\Logger;
use system\classes\Request;
use system\classes\Router;
use system\manager\ConnectionManager;
use system\helper\StringHelper;
use system\exceptions\SystemException;

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

	// The response
	private AResponse $_response;

	/**
	 * The class constructor.
     * Initiates all important core objects
	 */
	private function __construct() {
        Core::$_storage = new Storage();
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
        // Initiate general settings
        $settings = Core::$_configuration->getSection("development");
        Core::$_storage::set("environment", $settings["environment"]);
        Core::$_storage::set("debug_mode", (bool)$settings["debug"]);

        Core::$_analyser = new TimeAnalyser();

        if( Core::$_storage::get("debug_mode") ) {
            Core::$_analyser->addTimer("template-parsing", "");
            Core::$_analyser->startTimer("template-parsing");
        }

        // Initiate session cookie settings
        $cookie = Core::$_configuration->getSection("cookie");
        ini_set('session.cookie_domain', $cookie["domain"]);
        session_start();

        // Initiate database connections
        $connections = Core::$_configuration->getSection("connections");
        foreach( $connections as $conn ) {
            $connection = match ( $conn["type"] ) {
                "postgres" => new PostgresConnection(),
                "mssql" => new MsSqlConnection(),
                "mysql" => new MySqlConnection(),
                default => null
            };

            if( $connection instanceof ADBConnection ) {
                $connection->_host = $conn["host"];
                $connection->_port = (int) $conn["port"];
                $connection->_dbname = $conn["dbname"];
                $connection->_user = $conn["user"];
                $connection->_pass = $conn["password"];
                Core::$_connection_manager->addConnection($connection);
            }
        }

        // clear the garbage
        $gc = new GarbageCollector();
        $gc->clean();

        // initiate actor roles tree
        Core::$_role_tree = RoleTree::getInstance();

        //$this->generateTestData();
        //$this->deleteTestData();

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
                Core::$_response_cache = ResponseCache::getInstance();

                // Always check on these files
                Core::$_response_cache->addFileCheck(PATH_ROOT."lang-de.ini");
                Core::$_response_cache->addFileCheck(PATH_ROOT."functions.php");
                Core::$_response_cache->addFileCheck(PATH_ROOT."constants.php");
                Core::$_response_cache->addFileCheck(PATH_SYSTEM."helper/HtmlHelper.php");
                Core::$_response_cache->addFileCheck(PATH_SYSTEM."helper/RequestHelper.php");
                Core::$_response_cache->addFileCheck(PATH_SYSTEM."helper/StringHelper.php");

                // Always check on these tables
                Core::$_response_cache->addDBCheck("mvc", "actors");
                Core::$_response_cache->addDBCheck("mvc", "actor_roles");
                Core::$_response_cache->addDBCheck("mvc", "access_permissions");
                Core::$_response_cache->addDBCheck("mvc", "access_restrictions");

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


    public function generateTestData():void {
        for( $i=0; $i<10000; $i++) {
            try {
                $email = StringHelper::getRandomString();
                $first_name = "TEST_". StringHelper::getRandomString();
                $last_name = StringHelper::getRandomString();
                $password = StringHelper::getRandomString();

                $pdo = Core::$_connection_manager->getConnection("mvc");
                $pdo->prepare("INSERT INTO actors (email, first_name, last_name, password) VALUES (:email, :first_name, :last_name, :password)");
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
            $pdo = Core::$_connection_manager->getConnection("mvc");
            $pdo->run("DELETE FROM actors WHERE first_name LIKE 'TEST_%'");
            $pdo->run("ALTER TABLE actors AUTO_INCREMENT=4");
        } catch( Exception $e ) {
            die($e->getMessage());
        }
    }

}
