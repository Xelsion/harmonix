<?php

namespace models;

use PDO;
use JsonException;

use system\Core;
use system\helper\SqlHelper;
use system\exceptions\SystemException;

class AccessRestrictionTypeModel extends entities\AccessRestrictionType {

    /**
     * The class constructor
     * If id is 0 it will return an empty actor
     *
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        parent::__construct($id);
    }

    /**
     * Returns all actors permissions that mach the given conditions,
     * The condition array is build like this:
     * <p>
     * array {
     *    array { col, condition, value },
     *    ...
     * }
     * </p>
     * All conditions are AND related
     *
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     *
     * @return array|null
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : ?array {
        $results = array();
        $pdo = Core::$_connection_manager->getConnection("mvc");
        if( !is_null($pdo) ) {
            $params = array();

            $query = "SELECT * FROM access_restriction_types";
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

            $pdo->prepare($query);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }
            $pdo->setFetchMode( PDO::FETCH_CLASS, __CLASS__);
            $results = $pdo->execute()->fetchAll();
        }

        return $results;
    }

}
