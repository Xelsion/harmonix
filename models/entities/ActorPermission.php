<?php

namespace models\entities;

use PDO;
use PDOException;
use RuntimeException;

use core\abstracts\AEntity;
use core\Core;

class ActorPermission extends AEntity {

	public int $id = 0;
	public int $actor_id = 0;
	public int $role_id = 0;
	public string $path = "";
	public ?string $controller = null;
	public ?string $method = null;

	/**
	 * @inheritDoc
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
	 * @inheritDoc
	 */
	public function update(): void {
		if( $this->id > 0 ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$sql = "UPDATE actor_permissions SET actor_id=:actor_id, role_id=:role_id, path=:path, controller=:controller, method=:method WHERE id=:id";
				$stmt = $pdo->prepare($sql);
				$stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
				$stmt->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
				$stmt->bindParam(':role_id', $this->role_id, PDO::PARAM_INT);
				$stmt->bindParam(':path', $this->path, PDO::PARAM_STR);
				$stmt->bindParam(':controller', $this->controller, PDO::PARAM_STR);
				$stmt->bindParam(':method', $this->method, PDO::PARAM_STR);
				$stmt->execute();
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
	}

	/**
	 * @inheritDoc
	 */
	public function delete(): bool {
		$pdo = Core::$_connection_manager->getConnection("mvc");
		if( $this->id > 0 ) {
			try {
				$stmt = $pdo->prepare("DELETE FROM actor_permissions WHERE id=:id");
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