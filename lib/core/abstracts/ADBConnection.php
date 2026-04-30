<?php

namespace lib\core\abstracts;

use lib\core\enums\DbType;

/**
 * The Abstract version of a DBConnection.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
abstract class ADBConnection {

	public string $host;
	public string $port;
	public string $dbname;
	public string $user;
	public string $pass;

	/**
	 * Returns a formatted connection string
	 *
	 * @return string
	 */
	abstract public function getConnectionString(): string;

	/**
	 * Returns the Connection options that will be used in each query
	 *
	 * @return array
	 */
	abstract public function getConnectionOptions(): array;

	/**
	 * Returns the DbType of the connection
	 *
	 * @return DbType
	 */
	abstract public function getType(): DbType;

}
