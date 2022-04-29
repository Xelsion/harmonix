<?php

namespace system\classes;

use DateTime;
use Exception;
use JsonException;
use PDO;
use system\Core;
use system\exceptions\SystemException;
use system\helper\SqlHelper;

class QueryBuilder {

    private string $_sql = "";

    protected ?PDOConnection $_conn = null;
    protected string $_table = "";
    protected string $_class = "";
    protected array $_conditions = array();
    protected array $_order = array();
    protected array $_limit = array();

    public function __construct( string $db ) {
        $this->_conn = Core::$_connection_manager->getConnection($db);
    }

    public function setTable( string $table ): void  {
        $this->_table = $table;
    }

    public function setConditions( array $conditions ) : void {
        $this->_conditions = $conditions;
    }

    public function addCondition( $col, $operator, $value ) : void {
        $this->_conditions[] = array($col, $operator, $value);
    }

    public function setLimit( int $limit, int $page = 1 ) : void {
        $this->_order["limit"] = $limit;
        $this->_order["offset"] = ( $page - 1 ) * $limit;
    }

    public function setOrder( string $col, string $direction = "asc" ) : void {
        $this->_order["col"] = $col;
        $this->_order["dir"]  = ( $direction === "asc" || $direction === "desc" ) ? $direction : "asc";
    }

    public function setFetchClass( string $class ) : void {
        $this->_class = $class;
    }

    public function getSql() : string {
        return $this->_sql;
    }

    /**
     * Builds the SQL Query and sends it to the database.
     * Returns the results of the query in an array
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public function getResults() : array {
        $columns = array();
        $params = array();
        foreach( $this->_conditions as $i => $condition ) {
            $columns[] = $condition[0].$condition[1].":".$i;
            $params[$i] = $condition[2];
        }

        $this->_sql = "SELECT * FROM " . $this->_table;
        if( !empty($this->_conditions) ) {
            $this->_sql .= " WHERE ".implode(" AND ", $columns);
        }
        if( !empty($this->_order) ) {
            $this->_sql .= " ORDER BY " . $this->_order["col"] . " " . $this->_order["dir"];
        }
        if( !empty($this->_limit) ) {
            $this->_sql .= " LIMIT :limit OFFSET :offset";
            $params["limit"] = $this->_limit["limit"];
            $params["offset"] = $this->_limit["offset"];
        }

        $this->_conn->prepare($this->_sql);
        foreach( $params as $key => $value ) {
            $this->_conn->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
        }
        if( $this->_class !== "" ) {

            $results = $this->_conn->execute()->fetchAll( PDO::FETCH_CLASS, $this->_class);
        } else {
            $results = $this->_conn->execute()->fetchAll();
        }

        return $results;
    }

    /**
     * Gets the last timestamp of the last modified row in the curren table.
     * Needs created, updated and deleted as columns in the table
     *
     * @return int
     *
     * @throws JsonException
     * @throws SystemException
     * @throws Exception
     */
    public function getLastModificationDate() : int {
        $created = 0;
        $updated = 0;
        $deleted = 0;
        $modified = 0;
        $this->_conn->prepare("SELECT max(created) as created, max(updated) as updated, max(deleted) as deleted FROM ". $this->_table ." LIMIT 1");
        $row = $this->_conn->execute()->fetch();
        if( $row ) {
            $created = new DateTime($row["created"]);
            $created = $created->getTimestamp();
            if( $row["updated"] !== NULL ) {
                $updated = new DateTime($row["updated"]);
                $updated = $updated->getTimestamp();
            }
            if( $row["deleted"] !== NULL ) {
                $deleted = new DateTime($row["deleted"]);
                $deleted = $deleted->getTimestamp();
            }
            $modified = ( $updated >= $deleted ) ? $updated : $deleted;
        }
        return ( $created >= $modified ) ? $created : $modified;
    }

    /**
     * Builds a string with the current information of the query and returns it
     *
     * @return string
     *
     * @throws JsonException
     */
    public function getCacheName() : string {
        $name_parts = array( $this->_table, $this->_class);
        if( !empty($this->_conditions) ) {
            $name_parts[] = md5(json_encode($this->_conditions, JSON_THROW_ON_ERROR));
        }

        if( !empty($this->_order) ) {
            $name_parts[] = $this->_order["col"];
            $name_parts[] = $this->_order["dir"];
        }

        if( !empty($this->_limit) ) {
            $name_parts[] = $this->_limit["limit"];
            $name_parts[] = $this->_limit["offset"];
        }
        return implode("_", $name_parts);
    }
}