<?php
namespace models\entities;

use Exception;
use lib\App;
use lib\core\blueprints\AEntity;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use PDO;

/**
 * The AccessRestrictionType entity
 * Represents a single entry in the database
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0;
 */
class AccessRestrictionType extends AEntity {

    public int $id = 0;
    public string $name = "";
    public int $include_siblings = 0;
    public int $include_children = 0;
    public int $include_descendants = 0;
    public string $created = "";
    public ?string $updated = null;
    public ?string $deleted = null;

    /**
     * The class constructor
     *
     * @throws SystemException
     */
    public function __construct( int $id = 0 ) {
        if( $id > 0 ) {
            try {
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("SELECT * FROM access_restriction_types WHERE id=:id");
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
            $sql = "INSERT INTO access_restriction_types (name, include_siblings, include_children, include_descendants) VALUES (:name, :include_siblings, :include_children, :include_descendants)";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':name', $this->name);
            $pdo->bindParam(':include_siblings', $this->include_siblings, PDO::PARAM_INT);
            $pdo->bindParam(':include_children', $this->include_children, PDO::PARAM_INT);
            $pdo->bindParam(':include_descendants', $this->include_descendants, PDO::PARAM_INT);
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
            $cm = App::getInstanceOf(ConnectionManager::class);
            $pdo = $cm->getConnection("mvc");
            $sql = "UPDATE access_restriction_types SET name=:name, include_siblings=:include_siblings, include_children=:include_children, include_descendants=:include_descendants WHERE id=:id";
            $pdo->prepareQuery($sql);
            $pdo->bindParam(':id', $this->id, PDO::PARAM_INT);
            $pdo->bindParam(':name', $this->name);
            $pdo->bindParam(':include_siblings', $this->include_siblings, PDO::PARAM_INT);
            $pdo->bindParam(':include_children', $this->include_children, PDO::PARAM_INT);
            $pdo->bindParam(':include_descendants', $this->include_descendants, PDO::PARAM_INT);
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
                $cm = App::getInstanceOf(ConnectionManager::class);
                $pdo = $cm->getConnection("mvc");
                $pdo->prepareQuery("DELETE FROM access_restriction_types WHERE id=:id");
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
