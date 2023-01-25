<?php
namespace models\entities;

use PDO;
use lib\App;
use lib\abstracts\AEntity;
use lib\classes\Configuration;
use lib\helper\StringHelper;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

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

	// The columns of the DB table
	public string $id = "";
	public int $actor_id = 0;
    public int $as_actor = 0;
    public string $ip = "";
	public string $expired = "";
    public string $created = "";
    public ?string $updated = null;

    /**
     * The constructor loads the database content into this object.
     * If a session was setClass it will load it else it will return an
     * empty entity
     */
	public function __construct( Configuration $config ) {
        $rotate_session = $config->getSectionValue("security", "rotate_session");
        if( !is_null($rotate_session) ) {
            $this->_rotate_session = (bool)$rotate_session;
        }
	}

    /**
     * @param string $session_id
     *
     * @return void
     *
     * @throws SystemException
     */
    public function init( string $session_id ): void {
        try {
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $pdo->prepareQuery("SELECT * FROM sessions WHERE id=:id");
            $pdo->bindParam(":id", $session_id);
            $pdo->setFetchMode(PDO::FETCH_INTO, $this);
            $pdo->execute()->fetch();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }


    /**
     * @inheritDoc
     */
	public function create(): void {
		try {
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
			$sql = "INSERT INTO sessions (id, actor_id, ip, expired) VALUES (:id, :actor_id, :ip, :expired)";
			$pdo->prepareQuery($sql);
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
     */
	public function update(): void {
		try {
            $curr_id = $this->id;
            if( $this->_rotate_session ) {
                $this->id = StringHelper::getGuID();
            }
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
			$sql = "UPDATE sessions SET id=:id, actor_id=:actor_id, as_actor=:as_actor, ip=:ip, expired=:expired WHERE id=:curr_id";
			$pdo->prepareQuery($sql);
            $pdo->bindParam(':id', $this->id);
			$pdo->bindParam(':curr_id', $curr_id);
			$pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
            $pdo->bindParam(':as_actor', $this->as_actor, PDO::PARAM_INT);
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
                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
				$pdo->prepareQuery("DELETE FROM sessions WHERE id=:id");
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
