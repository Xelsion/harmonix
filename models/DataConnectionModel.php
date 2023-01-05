<?php

namespace models;

use Exception;
use PDO;

use system\exceptions\SystemException;
use system\helper\SqlHelper;
use system\System;

class DataConnectionModel extends entities\DataConnection {

    public array $columns = array();

    /**
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct(int $id = 0) {
        parent::__construct($id);
        if( $id > 0 ) {
            try {
                $pdo = System::$Core->connection_manager->getConnection("mvc");
                $pdo->prepareQuery("SELECT column_name FROM data_connection_columns WHERE connection_id=:id");
                $pdo->bindParam("id", $id, PDO::PARAM_INT);
                $results = $pdo->execute()->fetchAll();
                foreach($results as $row) {
                    $this->columns[] = $row["column_name"];
                }
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * Returns all actor roles that mach the given conditions,
     * The condition array is build like this:
     * <p>
     * array {
     *    array { col, condition, value },
     *    ...
     * }
     * </p>
     * All conditions are AND related
     *
     * @param array $conditions default array()
     * @param string|null $order default ""
     * @param string|null $direction default ""
     * @param int $limit default 0
     * @param int $page default 1
     *
     * @return array
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : array {
        $results = array();
        $pdo = System::$Core->connection_manager->getConnection("mvc");
        if( !is_null($pdo) ) {
            $params = array();

            $query = "SELECT * FROM data_connections";
            if( !empty($conditions) ) {
                $columns = array();

                foreach( $conditions as $i => $condition ) {
                    $columns[] = $condition[0] . $condition[1] . ":" . $i;
                    $params[$i] = $condition[2];
                }
                $query .= " WHERE " . implode(" AND ", $columns);
            }

            if( $order !== "" ) {
                $query .= " ORDER BY " . $order . " " . $direction;
            }

            if( $limit > 0 ) {
                $offset = $limit * ($page - 1);
                $query .= " LIMIT :limit OFFSET :offset";
                $params["limit"] = $limit;
                $params["offset"] = $offset;
            }

            $pdo->prepareQuery($query);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }
            $pdo->setFetchMode(PDO::FETCH_CLASS, __CLASS__);
            $results = $pdo->execute()->fetchAll();
        }

        return $results;
    }


}