<?php

namespace lib\helper\blueprints;

use lib\core\exceptions\SystemException;
use PDO;

abstract class ASqlHelper {

	/**
	 * Adds the given conditions to the query in a WHERE clause in the right sql syntax.
	 * Returns all used parameters in an array wich keys matches these used in the sql query
	 *
	 * Conditions are arrays in an array.
	 * A condition array can contain [connection, column, operator, value] or just [connection, operator, value]
	 * if no connection is that its assumed it's an AND connection.
	 * Operators can be =, < >, <= >=,!=, LIKE, NOT LIKE, IN, NOT IN, BETWEEN, NOT BETWEEN, IS NULL or IS NOT NULL.
	 * The value may be a number or a string or an array with values if more is used by the operator like BETWEEN of IN.
	 *
	 * @param string $query
	 * @param array $conditions
	 * @return array
	 * @throws SystemException
	 */
	abstract public static function addQueryConditions(string &$query, array $conditions): array;

	/**
	 * Adds the given order to the query in a ORDER clause in the right sql syntax.
	 *
	 * @param string $query
	 * @param string $order
	 * @param ?string $direction
	 * @return void
	 */
	abstract public static function addQueryOrder(string &$query, string $order, ?string $direction = "ASC"): void;

	/**
	 * Adds the given limitation to the query in the right sql syntax.
	 * Returns all used parameters in an array wich keys matches these used in the sql query
	 *
	 * @param string $query
	 * @param int $limit
	 * @param int $page
	 * @return array
	 */
	abstract public static function addQueryLimit(string &$query, int $limit, int $page): array;

	/**
	 * Returns the PDO::PARAM type of the given value
	 *
	 * @param mixed $value
	 *
	 * @return int
	 */
	public static function getParamType(mixed $value): int {
		if( is_null($value) ) {
			return PDO::PARAM_NULL;
		}

		if( is_bool($value) ) {
			return PDO::PARAM_BOOL;
		}

		if( preg_match("/^\d+$/", $value) ) {
			return PDO::PARAM_INT;
		}

		return PDO::PARAM_STR;
	}

}