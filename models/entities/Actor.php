<?php

namespace models\entities;

use PDO;
use PDOException;
use RuntimeException;
use core\abstracts\AEntity;
use core\helper\StringHelper;
use core\System;

class Actor extends AEntity {

	public int $id = 0;
	public string $email = "";
	public string $password = "";
	public string $first_name = "";
	public string $last_name = "";
	public int $login_fails = 0;
	public bool $login_disabled = false;
	public string $created;
	public ?string $updated;
	public ?string $deleted;

	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM actors WHERE id=:id");
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->setFetchMode(PDO::FETCH_INTO, $this);
			$stmt->execute();
			$stmt->fetch();
		}
	}

	/**
	 * @return int
	 *
	 * @see \core\interfaces\IEntity
	 */
	public function create(): int {
		$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
		try {
			$sql = "INSERT INTO actors (email, password, first_name, last_name) VALUES (:email, :password, :first_name, :last_name)";
			$this->password = StringHelper::getBCrypt($this->password);
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindParam(':password', StringHelper::getBCrypt($this->password), PDO::PARAM_STR);
			$stmt->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
			$stmt->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
			$stmt->execute();
			$insert_id = $pdo->lastInsertId();
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
		return $insert_id;
	}

	/**
	 * @see \core\interfaces\IEntity
	 */
	public function update(): void {
		$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
		try {
			$sql = "UPDATE actors SET email=:email, password=:password, first_name=:first_name, last_name=:last_name WHERE id=:id";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
			$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
			$stmt->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
			$stmt->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
			$stmt->execute();
			$pdo->commit();
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * @return bool
	 * @see \core\interfaces\IEntity
	 */
	public function delete(): bool {
		if( $this->id > 0 ) {
			$pdo = System::getInstance()->getConnectionManager()->getConnection("mvc");
			try {
				$stmt = $pdo->prepare("DELETE FROM actors WHERE id=:id");
				$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
				$stmt->execute();
				$pdo->commit();
				return true;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
		return false;
	}
}