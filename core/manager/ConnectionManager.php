<?php

namespace core\manager;

use PDO;
use PDOException;
use RuntimeException;

class ConnectionManager {

	private array $_connections;
    private array $_active_connections = array();
	private array $options = array( PDO::ATTR_PERSISTENT => true, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE );

	public function __construct() {

	}

	public function addConnection( string $name, string $dns, string $user, string $pass ): void {
        $this->_connections[$name] = array(
            "dns" => $dns,
            "user" => $user,
            "pass" => $pass
        );
	}

	public function getConnection( string $name ) {
        if( isset($this->_active_connections[$name]) ) {
            return $this->_active_connections[$name];
        }

        if( isset($this->_connections[$name]) ) {
            $conn_array = $this->_connections[$name];
            try {
                $conn = new PDO($conn_array["dns"], $conn_array["user"], $conn_array["pass"], $this->options);
                $this->_active_connections[$name] = $conn;
                return $conn;
            } catch( PDOException $e ) {
                throw new RuntimeException($e->getMessage());
            }
        } else {
            throw new RuntimeException("ConnectionManager: [".$name."] connection not found");
        }
	}

}