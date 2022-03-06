<?php

namespace models;

use PDO;

use core\Core;

class Actor extends entities\Actor {

	public static function find( array $conditions ) {
		if( empty($conditions) ) {
			return static::findAll();
		}

		$columns = array();
		foreach( array_keys($conditions) as $col ) {
			$columns[] = $col.":".$col;
		}

		$pdo = Core::$_connection_manager->getConnection("mvc");
		$sql = "SELECT * FROM actors WHERE ".implode(" AND ", $columns);
		$stmt = $pdo->prepare($sql);
		foreach( $conditions as $key => $val ) {
			$stmt = $pdo->bindParam(":".$key, $val, static::getParamType($val));
		}
		$stmt->setFetchMode(PDO::FETCH_OBJ, __CLASS__);
		$stmt->execute();
		return $stmt->fetchAll();
	}

	public static function findAll() {
		$index = 0;
		$limit = 20;
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$stmt = $pdo->prepare("SELECT * FROM actors LIMIT :index, :max");
		$stmt->bindParam("index", $index, PDO::PARAM_INT);
		$stmt->bindParam("max", $limit, PDO::PARAM_INT);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, __CLASS__);
	}

	private static function getParamType( $value ): ?int {
		if( is_null($value) ) {
			return PDO::PARAM_NULL;
		}

		if( preg_match("/^[0-9]+$", $value) ) {
			return PDO::PARAM_INT;
		}
		return PDO::PARAM_STR;
	}

    public function toTableRow() {
        return "<div></div>";
    }

}