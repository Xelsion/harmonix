<?php

namespace system\helper;

use PDO;
use system\Core;

class SqlHelper {

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



    public static function findAllIn( string $db, string $table,?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1) {
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

            $pdo->prepare($sql);
            foreach( $params as $key => $value ) {
                $pdo->bindParam(":" . $key, $value, static::getParamType($value));
            }
        }
        return $pdo;
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

}