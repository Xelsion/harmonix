<?php

namespace system\classes;

use PDO;
use PDOException;
use PDOStatement;
use RuntimeException;

/**
 * The PDOConnection
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class PDOConnection extends PDO {

	private ?Logger $logger = null;
	private PDOStatement $stmt;

	/**
	 * @param $dns
	 * @param $user
	 * @param $pass
	 * @param array $options
	 */
	public function __construct( $dns, $user, $pass, $options = [] ) {
		parent::__construct($dns, $user, $pass, $options);
		$this->stmt = new PDOStatement();
		$this->logger = new Logger("database");
	}

	/**
	 * @param string $query
	 * @param array $options
	 */
	public function prepare( $query, array $options = [] ) {
		$this->stmt = parent::prepare($query, $options);
	}

	/**
	 * @param string $key
	 * @param $value
	 * @param int $type
	 * @return bool
	 */
	public function bindParam( string $key, $value, int $type = PDO::PARAM_STR ): bool {
        if( $type !== PDO::PARAM_STR) {
		    return $this->stmt->bindValue($key, $value, $type);
        } else {
            return $this->stmt->bindValue($key, $value);
        }
	}

	/**
	 * @param int $mode
	 * @param $class
	 * @param array $params
	 */
	public function setFetchMode( int $mode, $class ): void {
		$this->stmt->setFetchMode($mode, $class);
	}

	/**
	 * @return int
	 */
	public function rowCount(): int {
		return $this->stmt->rowCount();
	}

	/**
	 * @param string $query
	 * @return PDOStatement
	 */
	public function run( string $query ): PDOStatement {
		return $this->query($query);
	}

	/**
	 * @return PDOStatement
	 */
	public function execute(): PDOStatement {
		try {
			$this->stmt->execute();
		} catch( PDOException $e ) {
			try {
				$this->logger->log($e->getFile(), $e->getLine(), $e->getMessage()."\n\t=>\t[SQL] ".$this->stmt->queryString, $e->getTrace());
			} catch( \JsonException $je ) {
				throw new RuntimeException($je->getMessage(), (int)$je->getCode(), $je);
			}
			throw new RuntimeException($e->getMessage(), (int)$e->getCode(), $e);
		}
		return $this->stmt;
	}
}