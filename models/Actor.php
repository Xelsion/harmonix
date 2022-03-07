<?php

namespace models;

use PDO;

use core\Core;

class Actor extends entities\Actor {

	public array $_permissions = array();

	public function __construct( int $id = 0 ) {
		parent::__construct($id);
	}

	/**
	 * Returns all actors that mach the conditions
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
	 *
	 * @return array|false
	 */
	public static function findAll(): ?array {
		$index = 0;
		$limit = 20;
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$stmt = $pdo->prepare("SELECT * FROM actors LIMIT :index, :max");
		$stmt->bindParam("index", $index, PDO::PARAM_INT);
		$stmt->bindParam("max", $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

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
		return $result[0];
	}

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