<?php

namespace lib\core\database;

use lib\core\blueprints\ADBConnection;
use lib\core\enums\DbType;
use lib\core\exceptions\SystemException;
use PDO;
use PDOStatement;

/**
 * Class QueryBuilder
 * An object-oriented way to build SQL queries.
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class QueryBuilder extends PDO {
    protected PDOStatement $stmt;
    protected string $query_type = "";
    protected string $sql = "";

    /**
     * The class constructor
     *
     * @param ADBConnection $conn
     */
    public function __construct( ADBConnection $conn ) {
        parent::__construct($conn->getConnectionString(), $conn->user, $conn->pass, $conn->getConnectionOptions());
    }

    /**
     * Opens the query with "SELECT *"
     * if $columns are set it will replace the asterix and if $collumns is an array
     * it will be comma-separated
     *
     * @param mixed|null $columns
     * @return $this
     */
    public function Select( mixed $columns = null ): QueryBuilder {
        if( is_null($columns) ) {
            $this->sql = "SELECT *";
        } elseif( is_array($columns) ) {
            $this->sql = "SELECT " . implode(", ", $columns);
        } else {
            $this->sql = "SELECT " . $columns;
        }
        $this->query_type = "select";
        return $this;
    }

    /**
     * Opens the query with "INSERT INTO $table"
     *
     * @param string $table
     * @return $this
     */
    public function Insert( string $table ): QueryBuilder {
        $this->sql = "INSERT INTO " . $table;
        $this->query_type = "insert";
        return $this;
    }

    /**
     * Opens the query with "UPDATE $table"
     *
     * @param string $table
     * @return $this
     */
    public function Update( string $table ): QueryBuilder {
        $this->sql = "UPDATE " . $table;
        $this->query_type = "update";
        return $this;
    }

    /**
     * Opens the query with "DELETE FROM $table"
     *
     * @param string $table
     * @return $this
     */
    public function Delete( string $table ): QueryBuilder {
        $this->sql = "DELETE FROM " . $table;
        $this->query_type = "delete";
        return $this;
    }

    /**
     * Opens the query with "DELETE FROM $table"
     *
     * @param string $table
     * @return $this
     */
    public function Truncate( string $table ): QueryBuilder {
        $this->sql = "TRUNCATE " . $table;
        return $this;
    }

    /**
     * Adds "FROM" at the current point of the query.
     *
     * if $tables is set it will be added to the FROM clause
     * and if it's an array it will be comma-seperated
     *
     * @param mixed $tables optional
     * @return $this
     * @throws SystemException
     */
    public function From( mixed $tables = null ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        if( is_null($tables) ) {
            $this->sql .= " FROM";
        } elseif( is_array($tables) ) {
            $this->sql .= " FROM " . implode(", ", $tables);
        } else {
            $this->sql .= " FROM " . $tables;
        }
        return $this;
    }

    /**
     * Adds "INNER JOIN $table" at the current point of the query.
     *
     * @param string $table
     * @return $this
     * @throws SystemException
     */
    public function Join( string $table ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " INNER JOIN " . $table;
        return $this;
    }

    /**
     * Adds "LEFT JOIN $table" at the current point of the query.
     *
     * @param string $table
     * @return $this
     * @throws SystemException
     */
    public function leftJoin( string $table ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " LEFT JOIN " . $table;
        return $this;
    }

    /**
     * Adds "RIGHT JOIN $table" at the current point of the query.
     *
     * @param string $table
     * @return $this
     * @throws SystemException
     */
    public function rightJoin( string $table ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " RIGHT JOIN " . $table;
        return $this;
    }

    /**
     * Adds a "ON $condition" at the current point of the query.
     * if $condition is an array, it will be comma-separated.
     *
     * @param mixed $conditions
     * @return $this
     * @throws SystemException
     */
    public function On( mixed $conditions ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        if( is_array($conditions) ) {
            $this->sql .= " ON " . implode(", ", $conditions);
        } else {
            $this->sql .= " ON " . $conditions;
        }
        return $this;
    }

    /**
     * Adds "SET $column[0]=$value[0], $column[n]=$value[n]" to the query
     * if no values are given it will use placeholder as values
     * the placeholder name will de the same as the column name with a leading ":"
     * @param array $columns
     * @param array $values optional
     * @return $this
     * @throws SystemException
     */
    public function Set( array $columns, array $values = [] ): QueryBuilder {
        if( $this->query_type !== "update" ) {
            throw new SystemException(__FILE__, __LINE__, "The query type is not update");
        }
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        if( !empty($values) && count($columns) !== count($values) ) {
            throw new \InvalidArgumentException('The number of columns and values must be the same.');
        }
        $this->sql .= " SET";
        $is_first = true;
        if( empty($values) ) {
            foreach( $columns as $col ) {
                $this->sql .= (($is_first)?" ":", ") . $col . "=:" . $col;
                $is_first = false;
            }
        } else {
            $formatted_values = $this->getFormattedValues($values);
            $num_cols = count($columns);
            for( $i = 0; $i < $num_cols; $i++ ) {
                $this->sql .= (($is_first)?" ":", ") . $columns[$i] . "=" . $formatted_values[$i];
                $is_first = false;
            }
        }
        return $this;
    }

    /**
     * Adds "($columns[0], $columns[n]) VALUES ($values[0],$values[n])" to the current point of the query
     * if $values is not set or empty it will use placeholder as values.
     * the placeholder name will de the same as the column name with a leading ":"
     *
     * @param array $columns
     * @param array $values
     * @return $this
     * @throws SystemException
     */
    public function Columns( array $columns, array $values = [] ): QueryBuilder {
        if( $this->query_type !== "insert" ) {
            throw new SystemException(__FILE__, __LINE__, "The query type is not insert");
        }
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        if( !empty($values) && count($columns) !== count($values) ) {
            throw new \InvalidArgumentException('The number of columns and values must be the same.');
        }
        $this->sql .= " (" . implode(", ", $columns) . ") VALUES";
        if( empty($values) ) {
            $this->sql .= " (:" . implode(", :", $columns) . ")";
        } else {
            $formatted_values = $this->getFormattedValues($values);
            $this->sql .= " (" . implode(", ", $formatted_values) . ")";
        }
        return $this;
    }

    /**
     * Adds "($conditions" at the current point of the query
     * can be used to group conditions
     *
     * if $conditions is an array, it will be used as conditions witch will be linked by the given $operator
     * and the group will be closed automatically.
     * if not, you may use the "Add()" or "Or()" functions to add more conditions, but you need to close the group manually
     * by using the "closeGroup()" function
     *
     * @param mixed $conditions
     * @param string $operator default "OR"
     * @return $this
     * @throws SystemException
     */
    public function openGroup( mixed $conditions, string $operator = "OR" ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " (";
        if( is_array($conditions) ) {
            $this->sql .= implode(" " . $operator . " ", $conditions) . ")";
        } else {
            $this->sql .= $conditions;
        }
        return $this;
    }

    /**
     * Adds ")" at the current point of the query
     * this will close a previously opened grouping
     *
     * @return $this
     * @throws SystemException
     */
    public function closeGroup(): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= ")";
        return $this;
    }

    /**
     * Adds "WHERE $condition" at the current point of the query
     *
     * @param string $condition
     * @return $this
     * @throws SystemException
     */
    public function Where( string $condition ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " WHERE " . $condition;
        return $this;
    }

    /**
     * Adds "AND $condition" at the current point of the query
     *
     * @param string $condition optional
     * @return $this
     * @throws SystemException
     */
    public function And( string $condition = "" ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql.= " AND";
        if( $condition !== "" ) {
            $this->sql .= " " . $condition;
        }
        return $this;
    }

    /**
     * Adds "OR $condition" at the current point of the query
     *
     * @param string $condition optional
     * @return $this
     * @throws SystemException
     */
    public function Or( string $condition = "" ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " OR";
        if( $condition !== "" ) {
            $this->sql .= " " . $condition;
        }

        return $this;
    }

    /**
     * Adds "LIKE $condition" at the current point of the query
     *
     * @param string $condition
     * @return $this
     * @throws SystemException
     */
    public function Like( string $condition ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " LIKE " . $condition;
        return $this;
    }

    /**
     * Adds "NOT LIKE $condition" at the current point of the query
     *
     * @param string $condition
     * @return $this
     * @throws SystemException
     */
    public function notLike( string $condition ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " NOT LIKE " . $condition;
        return $this;
    }

    /**
     * Adds "IN($values)" at the current point of the query
     * $values will be comma-separated
     *
     * @param array $values
     * @return $this
     * @throws SystemException
     */
    public function In( array $values ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " IN(" . implode(", ", $values) . ")";
        return $this;
    }

    /**
     * Adds "NOT IN($values)" at the current point of the query
     * $values will be comma-separated
     *
     * @param array $values
     * @return $this
     * @throws SystemException
     */
    public function notIn( array $values ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " NOT IN(" . implode(", ", $values ) . ")";
        return $this;
    }

    /**
     * Adds "BETWEEN $condition1 AND $condition2" at the current point of the query
     *
     * @param mixed $condition1
     * @param mixed $condition2
     * @return $this
     * @throws SystemException
     */
    public function Between( mixed $condition1, mixed $condition2 ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " BETWEEN " . $condition1 . " AND " . $condition2;
        return $this;
    }

    /**
     * Adds "NOT BETWEEN $condition1 AND $condition2" at the current point of the query
     *
     * @param mixed $condition1
     * @param mixed $condition2
     * @return $this
     * @throws SystemException
     */
    public function notBetween( mixed $condition1, mixed $condition2 ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " NOT BETWEEN " . $condition1 . " AND " . $condition2;
        return $this;
    }

    /**
     * Adds "IS NULL" at the current point of the query
     *
     * @return QueryBuilder
     * @throws SystemException
     */
    public function isNull(): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " IS NULL";
        return $this;
    }

    /**
     * Adds "IS NOT NULL" at the current point of the query
     *
     * @return QueryBuilder
     * @throws SystemException
     */
    public function isNotNull(): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " IS NOT NULL";
        return $this;
    }

    /**
     * Adds a sub-query in braces at the current point of the query
     *
     * @param QueryBuilder $builder
     * @param string $alias optional
     * @param string $condition optional
     * @return $this
     * @throws SystemException
     */
    public function SubQuery( QueryBuilder $builder, string $alias = "", string $condition = "" ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " (" . $builder->sql . ")";
        if( $alias !== "" ) {
            $this->sql .= " AS " . $alias;
        }
        if( $condition !== "" ) {
            $this->sql .= " " . $condition;
        }
        return $this;
    }

    /**
     * Adds "AS $alias" at the current point of the query
     *
     * @param string $alias
     * @return $this
     * @throws SystemException
     */
    public function As( string $alias ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql .= " AS " . $alias;
        return $this;
    }

    /**
     * Adds "ORDER BY $order" at the current point of the query
     * if $column is an array, it will be used the keys as columns and its values as order
     *
     * @param mixed $columns
     * @param string $order default 'ASC'
     * @return $this
     * @throws SystemException
     */
    public function OrderBy( mixed $columns, string $order = "ASC" ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql.= " ORDER BY";
        if( is_array($columns) ) {
            $is_first = true;
            foreach($columns as $col => $direction) {
                $this->sql.= (($is_first) ? " " : ", ") . $col . " " . $direction;
                $is_first = false;
            }
        } else {
            $this->sql.= " " . $columns . " " . $order;
        }
        return $this;
    }

    /**
     * Adds a limitation to the number of results in the db specific syntax
     *
     * @param int $limit
     * @param int $offset
     * @return $this
     * @throws SystemException
     */
    public function Limit( int $limit, int $offset = 0 ): void {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        switch( $this->db_type ) {
            case DbType::MySQL:
                $this->sql .= " LIMIT " . $limit . " OFFSET " . $offset;
                break;
            case DbType::MsSQL:
                $this->sql .= " OFFSET " . $offset . " ROWS FETCH NEXT " . $limit . " ROWS ONLY";
                break;
            case DbType::Postgres:
                $this->sql .= " OFFSET " . $offset . " LIMIT " . $limit;
                break;
        }
    }

    /**
     * Adds "GROUP BY $columns" at the current point of the query
     * if $columns is an array, it will be comma-separated
     *
     * @param array $columns
     * @return $this
     * @throws SystemException
     */
    public function GroupBy( mixed $columns ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        if( is_array($columns) ) {
            $this->sql.= " GROUP BY " . implode(", ", $columns);
        } else {
            $this->sql.= " GROUP BY " . $columns;
        }

        return $this;
    }

    /**
     * Adds "HAVING $condition" at the current point of the query
     *
     * @param string $condition
     * @return $this
     * @throws SystemException
     */
    public function Having( string $condition ): QueryBuilder {
        if( $this->sql === "" ) {
            throw new SystemException(__FILE__, __LINE__, "Error in query Syntax");
        }
        $this->sql.= " HAVING " . $condition;
        return $this;
    }

    /**
     * Returns a formatted value.
     * strings will be embedded in '
     *
     * @param mixed $value
     * @return string|int
     */
    private function getFormattedValue( mixed $value ): string|int {
        return ( is_int($value) ) ? $value : "'" . $value . "'";
    }

    /**
     * Return the current sql string
     *
     * @return string
     */
    public function getQuery(): string {
        return $this->sql;
    }

    /**
     * Formats all values in teh given array
     *
     * @param array $values
     * @return array
     */
    private function getFormattedValues( array $values ): array {
        $formatted_values = [];
        foreach($values as $value) {
            $formatted_values[] = ( is_int($value) ) ? $value : "'" . $value . "'";
        }
        return $formatted_values;
    }

}
