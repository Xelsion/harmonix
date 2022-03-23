<?php

namespace models\entities;

use system\Core;
use PDO;
use PDOException;
use RuntimeException;

use system\abstracts\AEntity;
use system\System;

/**
 * The Session entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session extends AEntity {

	// The columns
	public string $id = "";
	public int $actor_id = 0;
	public string $expired = "";

	/**
	 * The constructor loads the database content into this object.
	 * If a session was set it will load it else it will return an
	 * empty entity
	 */
	public function __construct() {
		if( isset($_COOKIE["session"]) ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM sessions WHERE id=:id");
			$stmt->bindParam(":id", $_COOKIE["session"], PDO::PARAM_STR);
			$stmt->setFetchMode(PDO::FETCH_INTO, $this);
			$stmt->execute();
			$stmt->fetch();
		}
	}

	/**
	 * @return string
	 * @see \system\interfaces\IEntity
	 */
	public function create(): void {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "INSERT INTO sessions (id, actor_id, expired) VALUES (:id, :actor_id, :expired)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
			$stmt->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
			$stmt->bindParam(':expired', $this->expired, PDO::PARAM_STR);
			$stmt->execute();
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * @see \system\interfaces\IEntity
	 */
	public function update(): void {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "UPDATE sessions SET actor_id=:actor_id, expired=:expired WHERE id=:id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
			$stmt->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
			$stmt->bindParam(':expired', $this->expired, PDO::PARAM_STR);
			$stmt->execute();
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * @see \system\interfaces\IEntity
	 * @return bool
	 */
	public function delete(): bool {
		if( $this->id !== "" ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$stmt = $pdo->prepare("DELETE FROM sessions WHERE id=:id");
				$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
				$stmt->execute();
				return true;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
		return false;
	}
}