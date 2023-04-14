<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
use lib\core\database\QueryBuilder;
use lib\core\exceptions\SystemException;
use models\AccessRestrictionModel;
use models\entities\AccessRestriction;
use PDO;

/**
 * @inheritDoc
 *
 * @author Markus Schröder <xelsion@gmail.com>
 * @version 1.0.0
 */
class AccessRestrictionRepository extends ARepository{

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
     * @param string $domain
     * @param string|null $controller
     * @param string|null $method
     * @return mixed
     * @throws SystemException
     */
    public function get(string $domain, ?string $controller, ?string $method ): AccessRestrictionModel {
        try {
            $this->query_builder->Select()
                ->From("access_restrictions")
                ->Where("domain=:domain")
                    ->And("controller=:controller")
                    ->And("method=:method")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(":domain", $domain);
            $this->pdo->bindParam(":controller", $controller);
            $this->pdo->bindParam(":method", $method);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, AccessRestrictionModel::class);
            return $this->pdo->execute()->fetch();
        } catch( Exception $e ) {
            throw new SystemException($e->getFile(), $e->getLine(), $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * Returns the entry with the given $id as associative array
     *
     * @param int $id
     * @return array
     * @throws SystemException
     */
    public function getAsArray( int $id ): array {
        try {
            $this->query_builder->Select()
                ->From("access_restrictions")
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
     * Returns all entries as an array of AccessRestrictionModels
     *
     * @return array
     * @throws SystemException
     */
    public function getAll(): array {
        try {
            $this->query_builder->Select()
                ->From("access_restrictions")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->setFetchMode(PDO::FETCH_CLASS, AccessRestrictionModel::class);
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
        return $this->findIn("access_restrictions", AccessRestrictionModel::class, $conditions, $order, $direction, $limit, $page);
    }

    /**
     * Returns the total number of access restrictions
     *
     * @return int
     * @throws SystemException
     */
    public function getNumRows(): int {
        $this->query_builder->Select("COUNT(DISTINCT *)")->As("num_count")
            ->From("access_restrictions");
        $this->pdo->useQueryBuilder($this->query_builder);
        $result = $this->pdo->execute()->fetch();
        return (int)$result["num_count"];
    }

    /**
     * @param AccessRestriction $restriction
     * @return void
     * @throws SystemException
     */
    public function createObject( AccessRestriction $restriction ): void {
        try {
            $this->query_builder->Insert("access_restrictions")
                ->Columns(["domain", "controller", "method", "restriction_type", "role_id"])
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':domain', $restriction->domain);
            $this->pdo->bindParam(':controller', $restriction->controller);
            $this->pdo->bindParam(':method', $restriction->method);
            $this->pdo->bindParam(':restriction_type', $restriction->restriction_type, PDO::PARAM_INT);
            $this->pdo->bindParam(':role_id', $restriction->role_id, PDO::PARAM_INT);
            $this->pdo->execute();
            $restriction->id = $this->pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param AccessRestriction $restriction
     * @return void
     * @throws SystemException
     */
    public function updateObject( AccessRestriction $restriction ): void {
        try {
            $this->query_builder->Update("access_restrictions")
                ->Set(["domain", "controller", "method", "restriction_type", "role_id"])
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $restriction->id, PDO::PARAM_INT);
            $this->pdo->bindParam(':domain', $restriction->domain);
            $this->pdo->bindParam(':controller', $restriction->controller);
            $this->pdo->bindParam(':method', $restriction->method);
            $this->pdo->bindParam(':restriction_type', $restriction->restriction_type, PDO::PARAM_INT);
            $this->pdo->bindParam(':role_id', $restriction->role_id, PDO::PARAM_INT);
            $this->pdo->execute();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @param AccessRestriction $restriction
     * @return void
     * @throws SystemException
     */
    public function deleteObject( AccessRestriction $restriction ): void {
        try {
            $this->query_builder->Delete("access_restrictions")
                ->Where("id=:id")
            ;
            $this->pdo->useQueryBuilder($this->query_builder);
            $this->pdo->bindParam(':id', $restriction->id, PDO::PARAM_INT);
            $this->pdo->execute();
            $restriction->id = $this->pdo->lastInsertId();
        } catch( Exception $e ) {
            throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
        }
    }

    /**
     * @return void
     * @throws SystemException
     */
    public function deleteAll(): void {
        $this->query_builder->Truncate("access_restrictions");
        $this->pdo->useQueryBuilder($this->query_builder);
        $this->pdo->execute();
    }

}