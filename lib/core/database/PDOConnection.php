<?php

namespace lib\core\database;

use DateMalformedStringException;
use DateTime;
use Exception;
use lib\App;
use lib\core\blueprints\ADBConnection;
use lib\core\classes\Logger;
use lib\core\enums\DbType;
use lib\core\exceptions\SystemException;
use PDO;
use PDOException;
use PDOStatement;

#start

/**
 * @method QueryBuilder Select(mixed $columns = null)
 * @method QueryBuilder Insert(string $table)
 * @method QueryBuilder Update(string $table)
 * @method QueryBuilder Delete(string $table)
 * @method QueryBuilder Truncate(string $table)
 */
class PDOConnection {

	private PDO $pdo;
	private QueryBuilder $qb;
	private ?Logger $logger;
	private bool $table_infos_loaded = false;
	public array $table_infos = [];
	protected int $db_version;
	protected DbType $db_type;
	private string $db_name = "";

	/**
	 * The Constructor
	 *
	 * @param ADBConnection $conn
	 * @throws SystemException
	 */
	public function __construct(ADBConnection $conn) {
		$this->db_name = $conn->dbname;
		$this->pdo = new PDO($conn->getConnectionString(), $conn->user, $conn->pass, $conn->getConnectionOptions());
		$this->db_type = $conn->getType();
		$this->logger = App::getInstanceOf(Logger::class, null, ["log_type" => "database"]);

		$version_info = $this->getVersion();
		$this->db_version = $version_info['base'];
		if( $this->db_type === DbType::MsSQL && $this->db_version < 2012 ) {
			throw new SystemException(__FILE__, __LINE__, "QueryBuilder does not support MsSQL version " . $this->db_version);
		}
	}

	// ==================== Dynamische Delegation ====================

	/**
	 * Calls a QueryBuilder method.
	 *
	 * @param string $method
	 * @param array $args
	 * @return QueryBuilder|self
	 * @throws SystemException
	 */
	public function __call(string $method, array $args): QueryBuilder|self {
		if( !method_exists(QueryBuilder::class, $method) ) {
			throw new SystemException(__FILE__, __LINE__, "Method $method does not exist");
		}

		// Start methods of the QueryBuilder
		if( in_array($method, ['Select', 'Insert', 'Update', 'Delete', 'Truncate']) ) {
			$this->qb = new QueryBuilder($this->pdo, $this->db_type);
			$this->qb->$method(...$args);
			return $this;
		}
		if( !isset($this->qb) ) {
			throw new SystemException(__FILE__, __LINE__, "No query initialized. Call Select/Insert/Update/Delete first.");
		}

		// All the rest methods
		$result = $this->qb->$method(...$args);

		// All methods returning QueryBuilder return PDOConnection instead.
		if( $result === $this->qb ) {
			return $this;
		}

		// return the $result from the QueryBuilder
		return $result;
	}

	// ==================== Spezielle Hilfsfunktionen ====================

	/**
	 * Prepares the given query
	 *
	 * @param string $sql
	 * @param array $options
	 * @return void
	 * @throws SystemException
	 */
	public function prepareQuery(string $sql, array $options = []): void {
		$this->qb = new QueryBuilder($this->pdo, $this->db_type);
		$this->qb->sql = $sql;
		$this->qb->prepareStatement($options);
	}

	/**
	 * St the fetch mode
	 *
	 * @param int $mode
	 * @param mixed|null $class
	 * @return $this
	 */
	public function fetchMode(int $mode = PDO::FETCH_ASSOC, mixed $class = null): static {
		$this->qb->fetchMode($mode, $class);
		return $this;
	}

	/**
	 * Executes the current query
	 *
	 * @param array $options
	 * @return PDOStatement
	 * @throws SystemException
	 */
	public function execute(array $options = []): PDOStatement {
		if( !$this->qb ) {
			throw new SystemException(__FILE__, __LINE__, "No query prepared");
		}
		try {
			$this->qb->stmt->execute();
		} catch( PDOException $e ) {
			$this->logger->log(__FILE__, __LINE__, $e->getMessage() . "\n\t=>\t[SQL] " . $this->getFinalizedQuery());
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
		//App::$analyser->stop()->add($this->getFinalizedQuery());
		return $this->qb->stmt;
	}

	/**
	 * Returns the used query string with set given parameters
	 *
	 * @return string
	 */
	public function getFinalizedQuery(): string {
		if( !$this->qb ) {
			return "";
		}
		$sql = $this->qb->sql;
		$params = $this->qb->params;


		uksort($params, fn($a, $b) => strlen($b) <=> strlen($a));

		foreach( $params as $key => $value ) {
			if( is_null($value) ) {
				$val = "NULL";
			} elseif( is_bool($value) ) {
				$val = $value ? '1' : '0';
			} elseif( is_numeric($value) ) {
				$val = (string)$value;
			} else {
				$val = "'" . str_replace("'", "''", (string)$value) . "'";
			}
			$pattern = '/:' . preg_quote($key, '/') . '(?![A-Za-z0-9_])/';
			$sql = preg_replace($pattern, $val, $sql);
		}

		return $sql;
	}


	/**
	 * Returns the number of rows in a table or -1 if that table does not exist
	 *
	 * @param string $table_name
	 * @return int
	 * @throws DateMalformedStringException
	 * @throws SystemException
	 */
	public function getNumRowsOfTable(string $table_name): int {
		$this->ensureTableInfosLoaded();
		try {
			if( array_key_exists($table_name, $this->table_infos) ) {
				return $this->table_infos[$table_name]["num_rows"];
			}
			$result = $this->pdo->query("SELECT COUNT(*) AS num_rows FROM " . $table_name)->fetch();
			return (int)$result["num_rows"];
		} catch( Exception ) {
			return -1;
		}
	}

	// ==================== Tabelleninfos ====================

	/**
	 * Checks if the table information are loaded and loads then f necessary
	 *
	 * @return void
	 * @throws DateMalformedStringException
	 * @throws SystemException
	 */
	private function ensureTableInfosLoaded(): void {
		if( !$this->table_infos_loaded ) {
			$this->collectTableInfos($this->db_name);
			$this->table_infos_loaded = true;
		}
	}


	/**
	 * Load Table information of the current connected database
	 *
	 * @return void
	 * @throws SystemException
	 * @throws DateMalformedStringException
	 */
	private function collectTableInfos(): void {
		$results = [];
		try {
			if( $this->db_type === DbType::MySQL ) {
				$results = $this->pdo->query("
                    SELECT TABLE_NAME AS table_name, TABLE_ROWS AS num_rows,
                           CREATE_TIME AS created, UPDATE_TIME AS updated
                    FROM information_schema.TABLES 
                    WHERE TABLE_SCHEMA = '$this->db_name'
                ")->fetchAll(PDO::FETCH_ASSOC);
			} elseif( $this->db_type === DbType::MsSQL ) {
				$results = $this->pdo->query("
                    SELECT t.name AS table_name, i.rows AS num_rows,
                           t.create_date AS created, t.modify_date AS updated
                    FROM sys.tables t
                    INNER JOIN sysindexes i ON t.object_id = i.id
                    WHERE i.indid < 2
                ")->fetchAll(PDO::FETCH_ASSOC);
			} elseif( $this->db_type === DbType::Postgres ) {
				$results = $this->pdo->query("
                    SELECT relname AS table_name, n_live_tup AS num_rows,
                           NULL AS created, last_vacuum AS updated
                    FROM pg_stat_user_tables
                    WHERE schemaname = 'public'
                ")->fetchAll(PDO::FETCH_ASSOC);
			}
		} catch( Exception $e ) {
			$this->logger->log(__FILE__, __LINE__, $e->getMessage());
			return;
		}

		foreach( $results as $info ) {
			$last_mod = 0;
			if( !empty($info['created']) ) {
				$last_mod = new DateTime($info['created'])->getTimestamp();
			}
			if( !empty($info['updated']) ) {
				$last_mod = new DateTime($info['updated'])->getTimestamp();
			}
			$this->table_infos[$info['table_name']] = [
				"num_rows" => (int)($info["num_rows"] ?? 0),
				"modified" => $last_mod
			];
		}
	}

	// ==================== Version & Typ ====================

	/**
	 * Returns the database version
	 *
	 * @return array
	 */
	public function getVersion(): array {
		$sql = "";
		switch( $this->db_type ) {
			case DbType::Postgres:
			case DbType::MySQL:
				$sql = "SELECT VERSION() as version";
				break;
			case DbType::MsSQL:
				$sql = "SELECT @@VERSION as version";
				break;
		}
		if( $sql === "" )
			return ["base" => 0, "full" => "unknown"];

		$result = $this->pdo->query($sql)->fetch(PDO::FETCH_ASSOC);
		switch( $this->db_type ) {
			case DbType::Postgres:
				if( preg_match('/\s*PostgreSQL\s*((\d+)[0-9|.]+)\s*/i', $result["version"], $m) ) {
					return ["base" => (int)$m[1], "full" => $m[2]];
				}
				break;
			case DbType::MySQL:
				if( preg_match('/^\s*((\d+)[0-9|.]+)\s*/', $result["version"], $m) ) {
					return ["base" => (int)$m[1], "full" => $m[2]];
				}
				break;
			case DbType::MsSQL:
				if( preg_match('/SQL\s*Server\s*(\d+).*\-\s*([0-9|.]+)/i', $result["version"], $m) ) {
					return ["base" => (int)$m[1], "full" => $m[2]];
				}
				break;
		}
		return ["base" => 0, "full" => "unknown"];
	}

	/**
	 * Returns the database type
	 *
	 * @return DbType
	 */
	public function getType(): DbType {
		return $this->db_type;
	}
}
#end