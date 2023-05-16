<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\exceptions\SystemException;
use models\AccessRestrictionTypeModel;
use models\entities\AccessRestrictionType;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class AccessRestrictionTypeRepository extends ARepository {

    /**
     * @throws SystemException
     */
    public function __construct() {
        $cm = App::getInstanceOf(ConnectionManager::class);
        $this->pdo = $cm->getConnection("mvc");
    }

    /**
     * @param int $id
     * @return AccessRestrictionTypeModel
     * @throws SystemException
     */
    public function get( int $id): AccessRestrictionTypeModel {
        try {
            $restriction_type = $this->pdo->Select()
                ->From("access_restriction_types")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id)
                ->fetchMode(PDO::FETCH_CLASS, AccessRestrictionTypeModel::class)
                ->execute()
                ->fetch()
            ;
            if( !$restriction_type ) {
                $restriction_type = new AccessRestrictionTypeModel();
            }
            return $restriction_type;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param int $id
     * @return array
     * @throws SystemException
     */
    public function getAsArray( int $id): array {
        try {
            $restriction_type = $this->pdo->Select()
                ->From("access_restriction_types")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(":id", $id)
                ->execute()
                ->fetch()
            ;
            if( !$restriction_type ) {
                $restriction_type = array();
            }
            return $restriction_type;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @return array
     * @throws SystemException
     */
    public function getAll(): array {
        try {
            return $this->pdo->Select()
                ->From("access_restriction_types")
                ->prepareStatement()
                ->fetchMode(PDO::FETCH_CLASS, AccessRestrictionTypeModel::class)
                ->execute()
                ->fetchAll()
            ;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param array $conditions
     * @param string|null $order
     * @param string|null $direction
     * @param int $limit
     * @param int $page
     * @return array
     * @throws SystemException
     */
    public function find(array $conditions = array(), ?string $order = "", ?string $direction = "asc", int $limit = 0, int $page = 1): array {
        return $this->findIn("access_restriction_types", AccessRestrictionTypeModel::class, $conditions, $order, $direction, $limit, $page);
    }

    /**
     * Returns the total number of access restrictions
     *
     * @return int
     * @throws SystemException
     */
    public function getNumRows(): int {
        $result = $this->pdo->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("access_restriction_types")
            ->prepareStatement()
            ->execute()
            ->fetch()
        ;
        return (int)$result["num_count"];
    }


    /**
     * @param AccessRestrictionType $restriction_type
     * @return void
     * @throws SystemException
     */
    public function createObject(AccessRestrictionType $restriction_type ): void {
        try {
            $this->pdo->Insert("access_restriction_types")
                ->Columns(["name", "include_siblings", "include_children", "include_descendants"])
                ->prepareStatement()
                    ->withParam(':name', $restriction_type->name)
                    ->withParam(':include_siblings', $restriction_type->include_siblings, PDO::PARAM_INT)
                    ->withParam(':include_children', $restriction_type->include_children, PDO::PARAM_INT)
                    ->withParam(':include_descendants', $restriction_type->include_descendants, PDO::PARAM_INT)
                ->execute()
            ;
            $restriction_type->id = $this->pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param AccessRestrictionType $restriction_type
     * @return void
     * @throws SystemException
     */
    public function updateObject(AccessRestrictionType $restriction_type ): void {
        try {
            $this->pdo->Update("access_restriction_types")
                ->Set(["name", "include_siblings", "include_children", "include_descendants"])
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $restriction_type->id, PDO::PARAM_INT)
                    ->withParam(':name', $restriction_type->name)
                    ->withParam(':include_siblings', $restriction_type->include_siblings, PDO::PARAM_INT)
                    ->withParam(':include_children', $restriction_type->include_children, PDO::PARAM_INT)
                    ->withParam(':include_descendants', $restriction_type->include_descendants, PDO::PARAM_INT)
                ->execute()
            ;
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param AccessRestrictionType $restriction_type
     * @return void
     * @throws SystemException
     */
    public function deleteObject(AccessRestrictionType $restriction_type ): void {
        try {
            $this->pdo->Delete("access_restriction_types")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $restriction_type->id, PDO::PARAM_INT)
                ->execute()
            ;
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}