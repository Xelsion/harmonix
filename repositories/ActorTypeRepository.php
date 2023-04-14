<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use models\entities\ActorType;
use models\ActorTypeModel;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class ActorTypeRepository extends ARepository {

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
     * @return mixed
     * @throws SystemException
     */
    public function get(int $id ): ActorTypeModel {
        try {
            $this->query_builder->Select()
                ->From("actor_types")
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(":id", $id);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, ActorTypeModel::class);
            return $this->pdo->execute()->fetch();
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param int $id
     * @return mixed
     * @throws SystemException
     */
    public function getAsArray(int $id ): ActorTypeModel {
        try {
            $this->query_builder->Select()
                ->From("actor_types")
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
                ->From("actor_types")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, ActorTypeModel::class);
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
        return $this->findIn("actor_types", ActorTypeModel::class, $conditions, $order, $direction, $limit, $page);
    }

    /**
     * Returns the total number of actor types
     *
     * @return int
     * @throws SystemException
     */
    public function getNumRows(): int {
        $this->query_builder->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("actor_types")
        ;
        $this->pdo->useQueryBuilder($this->query_builder);
        $result = $this->pdo->execute()->fetch();
        return (int)$result["num_count"];
    }

    /**
     * @param ActorType $type
     * @return void
     * @throws SystemException
     */
    public function createObject(ActorType $type ): void {
        try {
            $this->query_builder->Insert("actor_types")
                ->Columns(["name"])
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':name', $type->name);
            $this->pdo->execute();
            $type->id = $this->pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param ActorType $type
     * @return void
     * @throws SystemException
     */
    public function updateObject(ActorType $type ): void {
        if( $type->id === 0 || $type->is_protected ) {
            return;
        }
        try {
            $this->query_builder->Update("actor_types")
                ->Columns(["name"])
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $type->id, PDO::PARAM_INT);
            $this->pdo->bindParam(':name', $type->name);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param ActorType $type
     * @return void
     * @throws SystemException
     */
    public function deleteObject(ActorType $type ): void {
        if( $type->id === 0 || $type->is_protected ) {
            return;
        }

        try {
            $this->query_builder->Delete("actor_types")
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $type->id, PDO::PARAM_INT);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}