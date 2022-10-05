<?php

namespace system\manager;

use PDO;
use PDOException;
use RuntimeException;
use system\classes\PDOConnection;

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
	private array $options = array(
		PDO::ATTR_PERSISTENT         => true,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
	);

	/**
	 * the class constructor
	 */
	public function __construct() {

	}

	/**
	 * Adds a connection the available connections
	 *
	 * @param string $name
	 * @param string $dns
	 * @param string $user
	 * @param string $pass
	 */
	public function addConnection( string $name, string $dns, string $user, string $pass ): void {
		$this->_connections[$name] = array(
			"dns"  => $dns,
			"user" => $user,
			"pass" => $pass
		);
	}

	/**
	 * Returns a connections
	 * First checks if the connection is already active, if so returns it
	 * else it activates the connection, stores it to the active connections
	 * and returns it.
	 *
	 * @param string $name
	 * @return mixed|PDO
	 */
	public function getConnection( string $name ) {
		// is the connection already active?
		if( isset($this->_active_connections[$name]) ) {
			return $this->_active_connections[$name];
		}

		// check if it's an available connection
		if( isset($this->_connections[$name]) ) {
			$conn_array = $this->_connections[$name];
			try {
				// try to establish the connection
				$conn = new PDOConnection($conn_array["dns"], $conn_array["user"], $conn_array["pass"], $this->options);
				// add it to the active connections
				$this->_active_connections[$name] = $conn;
				return $conn;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage(), $e->getCode(), $e->getTrace());
			}
		} else {
			throw new RuntimeException("ConnectionManager: [".$name."] connection not found");
		}
	}

}
