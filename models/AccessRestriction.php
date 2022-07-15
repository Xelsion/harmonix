<?php

namespace models;

use JsonException;
use system\classes\CacheFile;
use system\classes\QueryBuilder;
use system\Core;
use system\exceptions\SystemException;

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
        $queryBuilder = new QueryBuilder("mvc");
        $queryBuilder->setTable("access_restrictions");
        if( !empty($conditions) ) {
            $queryBuilder->setConditions( $conditions );
        }
        if( !is_null($order) && $order !== "" ) {
            $queryBuilder->setOrder($order, $direction);
        }
        if( $limit > 0 ) {
            $queryBuilder->setLimit($limit, $page);
        }
        $queryBuilder->setFetchClass(__CLASS__);

        if( self::isCacheable() ) {
            $cache = new CacheFile(md5($queryBuilder->getCacheName()));
            $last_modify = $queryBuilder->getLastModificationDate();
            if( $cache->isUpToDate($last_modify) ) {
                $results = unserialize($cache->loadFromCache(), array(false));
            } else {
                $results = $queryBuilder->getResults();
                $cache->saveToCache(serialize($results));
            }
        } else {
            $results = $queryBuilder->getResults();
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