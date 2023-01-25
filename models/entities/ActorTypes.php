<?php
namespace models\entities;

use PDO;
use lib\App;
use lib\abstracts\AEntity;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

/**
 * The ActorTypes entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorTypes extends AEntity {

    public int $id = 0;
    public string $name = "";
    public bool $is_protected = false;
    public string $created = "";
    public ?string $updated = null;
    public ?string $deleted = null;

    /**
     * The constructor loads the database content into this object.
     * If id is 0 the entity will be empty
     *
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            try {
                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM actor_types WHERE id=:id");
                $pdo->bindParam(":id", $id, PDO::PARAM_INT);
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
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "INSERT INTO actor_types (name) VALUES (:name)";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':name', $this->name);
            $pdo->execute();
            $this->id = $pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritDoc
     */
    public function update(): void {
        if( $this->id > 0 ) {
            try {
                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $sql = "UPDATE actor_types SET name=:name WHERE id=:id";
                $pdo->prepareQuery($sql);
                $pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
                $pdo->bindParam(':name', $this->name);
                $pdo->execute();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool {
        if( $this->id > 0 && !$this->is_protected ) {
            try {
                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("DELETE FROM actor_types WHERE id=:id");
                $pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
                $pdo->execute();
                return true;
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        return false;
    }

}
