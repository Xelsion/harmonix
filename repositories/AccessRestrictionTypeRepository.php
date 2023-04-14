<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
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

    private QueryBuilder $query_builder;

    /**
     * @throws SystemException
     */
    public function __construct() {
        $cm = App::getInstanceOf(ConnectionManager::class);
        $this->pdo = $cm->getConnection("mvc");
        $this->query_builder = App::getInstanceOf(QueryBuilder::class, null, ["pdo" => $this->pdo]);
    }

    /**
     * @param int $id
     * @return AccessRestrictionTypeModel
     * @throws SystemException
     */
    public function get( int $id): AccessRestrictionTypeModel {
        try {
            $this->query_builder->Select()
                ->From("access_restriction_types")
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(":id", $id);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, AccessRestrictionTypeModel::class);
            return $this->pdo->execute()->fetch();
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
            $this->query_builder->Select()
                ->From("access_restriction_types")
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(":id", $id);
            return $this->pdo->execute()->fetch();
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
            $this->query_builder->Select()
                ->From("access_restriction_types");
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, AccessRestrictionTypeModel::class);
            return $this->pdo->execute()->fetchAll();
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
        $this->query_builder->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("access_restriction_types");
        $this->pdo->useQueryBuilder($this->query_builder);
        $result = $this->pdo->execute()->fetch();
        return (int)$result["num_count"];
    }


    /**
     * @param AccessRestrictionType $restriction_type
     * @return void
     * @throws SystemException
     */
    public function createObject(AccessRestrictionType $restriction_type ): void {
        try {
            $this->query_builder->Insert("access_restriction_types")
                ->Columns(["name", "include_siblings", "include_children", "include_descendants"])
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':name', $restriction_type->name);
            $this->pdo->bindParam(':include_siblings', $restriction_type->include_siblings, PDO::PARAM_INT);
            $this->pdo->bindParam(':include_children', $restriction_type->include_children, PDO::PARAM_INT);
            $this->pdo->bindParam(':include_descendants', $restriction_type->include_descendants, PDO::PARAM_INT);
            $this->pdo->execute();
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
            $this->query_builder->Update("access_restriction_types")
                ->Set(["name", "include_siblings", "include_children", "include_descendants"])
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $restriction_type->id, PDO::PARAM_INT);
            $this->pdo->bindParam(':name', $restriction_type->name);
            $this->pdo->bindParam(':include_siblings', $restriction_type->include_siblings, PDO::PARAM_INT);
            $this->pdo->bindParam(':include_children', $restriction_type->include_children, PDO::PARAM_INT);
            $this->pdo->bindParam(':include_descendants', $restriction_type->include_descendants, PDO::PARAM_INT);
            $this->pdo->execute();
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
            $this->query_builder->Delete("access_restriction_types")->Where("id=:id");
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam("id", $restriction_type->id, PDO::PARAM_INT);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}