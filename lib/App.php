<?php
namespace lib;

use lib\core\ClassManager;
use lib\core\Request;
use lib\core\Router;
use lib\core\Storage;
use lib\abstracts\AController;
use lib\abstracts\ADBConnection;
use lib\abstracts\AMiddleware;
use lib\abstracts\AResponse;
use models\ActorModel;
use models\ActorRoleModel;
use lib\manager\ConnectionManager;
use lib\classes\Auth;
use lib\classes\Configuration;
use lib\classes\connections\MsSqlConnection;
use lib\classes\connections\MySqlConnection;
use lib\classes\connections\PostgresConnection;
use lib\classes\GarbageCollector;
use lib\classes\Language;
use lib\classes\TimeAnalyser;
use lib\classes\tree\RoleTree;
use lib\helper\StringHelper;

use Exception;
use lib\exceptions\SystemException;
/**
 * The App class of type setSingleton
 * This class handles the main procedure from getting a request
 * to return the response output
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class App {

    // The class manager with dependency injection
    private static ClassManager $class_manager;

    // The current request
    public static Request $request;

	// The current response
	public static ?AResponse $response = null;

    // The current actor
    public static ActorModel $curr_actor;

    // The current actor role
    public static ActorRoleModel $curr_actor_role;

    public static Auth $auth_settings;

    // The global accessible key=>value storage for the application
    public static Storage $storage;

    // An array of instances running as middleware
    private array $middleware = array();

    /**
     * The class constructor.
     * Initiates all important core objects
     *
     * @throws SystemException
     */
	public function __construct() {
        App::$class_manager = new ClassManager();
        App::setSingleton(Configuration::class, Configuration::getInstance());
        App::setSingleton(ConnectionManager::class, App::getInstance(ConnectionManager::class));
        App::setSingleton(Request::class, Request::getInstance());
        App::setSingleton(Router::class, Router::getInstance());
        App::setSingleton(Language::class, Language::getInstance());

        App::$request = App::getInstance(Request::class);
        App::$curr_actor = App::getInstance(ActorModel::class);
        App::$curr_actor_role = App::getInstance(ActorRoleModel::class);
        App::$storage = App::getInstance(Storage::class);

        App::$storage::set("analyser", App::getInstance(TimeAnalyser::class));
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
     * @throws SystemException - if no valid controller and its method was found
     */
	public function run(): void {
        // Initiate general settings
        $config = App::getInstance(Configuration::class);
        $environment = $config->getSectionValue("system", "environment");
        $debug = $config->getSectionValue($environment, "debug");
        App::$storage::set("debug_mode", (bool)$debug);
        App::$storage::set("is_cached", false);

        if( App::$storage::get("debug_mode") ) {
            $analyser = App::$storage::get("analyser");
            $analyser->addTimer("template-parsing");
            $analyser->startTimer("template-parsing");
        }

        // Initiate session cookie settings
        $cookie = $config->getSection("cookie");
        ini_set('session.cookie_domain', $cookie["domain"]);
        session_start();

        // Initiate database connections
        $cm = App::getInstance(ConnectionManager::class);
        $connections =  $config->getSection("connections");
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
                $cm->addConnection($connection);
            }
        }

        // some classes need db connections to be initialized, we do them now
        App::setSingleton(RoleTree::class, RoleTree::getInstance());

        // process all middlewares
        foreach( $this->middleware as $middleware ) {
            call_user_func_array( [$middleware, "proceed"], [] );
        }

        // clear the garbage
        $gc = App::getInstance(GarbageCollector::class);
        $gc->clean();

        //$this->generateTestData();
        //$this->deleteTestData();

        // Try to getInstance the responsible route for this requested uri
        $router = App::getInstance(Router::class);
        try {
            $route = $router->getRoute(App::$request);
            if( empty($route) ) { // no route found
                App::$request->setRequestUri("/error/404");
                $route = $router->getRoute(App::$request);
            }
        } catch( Exception $e ) { // route was found but with mismatching arguments
            App::$storage::set("message", $e->getMessage());
            App::$request->setRequestUri("/error/400");
            $route = $router->getRoute(App::$request);
        }

        // Get the controller
        $controller = $route["controller"];

        // Is it a compatible controller?
        if( $controller instanceof AController ) {
            // Get the method and its parameters
            $method = $route["method"];
            $params = $route["params"];

            // Set the actor role for the current request
            App::$curr_actor_role = App::$curr_actor->getRole($controller::class, $method);

            // Set the Authentication class
            App::$auth_settings = App::getInstance(Auth::class);

            // Has the current actor access to this request?
            if( App::$auth_settings->hasAccess() ) {
                // Get the Response obj from the controller
                $this::$response = call_user_func_array([$controller, $method], $params);
                $this::$response->setHeaders();
            } else {
                redirect("/error/403");
            }
        } else {
            // No valid controller found
            throw new SystemException(__FILE__, __LINE__, "Controller for request ".App::getInstance(Request::class)->getRequestUri()." cant be found!");
        }

	}

	/**
	 * Returns the output from the current AResponse object
	 *
	 * @return string
	 */
    public function getResult(): string {
        $output = $this::$response->getOutput();
        $analyser = App::$storage::get("analyser");
        $analyser->stopTimer("template-parsing");
        $elapsed_time = $analyser->getTimerElapsedTime("template-parsing");
        $elapsed_time = round($elapsed_time * 1000, 2);
        $is_cached = ( App::$storage::get("is_cached") ) ? "true" : "false";
        $output = str_replace("{{is_cached}}", $is_cached, $output);
        return str_replace("{{build_time}}", $elapsed_time."ms",$output);
	}

    /**
     * @param string $namespace
     * @param callable|string $concrete
     *
     * @return void
     */
    public static function setClass(string $namespace, callable|string $concrete ): void {
        App::$class_manager->set($namespace, $concrete);
    }

    /**
     * @param string $namespace
     * @param object $instance
     *
     * @return void
     */
    public function setSingleton(string $namespace, object $instance ): void {
        App::$class_manager->singleton($namespace, $instance);
    }

    /**
     * @param string $namespace
     * @param string|null $method
     * @param array $args
     *
     * @return mixed
     *
     * @throws SystemException
     */
    public static function getInstance(string $namespace, ?string $method = null, array $args = []): mixed {
        return App::$class_manager->get($namespace, $method, $args);
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

                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
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
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $pdo->run("DELETE FROM actors WHERE first_name LIKE 'TEST_%'");
            $pdo->run("ALTER TABLE actors AUTO_INCREMENT=4");
        } catch( Exception $e ) {
            die($e->getMessage());
        }
    }

}
