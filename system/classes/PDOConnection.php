<?php

namespace system\classes;

use PDO;
use Exception;
use PDOException;
use PDOStatement;
use JsonException;
use DateTime;

use system\abstracts\ADBConnection;
use system\exceptions\SystemException;

/**
 * The PDOConnection
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class PDOConnection extends PDO {

	private ?Logger $logger;
	private PDOStatement $stmt;
    private ADBConnection $conn;

    private array $table_infos = array();
    private string $used_query = "";
    private array $used_params = array();

    /**
     * @param ADBConnection $conn
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function __construct( ADBConnection $conn ) {
		parent::__construct($conn->getConnectionString(), $conn->user, $conn->pass, $conn->getConnectionOptions());
        $this->conn = $conn;
		$this->stmt = new PDOStatement();
		$this->logger = new Logger("database");
        $this->setModificationTimes();
	}

	/**
	 * @param string $query
	 * @param array $options
	 */
	public function prepare( string $query, array $options = [] ): void {
        $this->used_query = "";
        $this->used_params = array();
		$this->stmt = parent::prepare($query, $options);
        $this->used_query = $query;
	}

	/**
	 * @param string $key
	 * @param $value
	 * @param int $type
	 * @return bool
	 */
	public function bindParam( string $key, $value, int $type = PDO::PARAM_STR ): bool {
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
                $keys[] = '/'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }
        }

        array_walk($values, static function( &$v, $k ) {
            if( !is_numeric($v) && $v !== "NULL" ) {
                $v = "\'" . $v . "\'";
            }
        });

        return preg_replace($keys, $values, $this->used_query, 1);
    }

	/**
	 * @param int $mode
	 * @param $class
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
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function execute(): PDOStatement {
		try {
			$this->stmt->execute();
		} catch( PDOException $e ) {
			$this->logger->log($e->getFile(), $e->getLine(), $e->getMessage()."\n\t=>\t[SQL] ".$this->stmt->queryString, $e->getTrace());
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
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

    /**
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     * @throws Exception
     */
    private function setModificationTimes(): void {
        $this->prepare("SELECT table_name, table_rows, create_time, update_time FROM information_schema.tables WHERE table_schema=:db");
        $this->bindParam("db", $this->conn->dbname);
        $results = $this->execute()->fetchAll();
        foreach( $results as $row ) {
            $create_time = $row["create_time"];
            $update_time = ( !is_null($row["update_time"]) )
                ? $row["update_time"]
                : "1970-01-01 00:00:00";
            $create_date = new DateTime($create_time);
            $update_date = new DateTime($update_time);

            $modification_time = ( $update_date > $create_date )
                ? $update_date->getTimestamp()
                : $create_date->getTimestamp();
            $this->table_infos[$row["table_name"]] = array(
                "num_rows" => $row["table_rows"],
                "modified" => $modification_time
            );
        }
    }
}
