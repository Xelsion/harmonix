<?php

namespace models\entities;

use PDO;
use PDOException;
use RuntimeException;

use core\abstracts\AEntity;
use core\System;

class Session extends AEntity {

	public string $id = "";
	public int $actor_id = 0;
	public string $expired = "";

	public function __construct() {
		if( isset($_COOKIE["session"]) ) {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM sessions WHERE id=:id");
			$stmt->bindParam(":id", $_COOKIE["session"], PDO::PARAM_STR);
			$stmt->setFetchMode(PDO::FETCH_INTO, $this);
			$stmt->execute();
			$stmt->fetch();
		}
	}

	/**
	 * @return string
	 * @see \core\interfaces\IEntity
	 */
	public function create(): string {
		$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
		try {
			$sql = "INSERT INTO sessions (id, actor_id, expired) VALUES (:id, :actor_id, :expired)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $this->id, PDO::PARAM_STR);
			$stmt->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
			$stmt->bindParam(':expired', $this->expired, PDO::PARAM_STR);
			$stmt->execute();
			return $this->id;
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * @see \core\interfaces\IEntity
	 */
	public function update(): void {
		$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
		try {
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
	 * @return bool
	 * @see \core\interfaces\IEntity
	 */
	public function delete(): bool {
		if( $this->id !== "" ) {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
			try {
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