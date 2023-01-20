<?php

namespace models\entities;

use Exception;
use lib\abstracts\AEntity;
use lib\core\System;
use lib\exceptions\SystemException;
use PDO;

class DataConnection extends AEntity {

    public int $id = 0;

    public string $name = "";

    public string $db_name = "";

    public string $table_name = "";

    public string $table_col = "";

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
                $pdo = System::$Core->connection_manager->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM data_connections WHERE id=:id");
                $pdo->bindParam(":id", $id, PDO::PARAM_INT);
                $pdo->setFetchMode(PDO::FETCH_INTO, $this);
                $pdo->execute()->fetch();
            } catch( Exception $e ) {
                throw new SystemException(__FILE__, __LINE__, $e->getMessage());
            }
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
            $pdo->prepareQuery("INSERT INTO data_connections (name, db_name, table_name, table_col) VALUES (:name, :db_name, :table_name, :table_col)");
            $pdo->bindParam(":name", $this->name, PDO::PARAM_STR);
            $pdo->bindParam(":db_name", $this->db_name, PDO::PARAM_STR);
            $pdo->bindParam(":table_name", $this->table_name, PDO::PARAM_STR);
            $pdo->bindParam(":table_col", $this->table_col, PDO::PARAM_STR);
            $pdo->execute();
            $this->id = $pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }
    }

    /**
     * @return void
     *
     * @throws SystemException
     */
    public function update(): void {
        try {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $pdo->prepareQuery("UPDATE data_connections SET name=:name, db_name=:db_name, table_name=:table_name, table_col=:table_col WHERE id=:id");
            $pdo->bindParam(":name", $this->name, PDO::PARAM_STR);
            $pdo->bindParam(":db_name", $this->db_name, PDO::PARAM_STR);
            $pdo->bindParam(":table_name", $this->table_name, PDO::PARAM_STR);
            $pdo->bindParam(":table_col", $this->table_col, PDO::PARAM_STR);
            $pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
            $pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function delete(): bool {
        if( $this->id > 0 ) {
            $pdo = System::$Core->connection_manager->getConnection("mvc");
            $pdo->prepareQuery("DELETE FROM data_connections WHERE id=:id");
            $pdo->bindParam(":id", $this->id, PDO::PARAM_INT);
            $pdo->execute();
            return true;
        }
        return false;
    }
}