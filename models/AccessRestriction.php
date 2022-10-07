<?php

namespace models;

use JsonException;
use system\classes\CacheFile;
use system\classes\PDOCache;
use system\classes\QueryBuilder;
use system\Core;
use system\exceptions\SystemException;
use system\helper\SqlHelper;

class AccessRestriction extends entities\AccessRestriction {

    /**
     * The class constructor
     * If id is 0 it will return empty access restriction
     *
     */
    public function __construct() {
        parent::__construct();
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
     * @return array|false|null
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function find( array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : ?array {
        $results = array();
        $db = Core::$_connection_manager->getConnection("mvc");
        if( !is_null($db) ) {
            $params = array();

            $query = "SELECT * FROM access_restrictions";
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

            $db->prepare($query);
            foreach( $params as $key => $value ) {
                $db->bindParam(":" . $key, $value, SqlHelper::getParamType($value));
            }

            $pdo_cache = new PDOCache($db);
            $pdo_cache->checkTable("mvc", "access_restrictions");
            $results = $pdo_cache->getResults(__CLASS__);

        }

        return $results;
    }

    /**
     * @return void
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function deleteAll() : void {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $sql = "TRUNCATE access_restrictions";
        $pdo->prepare($sql);
        $pdo->execute();
    }
}
