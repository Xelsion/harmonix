<?php

namespace models;

use PDO;

use core\Core;

/**
 * The Actor
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Actor extends entities\Actor {

	// a collection of all permission this user has
	public array $_permissions = array();

	/**
	 * The class constructor
	 * If id is 0 it will return an empty actor
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		parent::__construct($id);
	}

	/**
	 * Returns all actors that mach the given conditions,
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
	public static function find( array $conditions ): ?array {
		if( empty($conditions) ) {
			return static::findAll();
		}

		$columns = array();
		foreach( $conditions as $condition ) {
			$columns[] = $condition[0].$condition[1].":".$condition[0];
		}

		$pdo = Core::$_connection_manager->getConnection("mvc");
		$sql = "SELECT * FROM actors WHERE ".implode(" AND ", $columns);
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
	public static function findAll( int $index = 0, int $limit = 0 ): ?array {
		$pdo = Core::$_connection_manager->getConnection("mvc");
		if( $limit > 0 ) {
			$stmt = $pdo->prepare("SELECT * FROM actors LIMIT :index, :max");
			$stmt->bindParam("index", $index, PDO::PARAM_INT);
			$stmt->bindParam("max", $limit, PDO::PARAM_INT);
		} else {
			$stmt = $pdo->prepare("SELECT * FROM actors");
		}
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	/**
	 * Returns the actor role for the given controller method
	 * If non is set for a specific method it will look for a
	 * controller role and if non is set too, it will look for
	 * the domain role
	 *
	 * @param string $controller
	 * @param string $method
	 * @return ActorRole
	 */
	public function getRole( string $controller, string $method ): ActorRole {
		if( $this->id > 0 ) {
			if( empty($this->_permissions) ) {
				$this->initPermission();
			}

			if( isset($this->_permissions[SUB_DOMAIN][$controller][$method]) ) {
				return $this->_permissions[SUB_DOMAIN][$controller][$method];
			}

			if( isset($this->_permissions[SUB_DOMAIN][$controller][null]) ) {
				return $this->_permissions[SUB_DOMAIN][$controller][null];
			}

			if( isset($this->_permissions[SUB_DOMAIN][null][null]) ) {
				return $this->_permissions[SUB_DOMAIN][null][null];
			}
		}
		$result = ActorRole::find(array(
			array(
				"is_default",
				"=",
				1
			)
		));
        if( count($result) === 1 ) {
            return $result[0];
        }
		return new ActorRole();
	}

	/**
	 * Collects all permission for this user
	 */
	private function initPermission(): void {
		$permissions = ActorPermission::find(array(
			array(
				"actor_id",
				"=",
				$this->id
			)
		));

		foreach( $permissions as $permission ) {
			$this->_permissions[$permission->path][$permission->controller][$permission->method] = $permission->getRole();
		}
	}

	public function toTableRow() {
		return "<div></div>";
	}

}