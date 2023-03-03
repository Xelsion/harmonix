<?php

namespace models\entities;

use Exception;
use lib\core\exceptions\SystemException;
use PDO;
use lib\App;
use lib\core\blueprints\AEntity;
use lib\core\ConnectionManager;


/**
 * The Token entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class Token extends AEntity {

    public string $id = "";
    public string $expired = "";

    /**
     * @param string $id
     * @throws SystemException
     */
    public function __construct( string $id = "") {
        if( $id !== "" ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM tokens WHERE id=:id");
                $pdo->bindParam(":id", $id);
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
        if( $this->id !== "" && $this->expired !== "" ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("INSERT INTO tokens (id, expired) VALUES (:id, :expired)");
                $pdo->bindParam(":id", $this->id);
                $pdo->bindParam(":expired", $this->expired);
                $pdo->execute();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
    }

    /**
     * @inheritDoc
     */
    public function update(): void {
        if( $this->id !== "" ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("UPDATE tokens SET expired=:expired WHERE id=:id");
                $pdo->bindParam(":id", $this->id);
                $pdo->bindParam(":expired", $this->expired);
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
        if( $this->id !== "" ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("DELETE FROM tokens WHERE id=:id");
                $pdo->bindParam(":id", $this->id);
                $pdo->execute();
                return true;
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
            }
        }
        return false;
    }
}