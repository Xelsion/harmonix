<?php

namespace system\helper;

use DateTime;
use Exception;
use JsonException;
use models\Actor;
use PDO;
use system\classes\Cache;
use system\classes\PDOConnection;
use system\Core;
use system\exceptions\SystemException;

class SqlHelper {

    /**
     * @param string $db
     * @param string $table
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return mixed|PDO|PDOConnection
     */
    public static function findIn( string $db, string $table, array $conditions, ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) {
        if( empty($conditions) ) {
            return static::findAllIn($db,$table, $order, $direction, $limit, $page);
        }

        $columns = array();
        $params = array();
        foreach( $conditions as $i => $condition ) {
            $columns[] = $condition[0].$condition[1].":".$i;
            $params[$i] = $condition[2];
        }

        $pdo = Core::$_connection_manager->getConnection($db);
        if( $pdo ) {
            $sql = "SELECT * FROM ".$table." WHERE ".implode(" AND ", $columns);

            if( !is_null($order) && $order !== "" ) {
                $sql .= " ORDER BY ".$order." ". $direction;
            }

            if( $limit > 0 ) {
                $offset = ( $page - 1 ) * $limit;
                $sql .= " LIMIT :limit OFFSET :offset";
                $params["limit"] = $limit;
                $params["offset"] = $offset;
            }

            $pdo->prepare($sql);

            foreach( $params as $key => $value ) {
                $pdo->bindParam(":".$key, $value, static::getParamType($value));
            }
        }
        return $pdo;
    }


    /**
     * @param string $db
     * @param string $table
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return mixed|PDO|PDOConnection
     */
    public static function findAllIn( string $db, string $table, ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1) {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        if( $pdo ) {
            $sql = "SELECT * FROM " . $table;

            if( !is_null($order) && $order !== "" ) {
                $sql .= " ORDER BY " . $order . " " . $direction;
            }
            $params = array();
            if( $limit > 0 ) {
                $offset = ( $page - 1 ) * $limit;
                $sql .= " LIMIT :limit OFFSET :offset";
                $params["limit"] = $limit;
                $params["offset"] = $offset;
            }
            print_debug($sql);
            $pdo->prepare($sql);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, static::getParamType($value));
            }
        }
        return $pdo;
    }

    /**
     * @param string $db
     * @param string $table
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     *
     * @return mixed|PDO|PDOConnection
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function findInCached( string $db, string $table, array $conditions, ?string $class = "", ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1 ) {
        $cache_name = self::getCacheName($db, $table, $order, $direction, $limit, $page, $conditions, $class);
        $last_modify = self::getLastModificationDate($table);
        $cache = new Cache($cache_name);
        if( $cache->isUpToDate($last_modify) ) {
            $results = unserialize($cache->loadFromCache(), array(false));
        } else {
            if( !is_null($class) && $class !== "" ) {
                $results = self::findAllIn($db, $table, $order, $direction, $limit, $page)->execute()->fetchAll( PDO::FETCH_CLASS, $class);
            } else {
                $results = self::findAllIn($db, $table, $order, $direction, $limit, $page)->execute()->fetchAll();
            }
            $cache->saveToCache(serialize($results));
        }
        return $results;
    }

    /**
     * @param string $db
     * @param string $table
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return mixed|PDO|PDOConnection
     *
     * @throws JsonException
     * @throws SystemException
     */
    public static function findAllInCached( string $db, string $table, ?string $class = "", ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1) {
        $cache_name = self::getCacheName($db, $table, $order, $direction, $limit, $page, $class);
        $last_modify = self::getLastModificationDate($table);
        $cache = new Cache($cache_name);
        if( $cache->isUpToDate($last_modify) ) {
            print_debug("load from cache");
            $results = unserialize($cache->loadFromCache(), array(false));
        } else {
            if( !is_null($class) && $class !== '' ) {
                $results = self::findAllIn($db, $table, $order, $direction, $limit, $page)->execute()->fetchAll(PDO::FETCH_CLASS, $class);
            } else {
                $results = self::findAllIn($db, $table, $order, $direction, $limit, $page)->execute()->fetchAll();
            }
            print_debug("write to cache");
            $cache->saveToCache(serialize($results));
        }
        return $results;
    }

    /**
     * Return the timestamp of the latest possible modification date in this table
     *
     * @param string $table
     * @return int
     *
     * @throws JsonException
     * @throws SystemException
     * @throws Exception
     */
    public static function getLastModificationDate( string $table ) : int {
        $created = 0;
        $updated = 0;
        $deleted = 0;
        $modified = 0;
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $pdo->prepare("SELECT max(created) as created, max(updated) as updated, max(deleted) as deleted FROM ". $table ." LIMIT 1");
        $row = $pdo->execute()->fetch();
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
     * Returns the PDO::PARAM type of the given value
     *
     * @param $value
     * @return int
     */
    protected static function getParamType( $value ): int {
        if( is_null($value) ) {
            return PDO::PARAM_NULL;
        }

        if( is_bool($value) ) {
            return PDO::PARAM_BOOL;
        }

        if( preg_match("/^[0-9]+$/", $value) ) {
            return PDO::PARAM_INT;
        }

        return PDO::PARAM_STR;
    }


    /**
     * @param ...$params
     * @return string
     *
     * @throws JsonException
     */
    private static function getCacheName( ...$params ) : string {
        $name_parts = array();
        foreach( $params as $param ) {
            if( is_array($param) ) {
                $name_parts[] = md5(json_encode($param, JSON_THROW_ON_ERROR));
            } else {
                $name_parts[] = $param;
            }
        }
        return implode("_", $name_parts);
    }
}