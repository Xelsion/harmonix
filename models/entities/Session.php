<?php

namespace models\entities;

use Exception;
use JsonException;
use system\abstracts\ACacheableEntity;
use system\Core;
use PDO;
use PDOException;
use RuntimeException;
use system\exceptions\SystemException;

/**
 * The Session entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session extends ACacheableEntity {

	// The columns
	public string $id = "";
	public int $actor_id = 0;
    public string $ip = "";
	public string $expired = "";

    /**
     * The constructor loads the database content into this object.
     * If a session was set it will load it else it will return an
     * empty entity
     *
     * @throws SystemException
     */
	public function __construct() {
		if( isset($_COOKIE["session"]) ) {
            try {
                $pdo = Core::$_connection_manager->getConnection("mvc");
                $pdo->prepare("SELECT * FROM sessions WHERE id=:id");
                $pdo->bindParam(":id", $_COOKIE["session"]);
                $pdo->setFetchMode(PDO::FETCH_INTO, $this);
                $pdo->execute()->fetch();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
            }
        }
	}

    /**
     * @inheritDoc
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
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage());
		}
	}

    /**
     * @inheritDoc
     *
     * @throws SystemException
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
		} catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
		}
	}

    /**
     * @inheritDoc
     */
	public function delete(): bool {
		if( $this->id !== "" ) {
			try {
				$pdo = Core::$_connection_manager->getConnection("mvc");
				$pdo->prepare("DELETE FROM sessions WHERE id=:id");
				$pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
				$pdo->execute();
				return true;
			} catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
			}
		}
		return false;
	}

}