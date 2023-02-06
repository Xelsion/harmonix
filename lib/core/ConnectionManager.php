<?php
namespace lib\core;

use Exception;
use lib\core\blueprints\ADBConnection;
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
	public function __construct() {

	}

    /**
     * Adds a connection the available connections
     *
     * @param ADBConnection $conn
     */
	public function addConnection( ADBConnection $conn ): void {
        $this->_connections[$conn->dbname] = $conn;
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
	public function getConnection( string $dbname, bool $singleton = true ): mixed {
		// is the connection already active?
		if( isset($this->_active_connections[$dbname]) && $singleton ) {
			return $this->_active_connections[$dbname];
		}

		// check if it's an available connection
		if( isset($this->_connections[$dbname]) ) {
			$conn = $this->_connections[$dbname];
			try {
				// try to establish the connection
				$pdo_conn = new PDOConnection($conn);
				// add it to the active connections
                if( $singleton ) {
				    $this->_active_connections[$dbname] = $pdo_conn;
                }
				return $pdo_conn;
			} catch( Exception $e ) {
				throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
        } else {
			throw new SystemException(__FILE__, __LINE__,"ConnectionManager: [".$dbname."] connection not found");
		}
	}

}
