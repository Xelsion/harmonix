<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use models\ActorRoleModel;
use models\entities\ActorRole;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class ActorRoleRepository extends ARepository {

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
     * @return ActorRoleModel
     * @throws SystemException
     */
    public function get(int $id ): ActorRoleModel {
        try {
            $this->query_builder->Select()
                ->From("actor_roles")
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(":id", $id);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, ActorRoleModel::class);
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
    public function getAsArray(int $id ): array {
        try {
            $this->query_builder->Select()
                ->From("actor_roles")
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
                ->From("actor_roles");
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, ActorRoleModel::class);
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
        return $this->findIn("actor_roles", ActorRoleModel::class, $conditions, $order, $direction, $limit, $page);
    }

    /**
     * Returns the total number of actor roles
     *
     * @return int
     * @throws SystemException
     */
    public function getNumRows(): int {
        $this->query_builder->Select("COUNT(DISTINCT id)")->As("num_count")
            ->From("actor_roles");
        $this->pdo->useQueryBuilder($this->query_builder);
        $result = $this->pdo->execute()->fetch();
        return (int)$result["num_count"];
    }

    /**
     * @param ActorRole $role
     * @return void
     * @throws SystemException
     */
    public function createObject(ActorRole $role ): void {
        try {
            $this->query_builder->Insert("actor_roles")
                ->Columns(["child_of", "name", "rights_all", "rights_group", "rights_own"]);
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':child_of', $role->child_of, PDO::PARAM_INT);
            $this->pdo->bindParam(':name', $role->name);
            $this->pdo->bindParam(':rights_all', $role->rights_all, PDO::PARAM_INT);
            $this->pdo->bindParam(':rights_group', $role->rights_group, PDO::PARAM_INT);
            $this->pdo->bindParam(':rights_own', $role->rights_own, PDO::PARAM_INT);
            $this->pdo->execute();
            $role->id = $this->pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param ActorRole $role
     * @return void
     * @throws SystemException
     */
    public function updateObject(ActorRole $role ): void {
        if( $role->id === 0 || $role->is_protected ) {
            return;
        }

        try {
            $this->query_builder->Update("actor_roles")
                ->Set(["child_of", "name", "rights_all", "rights_group", "rights_own"])->Where("id=:id");
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $role->id, PDO::PARAM_INT);
            $this->pdo->bindParam(':child_of', $role->child_of, PDO::PARAM_INT);
            $this->pdo->bindParam(':name', $role->name);
            $this->pdo->bindParam(':rights_all', $role->rights_all, PDO::PARAM_INT);
            $this->pdo->bindParam(':rights_group', $role->rights_group, PDO::PARAM_INT);
            $this->pdo->bindParam(':rights_own', $role->rights_own, PDO::PARAM_INT);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param ActorRole $role
     * @return void
     * @throws SystemException
     */
    public function deleteObject(ActorRole $role ): void {
        if( $role->id === 0 || $role->is_protected ) {
            return;
        }

        try {
            $this->query_builder->Delete("actor_roles")
                ->Where("id=:id");
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $role->id, PDO::PARAM_INT);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}