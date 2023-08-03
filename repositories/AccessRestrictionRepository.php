<?php

namespace repositories;

use Exception;
use lib\App;
use lib\core\blueprints\ARepository;
use lib\core\ConnectionManager;
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
class AccessRestrictionRepository extends ARepository {

	/**
	 * @throws SystemException
	 */
	public function __construct() {
		$cm = App::getInstanceOf(ConnectionManager::class);
		$this->pdo = $cm->getConnection("mvc");
	}

	/**
	 * @param string $domain
	 * @param string|null $controller
	 * @param string|null $method
	 * @return mixed
	 * @throws SystemException
	 */
	public function get(string $domain, ?string $controller, ?string $method): AccessRestrictionModel {
		try {
			// @formatter:off
            $access_restriction = $this->pdo->Select()
                ->From("access_restrictions")
                ->Where("domain=:domain")
                    ->And("controller=:controller")
                    ->And("method=:method")
                ->prepareStatement()
                    ->withParam(":domain", $domain)
                    ->withParam(":controller", $controller)
                    ->withParam(":method", $method)
                ->fetchMode(PDO::FETCH_CLASS, AccessRestrictionModel::class)
                ->execute()
                ->fetch()
            ;
	        // @formatter:on
			if( !$access_restriction ) {
				$access_restriction = new AccessRestrictionModel();
			}
			return $access_restriction;
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
	public function getAsArray(int $id): array {
		try {
			// @formatter:off
            $access_restriction = $this->pdo->Select()
	            ->From("access_restrictions")
                ->Where("id=:id")
                ->PrepareStatement()
                    ->WithParam(":id", $id)
                ->execute()
	            ->fetch()
            ;
	        // @formatter:on
			if( !$access_restriction ) {
				$access_restriction = array();
			}
			return $access_restriction;
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
			// @formatter:off
            return $this->pdo->Select()
	            ->From("access_restrictions")
                ->prepareStatement()
                ->fetchMode(PDO::FETCH_CLASS, AccessRestrictionModel::class)
                ->execute()
                ->fetchAll()
            ;
	        // @formatter:on
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
		// @formatter:off
        $result = $this->pdo->Select("COUNT(DISTINCT *)")->As("num_count")
            ->From("access_restrictions")
            ->prepareStatement()
            ->execute()
            ->fetch()
        ;
	    // @formatter:on
		return (int)$result["num_count"];
	}

	/**
	 * @param AccessRestriction $restriction
	 * @return void
	 * @throws SystemException
	 */
	public function createObject(AccessRestriction $restriction): void {
		try {
			// @formatter:off
            $this->pdo->Insert("access_restrictions")
                ->Columns(["domain", "controller", "method", "restriction_type", "role_id"])
                ->prepareStatement()
                    ->withParam(':domain', $restriction->domain)
                    ->withParam(':controller', $restriction->controller)
                    ->withParam(':method', $restriction->method)
                    ->withParam(':restriction_type', $restriction->restriction_type, PDO::PARAM_INT)
                    ->withParam(':role_id', $restriction->role_id, PDO::PARAM_INT)
                ->execute()
            ;
	        // @formatter:on
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
	public function updateObject(AccessRestriction $restriction): void {
		try {
			// @formatter:off
            $this->pdo->Update("access_restrictions")
                ->Set(["domain", "controller", "method", "restriction_type", "role_id"])
                ->Where("id=:id")
                ->prepateStatement()
                    ->withParam(':id', $restriction->id, PDO::PARAM_INT)
                    ->withParam(':domain', $restriction->domain)
                    ->withParam(':controller', $restriction->controller)
                    ->withParam(':method', $restriction->method)
                    ->withParam(':restriction_type', $restriction->restriction_type, PDO::PARAM_INT)
                    ->withParam(':role_id', $restriction->role_id, PDO::PARAM_INT)
                ->execute()
            ;
	        // @formatter:on
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @param AccessRestriction $restriction
	 * @return void
	 * @throws SystemException
	 */
	public function deleteObject(AccessRestriction $restriction): void {
		try {
			// @formatter:off
            $this->pdo->Delete("access_restrictions")
                ->Where("id=:id")
                ->prepareStatement()
                    ->withParam(':id', $restriction->id, PDO::PARAM_INT)
                ->execute()
            ;
	        // @formatter:on
			$restriction = new AccessRestriction();
		} catch( Exception $e ) {
			throw new SystemException(__FILE__, __LINE__, $e->getMessage(), $e->getCode(), $e->getPrevious());
		}
	}

	/**
	 * @return void
	 * @throws SystemException
	 */
	public function deleteAll(): void {
		// @formatter:off
        $this->pdo->Truncate("access_restrictions")
            ->prepareStatement()
            ->execute()
        ;
	    // @formatter:on
	}

}