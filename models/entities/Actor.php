<?php

namespace models\entities;

use system\Core;
use PDO;
use PDOException;
use RuntimeException;
use system\abstracts\AEntity;
use system\helper\StringHelper;

/**
 * The Actor entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Actor extends AEntity {

	// the columns
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

	/**
	 * The constructor loads the database content into this object.
	 * If id is 0 the entity will be empty
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM actors WHERE id=:id");
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->setFetchMode(PDO::FETCH_INTO, $this);
			$stmt->execute();
			$stmt->fetch();
		}
	}

	/**
	 * @return int
	 * @see \system\interfaces\IEntity
	 */
	public function create(): int {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "INSERT INTO actors (email, password, first_name, last_name, login_fails, login_disabled) VALUES (:email, :password, :first_name, :last_name, :login_fails, :login_disabled)";
			$this->password = StringHelper::getBCrypt($this->password);
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
			$stmt->bindParam(':password', $encrypted_pass, PDO::PARAM_STR);
			$stmt->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
			$stmt->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
			$stmt->bindParam(':login_fails', $this->login_fails, PDO::PARAM_INT);
			$stmt->bindParam(':login_disabled', $this->login_disabled, PDO::PARAM_INT);
			$stmt->execute();
			$insert_id = $pdo->lastInsertId();
			$this->id = $insert_id;
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
		return $insert_id;
	}

	/**
	 * @see \system\interfaces\IEntity
	 */
	public function update(): void {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT password FROM actors WHERE id=:id");
			$stmt->bindParam(":id", $this->id, PDO::PARAM_INT);
			$stmt->execute();
			if( $row = $stmt->fetch() ) {
				if( $this->password !== '' && $row["password"] !== $this->password ) {
					$this->password = StringHelper::getBCrypt($this->password);
				} else {
					$this->password = $row["password"];
				}
				$sql = "UPDATE actors SET email=:email, password=:password, first_name=:first_name, last_name=:last_name, login_fails=:login_fails, login_disabled=:login_disabled WHERE id=:id";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->bindParam(':email', $this->email, PDO::PARAM_STR);
				$stmt->bindParam(':password', $this->password, PDO::PARAM_STR);
				$stmt->bindParam(':first_name', $this->first_name, PDO::PARAM_STR);
				$stmt->bindParam(':last_name', $this->last_name, PDO::PARAM_STR);
				$stmt->bindParam(':login_fails', $this->login_fails, PDO::PARAM_INT);
				$stmt->bindParam(':login_disabled', $this->login_disabled, PDO::PARAM_INT);
				$stmt->execute();
			}
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
	}

	/**
	 * @see \system\interfaces\IEntity
	 * @return bool
	 */
	public function delete(): bool {
		if( $this->id > 0 ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
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