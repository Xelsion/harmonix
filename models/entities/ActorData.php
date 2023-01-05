<?php

namespace models\entities;

use Exception;
use JsonException;
use PDO;

use system\System;
use system\abstracts\AEntity;
use system\exceptions\SystemException;

class ActorData extends AEntity {

    public int $id = 0;

    public int $actor_id = 0;

    public int $connection_id = 0;

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
     * @throws JsonException
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $pdo->prepareQuery("SELECT * FROM actor_data WHERE id=:id");
            $pdo->bindParam(":id", $id, PDO::PARAM_INT);
            $pdo->setFetchMode(PDO::FETCH_INTO, $this);
            $pdo->execute()->fetch();
        }
    }

    /**
     * @return void
     *
     * @throws SystemException
     */
    public function create(): void {
        try {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $sql = "INSERT INTO actor_data (actor_id, connection_id, data_key, data_value) VALUES (:actor_id, :connection_id, :data_key, :data_value)";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
            $pdo->bindParam(':connection_id', $this->connection_id, PDO::PARAM_INT);
            $pdo->bindParam(':data_key', $this->data_key);
            $pdo->bindParam(':data_value', $this->data_value);
            $pdo->execute();
            $this->id = $pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @return void
     *
     * @throws SystemException
     */
    public function update(): void {
        if( $this->id > 0 ) {
            try {
                $pdo = System::$Core->connection_manager->getConnection("mvc");
                $sql = "UPDATE actor_data SET actor_id=:actor_id, connection_id=:connection_id, data_key=:data_key, data_value=:data_value: WHERE id=:id";
                $pdo->prepareQuery($sql);
                $pdo->bindParam(':actor_id', $this->actor_id, PDO::PARAM_INT);
                $pdo->bindParam(':connection_id', $this->connection_id, PDO::PARAM_INT);
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

    public function delete(): bool {
        if( $this->id > 0 ) {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $pdo->prepareQuery("DELETE FROM actor_data WHERE id=:id");
            $pdo->bindParam("id", $this->id, PDO::PARAM_INT);
            $pdo->execute();
            return true;
        }
        return false;
    }
}