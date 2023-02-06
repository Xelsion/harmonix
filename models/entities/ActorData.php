<?php
namespace models\entities;

use Exception;
use lib\App;
use lib\core\blueprints\AEntity;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use PDO;

/**
 * The ActorData entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class ActorData extends AEntity {

    public int $id = 0;

    public int $actor_id = 0;

    public ?int $connection_id = null;

    public string $data_key = "";

    public string $data_value = "";

    public string $created = "";

    public ?string $updated = null;

    public ?string $deleted = null;


    /**
     * The constructor loads the database content into this object.
     * If id is 0 the entity will be empty
     *
     * @param int $id
     *
     * @throws \lib\core\exceptions\SystemException
     */
    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM actor_data WHERE id=:id");
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
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "INSERT INTO actor_data (actor_id, connection_id, data_key, data_value) VALUES (:actor_id, :connection_id, :data_key, :data_value)";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
            $pdo->bindParam(':connection_id', $this->connection_id, PDO::PARAM_NULL|PDO::PARAM_INT);
            $pdo->bindParam(':data_key', $this->data_key);
            $pdo->bindParam(':data_value', $this->data_value);
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
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $sql = "UPDATE actor_data SET actor_id=:actor_id, connection_id=:connection_id, data_key=:data_key, data_value=:data_value: WHERE id=:id";
                $pdo->prepareQuery($sql);
                $pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
                $pdo->bindParam(':connection_id', $this->connection_id, PDO::PARAM_NULL|PDO::PARAM_INT);
                $pdo->bindParam(':data_key', $this->data_key);
                $pdo->bindParam(':data_value', $this->data_value);
                $pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
                $pdo->execute();
                $this->id = $pdo->lastInsertId();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool {
        if( $this->id > 0 ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("DELETE FROM actor_data WHERE id=:id");
                $pdo->bindParam("id", $this->id, PDO::PARAM_INT);
                $pdo->execute();
                return true;
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        return false;
    }
}