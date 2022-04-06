<?php

namespace models\entities;

use PDO;
use PDOException;
use RuntimeException;
use system\Core;
use system\abstracts\AEntity;

class AccessRestrictionType extends AEntity {

    public int $id = 0;
    public string $name = "";
    public int $include_siblings = 0;
    public int $include_children = 0;
    public int $include_descendants = 0;
    public string $created = "";
    public ?string $updated = null;
    public ?string $deleted = null;

    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            $pdo = Core::$_connection_manager->getConnection("mvc");
            $pdo->prepare("SELECT * FROM access_restriction_types WHERE id=:id");
            $pdo->bindParam(":id", $id, PDO::PARAM_INT);
            $pdo->setFetchMode(PDO::FETCH_INTO, $this);
            $pdo->execute()->fetch();
        }
    }

    /**
     *
     * @see \system\interfaces\IEntity
     */
    public function create(): void {
        $pdo = Core::$_connection_manager->getConnection("mvc");
        $sql = "INSERT INTO access_restriction_types (name, include_siblings, include_children, include_descendants) VALUES (:name, :include_siblings, :include_children, :include_descendants)";
        $pdo->prepare($sql);
        $pdo->bindParam(':name', $this->name);
        $pdo->bindParam(':include_siblings', $this->include_siblings, PDO::PARAM_INT);
        $pdo->bindParam(':include_children', $this->include_children, PDO::PARAM_INT);
        $pdo->bindParam(':include_descendants', $this->include_descendants, PDO::PARAM_INT);
        $pdo->execute();
        $this->id = $pdo->lastInsertId();
    }

    /**
     * @see \system\interfaces\IEntity
     */
    public function update(): void {
        try {
            $pdo = Core::$_connection_manager->getConnection("mvc");
            $sql = "UPDATE access_restriction_types SET name=:name, include_siblings=:include_siblings, include_children=:include_children, include_descendants=:include_descendants WHERE id=:id";
            $pdo->prepare($sql);
            $pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
            $pdo->bindParam(':name', $this->name);
            $pdo->bindParam(':include_siblings', $this->include_siblings, PDO::PARAM_INT);
            $pdo->bindParam(':include_children', $this->include_children, PDO::PARAM_INT);
            $pdo->bindParam(':include_descendants', $this->include_descendants, PDO::PARAM_INT);
            $pdo->execute();
        } catch( PDOException $e ) {
            throw new RuntimeException($e->getMessage());
        }
    }

    /**
     * @see \system\interfaces\IEntity
     * @return bool
     */
    public function delete(): bool {
        if( $this->id > 0 ) {
            $pdo = Core::$_connection_manager->getConnection("mvc");
            $pdo->prepare("DELETE FROM access_restriction_types WHERE id=:id");
            $pdo->bindParam("id", $this->id, PDO::PARAM_INT);
            $pdo->execute();
            return true;
        }
        return false;
    }
}