<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use models\AccessPermissionModel;
use models\AccessRestrictionModel;
use models\entities\AccessPermission;
use models\entities\Actor;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class AccessPermissionRepository extends ARepository {

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
     * @param int $actor_id
     * @param int $role_id
     * @param string $domain
     * @param string|null $controller
     * @param string|null $method
     * @return mixed
     * @throws SystemException
     */
    public function get(int $actor_id, int $role_id, string $domain, ?string $controller, ?string $method ): AccessRestrictionModel {
        try {
            return $this->pdo->Select()
                    ->From("access_permission")
                    ->Where("actor_id=:actor_id")
                        ->And("role_id=:role_id")
                        ->And("domain=:domain")
                        ->And("controller=:controller")
                        ->And("method=:method")
                ->setParam(":actor_id", $actor_id, PDO::PARAM_INT)
                ->setParam(":role_id", $role_id, PDO::PARAM_INT)
                ->setParam(":domain", $domain)
                ->setParam(":controller", $controller)
                ->setParam(":method", $method)
                ->fetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class)
                ->execute()
                ->fetch()
            ;
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
                    ->From("access_permissions")
                ->fetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class)
                ->execute()
                ->fetchAll()
            ;
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Actor $actor
     * @return array
     * @throws SystemException
     */
    public function getAccessPermissionFor( Actor $actor ): array {
        try {
            $this->query_builder->Select()
                ->From("access_permissions")
                ->Where("actor_id=:actor_id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam("actor_id", $actor->id, PDO::PARAM_INT);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, AccessPermissionModel::class);
            return $this->pdo->execute()->fetchAll();
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param Actor $actor
     * @return void
     * @throws SystemException
     */
    public function deleteAccessPermissionFor( Actor $actor ): void {
        try {
            $this->query_builder->Delete("access_permissions")
                ->Where("actor_id=:actor_id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam("actor_id", $actor->id, PDO::PARAM_INT);
            $this->pdo->execute();
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
        return $this->findIn("access_permissions", AccessPermissionModel::class, $conditions, $order, $direction, $limit, $page);
    }

    /**
     * Returns the total number of access permissions
     *
     * @return int
     * @throws SystemException
     */
    public function getNumRows(): int {
        $this->query_builder->Select("COUNT(DISTINCT *)")->As("num_count")
            ->From("access_permissions")
        ;
        $this->pdo->useQueryBuilder($this->query_builder);
        $result = $this->pdo->execute()->fetch();
        return (int)$result["num_count"];
    }

    /**
     * @param AccessPermission $permission
     * @return void
     * @throws SystemException
     */
    public function createObject( AccessPermission $permission ): void {
        try {
            $this->query_builder->Insert("access_permissions")
                ->Columns(["actor_id", "role_id", "domain", "controller", "method"])
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':actor_id', $permission->actor_id, PDO::PARAM_INT);
            $this->pdo->bindParam(':role_id', $permission->role_id, PDO::PARAM_INT);
            $this->pdo->bindParam(':domain', $permission->domain);
            $this->pdo->bindParam(':controller', $permission->controller);
            $this->pdo->bindParam(':method', $permission->method);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param AccessPermission $permission
     * @return void
     * @throws SystemException
     */
    public function updateObject(AccessPermission $permission ): void {

    }

    /**
     * @param AccessPermission $permission
     * @return void
     * @throws SystemException
     */
    public function deleteObject(AccessPermission $permission ): void {
        try {
            $this->query_builder->Delete("access_permissions")
                ->Where("actor_id=:actor_id")
                    ->And("role_id=:role_id")
                    ->And("domain=:domain")
                    ->And("controller=:controller")
                    ->And("method=:method")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':actor_id', $permission->actor_id, PDO::PARAM_INT);
            $this->pdo->bindParam(':role_id', $permission->role_id, PDO::PARAM_INT);
            $this->pdo->bindParam(':domain', $permission->domain);
            $this->pdo->bindParam(':controller', $permission->controller);
            $this->pdo->bindParam(':method', $permission->method);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

}