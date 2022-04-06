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
			$pdo->prepare("SELECT * FROM actors WHERE id=:id");
			$pdo->bindParam(":id", $id, PDO::PARAM_INT);
			$pdo->setFetchMode(PDO::FETCH_INTO, $this);
			$pdo->execute()->fetch();
		}
	}

	/**
	 *
	 * @see \system\interfaces\IEntity
	 */
	public function create(): void {
		$pdo = Core::$_connection_manager->getConnection("mvc");
		$sql = "INSERT INTO actors (email, password, first_name, last_name, login_fails, login_disabled) VALUES (:email, :password, :first_name, :last_name, :login_fails, :login_disabled)";
		$this->password = StringHelper::getBCrypt($this->password);
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':email', $this->email);
		$stmt->bindParam(':password', $encrypted_pass);
		$stmt->bindParam(':first_name', $this->first_name);
		$stmt->bindParam(':last_name', $this->last_name);
		$stmt->bindParam(':login_fails', $this->login_fails, PDO::PARAM_INT);
		$stmt->bindParam(':login_disabled', $this->login_disabled, PDO::PARAM_INT);
		$stmt->execute();
		$this->id = $pdo->lastInsertId();
	}

	/**
	 * @see \system\interfaces\IEntity
	 */
	public function update(): void {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$pdo->prepare("SELECT password FROM actors WHERE id=:id");
			$pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
			if( $row = $pdo->execute()->fetch() ) {
				if( $this->password !== '' && $row["password"] !== $this->password ) {
					$this->password = StringHelper::getBCrypt($this->password);
				} else {
					$this->password = $row["password"];
				}
				$sql = "UPDATE actors SET email=:email, password=:password, first_name=:first_name, last_name=:last_name, login_fails=:login_fails, login_disabled=:login_disabled WHERE id=:id";
				$pdo->prepare($sql);
				$pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
				$pdo->bindParam(':email', $this->email);
				$pdo->bindParam(':password', $this->password);
				$pdo->bindParam(':first_name', $this->first_name);
				$pdo->bindParam(':last_name', $this->last_name);
				$pdo->bindParam(':login_fails', $this->login_fails, PDO::PARAM_INT);
				$pdo->bindParam(':login_disabled', $this->login_disabled, PDO::PARAM_INT);
				$pdo->execute();
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
			$pdo->prepare("DELETE FROM actors WHERE id=:id");
			$pdo->bindParam("id", $this->id, PDO::PARAM_INT);
			$pdo->execute();
			return true;
		}
		return false;
	}
}