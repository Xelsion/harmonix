<?php

namespace models\entities;

use Exception;
use JsonException;
use lib\abstracts\AEntity;
use lib\core\System;
use lib\exceptions\SystemException;
use PDO;

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
     * @throws JsonException
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $pdo->prepareQuery("SELECT * FROM actor_types WHERE id=:id");
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
            $pdo = System::$Core->connection_manager->getConnection("mvc");
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
     *
     * @throws SystemException
     */
    public function update(): void {
        if( $this->id > 0 ) {
            try {
                $pdo = System::$Core->connection_manager->getConnection("mvc");
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
                $pdo = System::$Core->connection_manager->getConnection("mvc");
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
