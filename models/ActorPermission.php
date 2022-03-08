<?php

namespace models;

use core\Core;
use PDO;

/**
 * The Actor Permissions
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorPermission extends entities\ActorPermission {

	private ?ActorRole $_role = null;

	/**
	 * The class constructor
	 * If id is 0 it will return an empty actor
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		parent::__construct($id);
		if( $this->role_id > 0 ) {
			$this->_role = new ActorRole($this->role_id);
		}
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
	 * @return array|false|null
	 */
	public static function find( array $conditions ) {
		if( empty($conditions) ) {
			return static::findAll();
		}

		$columns = array();
		foreach( $conditions as $condition ) {
			$columns[] = $condition[0].$condition[1].":".$condition[0];
		}

		$pdo = Core::$_connection_manager->getConnection("mvc");
		$sql = "SELECT * FROM actor_permissions WHERE ".implode(" AND ", $columns);
		$stmt = $pdo->prepare($sql);
		foreach( $conditions as $condition ) {
			$stmt->bindParam(":".$condition[0], $condition[2], static::getParamType($condition[2]));
		}
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * Returns all actors
	 * If limit is greater than 0 the query will return
	 * that many results starting at index.
	 * Returns false if an error occurs
	 *
	 * @param int $index
	 * @param int $limit
	 * @return array|false
	 */
	public static function findAll( int $index = 0, int $limit = 0 ) {
		$pdo = Core::$_connection_manager->getConnection("mvc");
		if( $limit > 0 ) {
			$stmt = $pdo->prepare("SELECT * FROM actor_permissions LIMIT :index, :max");
			$stmt->bindParam("index", $index, PDO::PARAM_INT);
			$stmt->bindParam("max", $limit, PDO::PARAM_INT);
		} else {
			$stmt = $pdo->prepare("SELECT * FROM actor_permissions");
		}
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * Returns the role of this permission
	 *
	 * @return ActorRole|null
	 */
	public function getRole(): ?ActorRole {
		return $this->_role;
	}
}