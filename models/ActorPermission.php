<?php

namespace models;

use core\Core;
use PDO;

class ActorPermission extends entities\ActorPermission {

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

	public static function findAll() {
		$index = 0;
		$limit = 20;
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$stmt = $pdo->prepare("SELECT * FROM actor_permissions LIMIT :index, :max");
		$stmt->bindParam("index", $index, PDO::PARAM_INT);
		$stmt->bindParam("max", $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

}