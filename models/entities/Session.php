<?php

namespace models\entities;

use PDO;
use Exception;

use system\Core;
use system\abstracts\AEntity;
use system\exceptions\SystemException;
use system\helper\StringHelper;

/**
 * The SessionModel entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Session extends AEntity {

    // Settings
    private bool $_rotate_session = false;

	// The columns
	public string $id = "";
	public int $actor_id = 0;
    public string $ip = "";
	public string $expired = "";
    public string $created = "";
    public ?string $updated = null;

    /**
     * The constructor loads the database content into this object.
     * If a session was set it will load it else it will return an
     * empty entity
     *
     * @throws SystemException
     */
	public function __construct() {
        $rotate_session = Core::$_configuration->getSectionValue("security", "rotate_session");
        if( !is_null($rotate_session) ) {
            $this->_rotate_session = (bool)$rotate_session;
        }

		if( isset($_COOKIE["session"]) ) {
            try {
                $pdo = Core::$_connection_manager->getConnection("mvc");
                $pdo->prepare("SELECT * FROM sessions WHERE id=:id");
                $pdo->bindParam(":id", $_COOKIE["session"]);
                $pdo->setFetchMode(PDO::FETCH_INTO, $this);
                $pdo->execute()->fetch();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
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
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

    /**
     * @inheritDoc
     *
     * @throws SystemException
     */
	public function update(): void {
		try {
            $curr_id = $this->id;
            if( $this->_rotate_session ) {
                $this->id = StringHelper::getGuID();
            }
			$pdo = Core::$_connection_manager->getConnection("mvc");
			$sql = "UPDATE sessions SET id=:id, actor_id=:actor_id, ip=:ip, expired=:expired WHERE id=:curr_id";
			$pdo->prepare($sql);
            $pdo->bindParam(':id', $this->id);
			$pdo->bindParam(':curr_id', $curr_id);
			$pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
            $pdo->bindParam(':ip', $this->ip);
			$pdo->bindParam(':expired', $this->expired);
			$pdo->execute();
		} catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
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
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
			}
		}
		return false;
	}

}
