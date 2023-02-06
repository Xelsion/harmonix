<?php
namespace lib\helper;

use lib\core\exceptions\SystemException;
use lib\helper\blueprints\ASqlHelper;

class MySqlHelper extends ASqlHelper {

    /**
     * @inheritDoc
     */
    public static function addQueryConditions(string &$query, array $conditions): array {
        $params = array();
        $query .= " WHERE ";
        foreach( $conditions as $index => $condition ) {
            if( count($condition) === 3 ) {
                $query_condition = "AND";
                $column = $condition[0];
                $operator = $condition[1];
                $value = $condition[2];
            } else if( count($condition) === 4 ) {
                $query_condition = ($condition[0] === "AND" || $condition[0] === "OR") ? $condition[0] : "AND";
                $column = $condition[1];
                $operator = $condition[2];
                $value = $condition[3];
            } else {
                throw new SystemException(__FILE__, __LINE__, "Query conditions must have 3 or 4 elements");
            }

            if( $index === 0 ) {
                $query_condition = "";
            }

            if( $operator === "IN" || $operator === "NOT IN" ) {
                if( is_array($value) && count($value) >= 2 ) {
                    $in_values = [];
                    foreach( $value as $v_index => $v ) {
                        $param_name = "in".$index.$v_index;
                        $in_values[] = ":".$param_name;
                        $params[$param_name] = $v;
                    }
                    $condition_str = $column . " ". $operator . "(" . implode(",", $in_values) . ")";
                } else {
                    throw new SystemException(__FILE__, __LINE__, "Query conditions of type IN or NOT IN must have at least 2 values");
                }
            } else if( $operator === "BETWEEN" || $operator === "NOT BETWEEN" ) {
                if( is_array($value) && count($value) === 2 ) {
                    $param_name1 = "between".$index."0";
                    $param_name2 = "between".$index."1";
                    $params[$param_name1] = $value[0];
                    $params[$param_name2] = $value[1];
                    $condition_str = $column . " " . $operator . " :" . $param_name1 . " AND :" . $param_name2;
                } else {
                    throw new SystemException(__FILE__, __LINE__, "Query conditions of type BETWEEN or NOT BETWEEN must have 2 values");
                }
            } else if( $operator === "LIKE" || $operator === "NOT LIKE" ) {
                $condition_str = $column . " " . $operator . " :" . $index;
                $params[$index] = $value;
            } else if( $operator === "IS NULL" || $operator === "IS NOT NULL" ) {
                $condition_str = $column . " " . $operator;
            } else {
                $condition_str = $column . " " . $operator . " :" . $index;
                $params[$index] = $value;
            }

            if( $query_condition !== "" ) {
                $condition_str = " " . $query_condition . " " . $condition_str;
            }

            $query .= $condition_str;
        }
        return $params;
    }

    /**
     * @inheritDoc
     */
    public static function addQueryOrder( string &$query, string $order, ?string $direction = "ASC" ): void {
        $query .= " ORDER BY " . htmlspecialchars($order, ENT_QUOTES);
        if( !is_null($direction) ) {
            $query .= " " . htmlspecialchars($direction, ENT_QUOTES);
        }
    }

    /**
     * @inheritDoc
     */
    public static function addQueryLimit( string &$query, int $limit, int $page ): array {
        $offset = $limit * ($page - 1);
        $query .= " LIMIT :limit OFFSET :offset";
        return ['limit' => $limit, 'offset' => $offset];
    }

}
