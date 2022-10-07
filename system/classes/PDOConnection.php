<?php

namespace system\classes;

use PDO;
use PDOException;
use PDOStatement;
use JsonException;
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

    private string $used_query = "";
    private array $used_params = array();

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
}
