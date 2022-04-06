<?php

namespace models\entities;

use system\Core;
use PDO;
use PDOException;
use RuntimeException;

use system\abstracts\AEntity;

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
    public string $ip = "";
	public string $expired = "";
    public string $created = "";
    public ?string $updated = null;
    public ?string $deleted = null;

	/**
	 * The constructor loads the database content into this object.
	 * If a session was set it will load it else it will return an
	 * empty entity
	 */
	public function __construct() {
		if( isset($_COOKIE["session"]) ) {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$pdo->prepare("SELECT * FROM sessions WHERE id=:id");
			$pdo->bindParam(":id", $_COOKIE["session"]);
			$pdo->setFetchMode(PDO::FETCH_INTO, $this);
			$pdo->execute()->fetch();
		}
	}

	/**
	 * @see \system\interfaces\IEntity
	 */
	public function create(): void {
		try {
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "INSERT INTO sessions (id, actor_id, ip, expired) VALUES (:id, :actor_id, :ip, :expired)";
			$pdo->prepare($sql);
			$pdo->bindParam(':id', $this->id);
			$pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
            $pdo->bindParam(':ip', $this->ip);
			$pdo->bindParam(':expired', $this->expired);
			$pdo->execute();
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
			$sql = "UPDATE sessions SET actor_id=:actor_id, ip=:ip, expired=:expired WHERE id=:id";
			$pdo->prepare($sql);
			$pdo->bindParam(':id', $this->id);
			$pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
            $pdo->bindParam(':ip', $this->ip);
			$pdo->bindParam(':expired', $this->expired);
			$pdo->execute();
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
				$pdo->prepare("DELETE FROM sessions WHERE id=:id");
				$pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
				$pdo->execute();
				return true;
			} catch( PDOException $e ) {
				throw new RuntimeException($e->getMessage());
			}
		}
		return false;
	}
}