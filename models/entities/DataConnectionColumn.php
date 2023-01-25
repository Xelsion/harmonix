<?php
namespace models\entities;

use PDO;
use lib\App;
use lib\abstracts\AEntity;
use lib\manager\ConnectionManager;

use Exception;
use lib\exceptions\SystemException;

/**
 * The DataConnectionColumn entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class DataConnectionColumn extends AEntity {

    public int $id = 0;

    public int $connection_id = 0;

    public string $column_name = "";

    public string $created = "";

    public ?string $updated = null;

    public ?string $deleted = null;

    /**
     * @param int $id
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            try {
                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM data_connection_columns WHERE id=:id");
                $pdo->bindParam(":id", $id, PDO::PARAM_INT);
                $pdo->setFetchMode(PDO::FETCH_INTO, $this);
                $pdo->execute()->fetch();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function create(): void {
        try {
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $pdo->prepareQuery("INSERT INTO data_connection_columns (connection_id, column_name) VALUES (:connection_id, :column_name)");
            $pdo->bindParam(":connection_id", $this->connection_id, PDO::PARAM_INT);
            $pdo->bindParam(":column_name", $this->column_name, PDO::PARAM_STR);
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
        try {
            $cm = App::getInstance(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $pdo->prepareQuery("UPDATE data_connection_columns SET connection_id=:connection_id, column_name=:column_name WHERE id=:id");
            $pdo->bindParam(":connection_id", $this->connection_id, PDO::PARAM_INT);
            $pdo->bindParam(":column_name", $this->column_name, PDO::PARAM_STR);
            $pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
            $pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(): bool {
        if( $this->id > 0 ) {
            try {
                $cm = App::getInstance(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("DELETE FROM data_connection_columns WHERE id=:id");
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