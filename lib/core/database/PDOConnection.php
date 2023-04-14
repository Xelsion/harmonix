<?php
namespace lib\core\database;

use DateTime;
use Exception;
use JsonException;
use lib\App;
use lib\core\blueprints\ADBConnection;
use lib\core\classes\Logger;
use lib\core\enums\DbType;
use lib\core\exceptions\SystemException;
use PDO;
use PDOException;
use PDOStatement;

/**
 * The PDOConnection
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class PDOConnection extends QB {

	private ?Logger $logger;

    private ADBConnection $conn;

    private array $table_infos = array();

    private string $used_query = "";

    private array $used_params = array();

    protected DbType $db_type;

    protected int $db_version;

    /**
     * @param ADBConnection $conn
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function __construct( ADBConnection $conn ) {
		parent::__construct($conn);
        $this->conn = $conn;
		$this->stmt = new PDOStatement();
		$this->logger = App::getInstanceOf(Logger::class, null, ["log_type" => "database"]);


        $this->db_type = $this->getType();
        $version_info = $this->getVersion();
        $this->db_version = $version_info['base'];
        if( $this->db_type === DbType::MsSQL && $this->db_version < 2012 ) {
            throw new SystemException(__FILE__, __LINE__ ,"QueryBuilder: does not support MsSQL version " . $this->db_version, "");
        }

        $this->setModificationTimes();
	}

    /**
     * Prepares the given query with the given options
     *
     * @param QueryBuilder $builder
     * @param array $options
     */
    public function useQueryBuilder( QueryBuilder $builder, array $options = [] ): void {
        $this->used_query = "";
        $this->used_params = array();
        $this->stmt = $this->prepare($builder->getQuery(), $options);
        $this->used_query = $builder->getQuery();
    }

	/**
     * Prepares the given query with the given options
     *
	 * @param string $query
	 * @param array $options
	 */
	public function prepareQuery( string $query, array $options = [] ): void {
        $this->used_query = "";
        $this->used_params = array();
		$this->stmt = $this->prepare($query, $options);
        $this->used_query = $query;
	}

    /**
     * Prepares the given query with the given options
     *
     * @param array $options
     *
     * @return PDOConnection
     */
    public function prepareSQL( array $options = [] ): PDOConnection {
        $this->used_query = "";
        $this->used_params = array();
        $this->stmt = $this->prepare($this->sql, $options);
        $this->used_query = $this->sql;
        return $this;
    }

    /**
     * Binds the given value with the given key
     *
     * @param string $key
     * @param mixed $value
     * @param int $type
     *
     * @return PDOConnection
     */
    public function setParam( string $key, mixed $value, int $type = PDO::PARAM_STR ): PDOConnection {
        $this->used_params[$key] = $value;
        if( $type !== PDO::PARAM_STR) {
            $this->stmt->bindValue($key, $value, $type);
        } else {
            $this->stmt->bindValue($key, $value);
        }
        return $this;
    }

    public function fetchMode( int $mode = PDO::PARAM_STR, mixed $type = null ): PDOConnection {
        $this->setFetchMode($mode, $type );
        return $this;
    }

	/**
     * Binds the given value with the given key
     *
	 * @param string $key
	 * @param mixed $value
	 * @param int $type
     *
	 * @return bool
	 */
	public function bindParam( string $key, mixed $value, int $type = PDO::PARAM_STR ): bool {
        $this->used_params[$key] = $value;
        if( $type !== PDO::PARAM_STR) {
		    return $this->stmt->bindValue($key, $value, $type);
        }
        return $this->stmt->bindValue($key, $value);
    }



    /**
     * Returns the query that is sent to the database
     *
     * @return string
     */
    public function getFinalizedQuery() : string {
        $keys = array();
        $values = array_values($this->used_params);

        # build a regular expression for each parameter
        foreach( $this->used_params as $key => $value ) {
            if( is_string($key) ) {
                $keys[] = '/:'.$key.'/';
            } else {
                $keys[] = '/:[?]/';
            }
        }

        array_walk($values, static function( &$v, $k ) {
            if( !is_numeric($v) && $v !== "NULL" ) {
                $v = "'" . $v . "'";
            }
        });

        return preg_replace($keys, $values, $this->used_query, 1);
    }

	/**
     * Sets the Fetch mode for the current query
     *
	 * @param int $mode
	 * @param $class
	 */
	public function setFetchMode( int $mode, $class ): void {
		$this->stmt->setFetchMode($mode, $class);
	}

	/**
     * Returns the number of Entries of the current statement
     *
	 * @return int
	 */
	public function rowCount(): int {
		return $this->stmt->rowCount();
	}

	/**
     * Runs the given query directly to the database and returns the response_types
     *
	 * @param string $query
     *
	 * @return PDOStatement
	 */
	public function run( string $query ): PDOStatement {
		return $this->query($query);
	}

    /**
     * Execute the prepared query and returns a statement
     *
     * @return PDOStatement
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function execute(): PDOStatement {
        App::$analyser->start();
		try {
			$this->stmt->execute();
		} catch( PDOException $e ) {
			$this->logger->log($e->getFile(), $e->getLine(), $e->getMessage()."\n\t=>\t[SQL] ".$this->stmt->queryString, $e->getTrace());
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
        App::$analyser->stop()->add($this->getFinalizedQuery());
		return $this->stmt;
	}

    /**
     * Returns the modification time of a table
     *
     * @param string $table_name
     * @return int
     */
    public function getModificationTimeOfTable( string $table_name ): int {
        if( array_key_exists($table_name, $this->table_infos) ) {
            return $this->table_infos[$table_name]["modified"];
        }
        return 0;
    }

    /**
     * Returns the number or rows in that table
     *
     * @param string $table_name
     * @return int
     */
    public function getNumRowsOfTable( string $table_name ): int {
        if( array_key_exists($table_name, $this->table_infos) ) {
            return $this->table_infos[$table_name]["num_rows"];
        }
        return 0;
    }

    public function getType(): DbType {
        return $this->conn->getType();
    }

    /**
     * Returns an array with versions informations of the current connection
     *
     * @return array
     */
    public function getVersion(): array {
        $type = $this->getType();
        $sql = "";
        switch( $type ) {
            case DbType::Postgres:
            case DbType::MySQL: $sql = "SELECT VERSION() as version"; break;
            case DbType::MsSQL: $sql = "SELECT @@VERSION as version"; break;
        }
        if( $sql === "" ) {
            return [ "base" => 0, "full" => "could not get server version" ];
        }
        $result = $this->run($sql)->fetch();
        switch( $type ) {
            case DbType::Postgres:
                if( preg_match('/\s*PostgreSQL\s*((\d+)[0-9|.]+)\s*/i', $result["version"], $matches) ) {
                    return [ "base" => (int) $matches[1], "full" => $matches[2]];
                }
                break;
            case DbType::MySQL:
                if( preg_match('/^\s*((\d+)[0-9|.]+)\s*/', $result["version"], $matches) ) {
                    return ["base" => (int) $matches[1], "full" => $matches[2]];
                }
                break;
            case DbType::MsSQL:
                if( preg_match('/SQL\s*Server\s*(\d+).*\-\s*([0-9|.]+)/i', $result["version"], $matches) ) {
                    return ["base" => (int) $matches[1], "full" => $matches[2]];
                }
                break;
        }
        return ["base" => 0, "full" => "unknown"];
    }

    /**
     * Returns all table name in the current db
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function getTables(): array {
        if( $this->getType() === DbType::MySQL ) {
            $this->prepareQuery("SELECT table_name FROM information_schema.tables WHERE table_schema=:db");
            $this->bindParam("db", $this->conn->dbname);
            return $this->execute()->fetchAll();
        }
        return [];
    }

    /**
     * Returns the names of the tables unique columns such as primary keys or uniques
     *
     * @param string $table_name
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function getTableKeyColumns( string $table_name ): array {
        if( $this->getType() === DbType::MySQL ) {
            $this->prepareQuery("SELECT column_name FROM information_schema.columns WHERE table_schema=:db AND table_name=:table_name AND column_key IN('PRI','UNI')");
            $this->bindParam("db", $this->conn->dbname);
            $this->bindParam("table_name", $table_name);
            return $this->execute()->fetchAll();
        }
        return [];
    }

    /**
     * Returns the names of the tables columns
     *
     * @param string $table_name
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function getTableColumns( string $table_name ): array {
        if( $this->getType() === DbType::MySQL ) {
            $this->prepareQuery("SELECT column_name FROM information_schema.columns WHERE table_schema=:db AND table_name=:table_name");
            $this->bindParam("db", $this->conn->dbname);
            $this->bindParam("table_name", $table_name);
            return $this->execute()->fetchAll();
        }
        return [];
    }

    /**
     * Gets the last Modification time of each mysql of the current database
     * and stores them into an array
     *
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     * @throws Exception
     */
    private function setModificationTimes(): void {
        if( $this->getType() === DbType::MySQL ) {
            $this->prepareQuery("SELECT table_name, table_rows, create_time, update_time FROM information_schema.tables WHERE table_schema=:db");
            $this->bindParam("db", $this->conn->dbname);
            $results = $this->execute()->fetchAll();
            foreach( $results as $row ) {
                $create_date = new DateTime($row["create_time"] ?? '1970-01-01 00:00:00');
                $update_date = new DateTime($row["update_time"] ?? '1970-01-01 00:00:00');

                $modification_time = ( $update_date > $create_date )
                    ? $update_date->getTimestamp()
                    : $create_date->getTimestamp()
                ;
                $this->table_infos[$row["table_name"]] = array(
                    "num_rows" => $row["table_rows"],
                    "modified" => $modification_time
                );
            }
        }
    }

    /**
     * Checks if the table with the given name exists or not
     *
     * @return bool
     */
    private function schemaIsCompatible(): bool {
        try {
            $this->prepareQuery("SELECT table_schema, table_name, table_rows, create_time, update_time FROM information_schema.tables");
            $results_tables = $this->execute();

            $this->prepareQuery("SELECT column_name, table_name, table_schema FROM information_schema.columns");
            $results_columns = $this->execute();

            return true;
        } catch( Exception ) {
            return false;
        }
    }

}
