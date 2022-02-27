<?php

namespace core\manager;

use PDO;
use PDOException;
use RuntimeException;

class ConnectionManager {

	private array $_connections;
	private array $options = array( PDO::ATTR_PERSISTENT => true, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_ERRMODE );

	public function __construct() {

	}

	public function addConnection( string $name, string $dns, string $user, string $pass ): void {
		try {
			$conn = new PDO($dns, $user, $pass, $this->options);
			$this->_connections[$name] = $conn;
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	public function getConnection( string $name ) {
		return $this->_connections[$name] ?? null;
	}

}