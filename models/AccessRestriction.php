<?php

namespace models;

use PDO;
use system\Core;
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
     */
    public static function find( array $conditions, ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) : ?array {
        $pdo = SqlHelper::findIn("mvc", "access_restrictions", $conditions, $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    /**
     * Returns all actors
     * If limit is greater than 0 the query will return
     * that many results starting at index.
     * Returns false if an error occurs
     *
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array|false
     */
    public static function findAll( ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ): ?array {
        $pdo = SqlHelper::findAllIn("mvc", "access_restrictions", $order, $direction, $limit, $page);
        return $pdo->execute()->fetchAll(PDO::FETCH_CLASS, __CLASS__);
    }

    public static function deleteAll() {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $sql = "DELETE FROM access_restrictions";
        $pdo->prepare($sql);
        $pdo->execute();
    }
}