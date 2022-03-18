<?php

namespace models\entities;

use PDO;
use PDOException;
use RuntimeException;

use system\abstracts\AEntity;
use system\Core;

/**
 * The ActorPermission entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorPermission extends AEntity {

	// The columns
	public int $actor_id = 0;
	public int $role_id = 0;
	public string $path = "";
	public ?string $controller = null;
	public ?string $method = null;

	/**
	 * The constructor loads the database content into this object.
	 * If id is 0 the entity will be empty
	 *
	 * @param int $id
	 */
	public function __construct( int $id = 0 ) {
		if( $id > 0 ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$stmt = $pdo->prepare("SELECT * FROM actor_permissions WHERE id=:id");
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
	public function create(): ?int {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "INSERT INTO actor_permissions (actor_id, role_id, path, controller, method) VALUES (:actor_id, :role_id, :path, :controller, :method)";
			$stmt = $pdo->prepare($sql);
			$stmt->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
			$stmt->bindParam(':role_id', $this->role_id, PDO::PARAM_INT);
			$stmt->bindParam(':path', $this->path, PDO::PARAM_STR);
			$stmt->bindParam(':controller', $this->controller, PDO::PARAM_STR);
			$stmt->bindParam(':method', $this->method, PDO::PARAM_STR);
			$stmt->execute();
			$insert_id = $pdo->lastInsertId();
		} catch( PDOException $e ) {
			throw new RuntimeException($e->getMessage());
		}
		return $insert_id;
	}

	/**
	 * @see \system\interfaces\IEntity
	 */
	public function update(): void {

	}

	/**
	 * @see \system\interfaces\IEntity
	 * @return bool
	 */
	public function delete(): bool {
		$pdo = Core::$_connection_manager->getConnection("mvc");
		if( $this->actor_id > 0 ) {
			try {
				$stmt = $pdo->prepare("DELETE FROM actor_permissions WHERE actor_id=:actor_id");
				$stmt->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
				$stmt->execute();
				return true;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
		return false;
	}
}