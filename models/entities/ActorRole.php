<?php

namespace models\entities;

use PDO;
use PDOException;
use RuntimeException;

use core\Core;
use core\abstracts\AEntityNode;

/**
 * The ActorRole entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorRole extends AEntityNode {

	// The columns
	public int $id = 0;
	public ?int $child_of = null;
	public string $name = "";
	public int $rights_all = 0b0000;
	public int $rights_group = 0b0000;
	public int $rights_own = 0b0000;
	public bool $is_default = false;
	public bool $is_protected = false;

	/**
	 * The constructor loads the database content into this object.
	 * If id is 0 the entity will be empty
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM actor_roles WHERE id=:id");
			$stmt->bindParam(":id", $id, PDO::PARAM_INT);
			$stmt->setFetchMode(PDO::FETCH_INTO, $this);
			$stmt->execute();
			$stmt->fetch();
		}
		parent::__construct($this->id, $this->child_of, $this->name);
	}

	/**
	 * @return string
	 * @see \core\interfaces\IEntity
	 */
	public function create(): ?string {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "INSERT INTO actor_roles (child_of, name, rights_all, rights_group, rights_own) VALUES (:child_of, :name, :rights_all, :rights_group, :rights_own)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':child_of', $this->child_of, PDO::PARAM_INT);
			$stmt->bindParam(':name', $this->name);
			$stmt->bindParam(':rights_all', $this->rights_all, PDO::PARAM_INT);
			$stmt->bindParam(':rights_group', $this->rights_group, PDO::PARAM_INT);
			$stmt->bindParam(':rights_own', $this->rights_own, PDO::PARAM_INT);
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
		if( $this->id > 0 ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$sql = "UPDATE actor_roles SET child_of=:child_of, name=:name, rights_all=:rights_all, rights_group=:rights_group, rights_own=:rights_own WHERE id=:id";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->bindParam(':child_of', $this->child_of, PDO::PARAM_INT);
				$stmt->bindParam(':name', $this->name, PDO::PARAM_STR);
				$stmt->bindParam(':rights_all', $this->rights_all, PDO::PARAM_INT);
				$stmt->bindParam(':rights_group', $this->rights_group, PDO::PARAM_INT);
				$stmt->bindParam(':rights_own', $this->rights_own, PDO::PARAM_INT);
				$stmt->execute();
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
	}

	/**
	 * @return bool
	 * @see \core\interfaces\IEntity
	 */
	public function delete(): bool {
		if( $this->id > 0 ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$stmt = $pdo->prepare("DELETE FROM actor_roles WHERE id=:id");
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->execute();
				return true;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
		return false;
	}
}