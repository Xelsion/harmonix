<?php

namespace lib\core\database\connections;

use lib\core\blueprints\ADBConnection;
use lib\core\enums\DbType;
use PDO;

/**
 * The PostgresConnection class
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class PostgresConnection extends ADBConnection {

	private array $_options = array(
		PDO::ATTR_PERSISTENT         => false,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
	);

	/**
	 * Returns a formate connection string for a Postgres database
	 *
	 * @return string
	 */
	public function getConnectionString(): string {
		return sprintf("pgsql:host=%s;port=%d;dbname=%s", $this->host, $this->port, $this->dbname);
	}

	/**
	 * Returns the Postgres options that will be used in each query
	 *
	 * @return array
	 */
	public function getConnectionOptions(): array {
		return $this->_options;
	}

	/**
	 * @inheritDoc
	 */
	public function getType(): DbType {
		return DbType::Postgres;
	}
}
