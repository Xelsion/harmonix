<?php
namespace lib\core;

use Exception;
use lib\core\blueprints\ADBConnection;
use lib\core\classes\Configuration;
use lib\core\database\connections\MsSqlConnection;
use lib\core\database\connections\MySqlConnection;
use lib\core\database\connections\PostgresConnection;
use lib\core\database\PDOConnection;
use lib\core\exceptions\SystemException;

/**
 * This class will handle all database connections
 * required by the application
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ConnectionManager {

	// the available connections
	private array $_connections;
	// the active connections
	private array $_active_connections = array();
	// the pdo options for all connections

	/**
	 * the class constructor
	 */
	public function __construct( Configuration $config ) {
        $connections =  $config->getSection("connections");
        foreach( $connections as $key => $conn ) {
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
                $this->addConnection($key, $connection);
            }
        }
	}

    /**
     * Adds a connection the available connections
     *
     * @param ADBConnection $conn
     */
	public function addConnection( string $key, ADBConnection $conn ): void {
        $this->_connections[$key] = $conn;
	}

    /**
     * Returns an array of all available connection keys
     *
     * @return array
     */
    public function getAvailableConnections(): array {
        return array_keys($this->_connections);
    }

    /**
     * Returns a connections
     * First checks if the connection is already active, if so returns it
     * else it activates the connection, stores it to the active connections
     * and returns it.
     *
     * @param string $dbname
     * @param bool $singleton
     *
     * @return mixed|\lib\core\database\PDOConnection
     *
     * @throws \lib\core\exceptions\SystemException
     */
	public function getConnection( string $key, bool $singleton = true ): mixed {
		// is the connection already active?
		if( isset($this->_active_connections[$key]) && $singleton ) {
			return $this->_active_connections[$key];
		}

		// check if it's an available connection
		if( isset($this->_connections[$key]) ) {
			$conn = $this->_connections[$key];
			try {
				// try to establish the connection
				$pdo_conn = new PDOConnection($conn);
				// add it to the active connections
                if( $singleton ) {
				    $this->_active_connections[$key] = $pdo_conn;
                }
				return $pdo_conn;
			} catch( Exception $e ) {
				throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
        } else {
			throw new SystemException(__FILE__, __LINE__,"ConnectionManager: [".$key."] connection not found");
		}
	}

}
