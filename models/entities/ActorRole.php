<?php

namespace models\entities;

use JsonException;
use PDO;
use PDOException;
use RuntimeException;

use system\abstracts\ACacheableEntity;
use system\Core;
use system\exceptions\SystemException;

/**
 * The ActorRole entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorRole extends ACacheableEntity {

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
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$pdo->prepare("SELECT * FROM actor_roles WHERE id=:id");
			$pdo->bindParam(":id", $id, PDO::PARAM_INT);
			$pdo->setFetchMode(PDO::FETCH_INTO, $this);
			$pdo->execute()->fetch();
		}
	}

    /**
     * @inheritDoc
     */
	public function create(): void {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "INSERT INTO actor_roles (child_of, name, rights_all, rights_group, rights_own) VALUES (:child_of, :name, :rights_all, :rights_group, :rights_own)";
			$pdo->prepare($sql);
			$pdo->bindParam(':child_of', $this->child_of, PDO::PARAM_INT);
			$pdo->bindParam(':name', $this->name);
			$pdo->bindParam(':rights_all', $this->rights_all, PDO::PARAM_INT);
			$pdo->bindParam(':rights_group', $this->rights_group, PDO::PARAM_INT);
			$pdo->bindParam(':rights_own', $this->rights_own, PDO::PARAM_INT);
			$pdo->execute();
			$this->id = $pdo->lastInsertId();
		} catch( PDOException $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage());
		}
	}

    /**
     * @inheritDoc
     *
     * @throws JsonException
     * @throws SystemException
     */
	public function update(): void {
		if( $this->id > 0 ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$sql = "UPDATE actor_roles SET child_of=:child_of, name=:name, rights_all=:rights_all, rights_group=:rights_group, rights_own=:rights_own WHERE id=:id";
				$pdo->prepare($sql);
				$pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
				$pdo->bindParam(':child_of', $this->child_of, PDO::PARAM_INT);
				$pdo->bindParam(':name', $this->name);
				$pdo->bindParam(':rights_all', $this->rights_all, PDO::PARAM_INT);
				$pdo->bindParam(':rights_group', $this->rights_group, PDO::PARAM_INT);
				$pdo->bindParam(':rights_own', $this->rights_own, PDO::PARAM_INT);
				$pdo->execute();
			} catch( PDOException $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
			}
		}
	}

    /**
     * @inheritDoc
     */
	public function delete(): bool {
		if( $this->id > 0 ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$pdo->prepare("DELETE FROM actor_roles WHERE id=:id");
				$pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
				$pdo->execute();
				return true;
			} catch( PDOException $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
			}
		}
		return false;
	}

}